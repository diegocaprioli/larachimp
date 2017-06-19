<?php namespace DiegoCaprioli\Larachimp\Jobs;

use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use DiegoCaprioli\Larachimp\Services\MailchimpManager;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * Updates the user's email in the Mailchimp list
 */
class UpdateMailchimpMemberEmail implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * An instance object that can be synced as a Mailchimp List member.
     *
     * @var \DiegoCaprioli\Larachimp\Models\LarachimpListMember
     */
    private $member;

    /**
     * The old email that needs to be replaced with the new one on the member
     * 
     * @var string
     */
    private $oldEmail;

    /**
     * Create a new job instance.
     * 
     * @param \DiegoCaprioli\Larachimp\Models\LarachimpListMember $member
     */
    public function __construct(LarachimpListMember $member, $oldEmail)
    {
        $this->member = $member;
        $this->oldEmail = $oldEmail;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // The Job only executes if the apiKey is set! This works sort of an
        // on/off switch
        if (!empty(config('diegocaprioli.larachimp.larachimp.apikey'))) {
            $manager = App::make(MailchimpManager::class);
            $manager->updateMembersEmail($this->member, $this->oldEmail);
        }        
    }
}
