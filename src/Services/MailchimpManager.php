<?php namespace DiegoCaprioli\Larachimp\Services;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use Illuminate\Contracts\Logging\Log;

class MailchimpManager
{
    /**
     * The ID of the Mailchimp list to use to subscribe and unsubscribe members.
     *
     * @var string
     */
    private $listId;

    /**
     * Logger instance
     * @var Illuminate\Contracts\Logging\Log
     */
    private $log;

    /**
     * Returns a new MailchimpManager instance ready to use.
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
        $this->listId = config('diegocaprioli.larachimp.larachimp.list_id');
    }

    /**
     * Verifies that the list_id corresponds to a valid list in the connected
     * Mailchimp account.
     */
    protected function verifyList()
    {
        $response = LarachimpFacade::request('GET', 'lists/'.$this->listId, [
            'query' => ['fields' => 'id,web_id,name'],
        ]);
        if (empty($response)) {
            throw new \Exception('The Mailchimp List does not exists. Please verify the list_id configuration.');
        }
    }

    /**
     * Returns the stdClass representing the Mailchimp List Member searched by
     * email exact match. Returns null if no match is found.
     * The returned stdClass has the fields as Mailchimp return for it's members
     * under the exact_matches.members entry.
     *
     * @param LarachimpListMember $member The object instance to search in Mailchimp
     *
     * @return stdClass|null
     *
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/search-members/
     */
    protected function searchMember(LarachimpListMember $member)
    {
        $response = LarachimpFacade::request('GET', 'search-members', [
            'query' => [
                'query' => $member->getEmail(),
                'list_id' => $this->listId,
            ],
        ]);

        if (empty($response)) {
            return;
        } elseif (isset($response->exact_matches)) {
            // Get the result. It should be an exact match
            if ($response->exact_matches->total_items == 1) {
                return $response->exact_matches->members[0];
            }
        } else {
            return;
        }
    }

    /**
     * Adds the $member to the Mailchimp List.
     *
     * @param LarachimpListMember $member The onbject isntance to add as a list member
     *
     * @return stdClass The Mailchimp member
     */
    protected function addListMember(LarachimpListMember $member)
    {
        return LarachimpFacade::request('POST', 'lists/'.$this->listId.'/members', [
            'body' => json_encode([
                'email_address' => $member->getEmail(),
                'status' => $member->isSubscribedToMailchimpList() ? 'subscribed' : 'unsubscribed',
                'email_type' => 'html',
            ]),
        ]);
    }

    /**
     * Updates the subscription status of the list member.
     *
     * @param LarachimpListMember $member         The object instance that corresponds to the Mailchimp member
     * @param string              $subscriberHash The ID in Mailchimp for this subscriber
     *
     * @return stdClass The Mailchimp member
     */
    protected function updateListMember(LarachimpListMember $member, $subscriberHash)
    {
        return LarachimpFacade::request('PATCH', 'lists/'.$this->listId.'/members/'.$subscriberHash, [
            'body' => json_encode([
            	'status' => $member->isSubscribedToMailchimpList() ? 'subscribed' : 'unsubscribed',
        	]),
        ]);
    }

    /**
     * Syncs the member to the Mailchimp List Member's data. Subscribes or
     * unsubscribes and adds new members if they don't exists yet.
     *
     * @param LarachimpListMember $member The object instance that corresponds to the Mailchimp member and should be synced
     *
     * @return stdClass The Mailchimp member
     */
    public function syncMember(LarachimpListMember $member)
    {
        // Verify the list exists
        $this->verifyList();

        // Search the user by email in the list
        $mailchimpListMember = $this->searchMember($member);
        $this->log->info('Member Found = '.var_export($mailchimpListMember, true));
        if (empty($mailchimpListMember)) {
            // Add the user to the list
            $mailchimpListMember = $this->addListMember($member);
        } else {
            // Already exists, check if the subscription status should be changed
            $currentStatus = $member->isSubscribedToMailchimpList() ? 'subscribed' : 'unsubscribed';
            if ($mailchimpListMember->status != $currentStatus) {
                // Change status
                $mailchimpListMember = $this->updateListMember($member, $mailchimpListMember->id);
            }
        }

        return $mailchimpListMember;
    }
}
