<?php namespace DiegoCaprioli\Larachimp\Services;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use DiegoCaprioli\Larachimp\Services\Larachimp;
use DiegoCaprioli\Larachimp\Traits\BasicLogging;
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
     * Make this class use a logger and it's basic methods
     */
    use BasicLogging;   


    /**
     * Returns a new MailchimpManager instance ready to use.
     *
     * @param \Illuminate\Contracts\Logging\Log $log
     */
    public function __construct(Log $log = null)
    {
        $this->log = $log;
        $this->listId = config('diegocaprioli.larachimp.larachimp.list_id');        
        LarachimpFacade::setLog($log);
    }

    /**
     * Verifies that the list_id corresponds to a valid list in the connected
     * Mailchimp account.
     */
    protected function verifyList()
    {        
        if (empty(config('diegocaprioli.larachimp.larachimp.api_key'))) {
            throw new \Exception('The Mailchimp API key is not properly set. Please verify the api_key configuration.');
        }

        $response = LarachimpFacade::get('lists/' . $this->listId, [
            'query' => ['fields' => 'id,web_id,name'],
        ]);
        if (empty($response)) {
            throw new \Exception('The Mailchimp List "' . $this->listId . '" does not exists. Please verify the list_id configuration.');
        }
    }

    /**
     * Returns the stdClass representing the Mailchimp List Member searched by
     * email exact match. Returns null if no match is found.
     * The returned stdClass has the fields as Mailchimp return for it's members
     * under the exact_matches.members entry.
     *
     * @param string $email The email of the Mailchim member to search for
     *
     * @return stdClass|null
     *
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/search-members/
     */
    public function searchMember($email)
    {
        $response = LarachimpFacade::get('search-members', [
            'query' => [
                'query' => $email,
                'list_id' => $this->listId,
            ],
        ]);

        $member = null;
        if (!empty($response)) {
            if (isset($response->exact_matches)) {
                // Get the result. It should be an exact match
                if ($response->exact_matches->total_items == 1) {
                    $member = $response->exact_matches->members[0];
                }
            }
        }        

        $this->logInfo('Member Found = ' . var_export($member, true));

        return $member;
    }

    /**
     * Adds the $member to the Mailchimp List.
     *
     * @param LarachimpListMember $member The onbject isntance to add as a list member
     *
     * @return stdClass The Mailchimp member
     */
    public function addListMember(LarachimpListMember $member)
    {
        return LarachimpFacade::post('lists/' . $this->listId . '/members', [
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
    public function updateListMember(LarachimpListMember $member, $subscriberHash)
    {
        return LarachimpFacade::patch('lists/' . $this->listId . '/members/' . $subscriberHash, [
            'body' => json_encode([
                'status' => $member->isSubscribedToMailchimpList() ? 'subscribed' : 'unsubscribed',
        	]),
        ]);
    }


    /**
     * Removes the member from the mailchimp list, by email
     * 
     * @param  string $email The email to remove from the list
     */
    public function removeListMember($email)
    {
        // Get the member first from Mailchimp
        $member = $this->searchMember($email);
        if (empty($member)) {
            throw new \Exception('There\'s no Mailchimp member in the list with the email \'' . $email . '\'.');
        }

        LarachimpFacade::delete('lists/' . $this->listId . '/members/' . $member->id);
    }


    /**
     * Syncs the member to the Mailchimp List Member's data. Subscribes or
     * unsubscribes and adds new members if they don't exists yet.
     *
     * @param LarachimpListMember $member The object instance that corresponds to the Mailchimp member and should be synced
     * @return stdClass The Mailchimp member
     */
    public function syncMember(LarachimpListMember $member)
    {
        // Verify the list exists
        $this->verifyList();

        // Search the user by email in the list
        $mailchimpListMember = $this->searchMember($member->getEmail());

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


    /**
     * [updateMembersEmail description]
     * @param  LarachimpListMember  $member     The LarachimpListMember with the new email address
     * @param  string               $oldEmail   The old email address that the $member was registered with
     * @return stdClass                         The Mailchimp member
     */
    public function updateMembersEmail(LarachimpListMember $member, $oldEmail)
    {

        // Verify the list exists
        $this->verifyList();

        // Search the user using the oldEmail email in the list
        $oldListMember = $this->searchMember($oldEmail);
        if (empty($oldListMember)) {
            throw new \Exception('There\'s no Mailchimp member in the list with the email \'' . $oldEmail . '\'.');
        }

        // Add a new member with the new email:
        $newListMember = $this->syncMember($member);

        // Remove the old member
        $this->removeListMember($oldEmail);

        return $newListMember;
    }

}
