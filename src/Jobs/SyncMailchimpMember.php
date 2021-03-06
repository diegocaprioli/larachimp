<?php namespace DiegoCaprioli\Larachimp\Jobs;

use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use DiegoCaprioli\Larachimp\Services\MailchimpManager;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * Syncronizes a user's email in the configured Mailchimp list. If the user's 
 * email does not exist yet on the list, it creates it. If it exists already, 
 * it syncs up the subscribers status (Subscribed / Unsubscribed)
 */
class SyncMailchimpMember implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * An instance object that can be synced as a Mailchimp List member.
     *
     * @var \DiegoCaprioli\Larachimp\Models\LarachimpListMember
     */
    private $member;

    /**
     * Create a new job instance.
     * 
     * @param \DiegoCaprioli\Larachimp\Models\LarachimpListMember $member
     */
    public function __construct(LarachimpListMember $member)
    {
        $this->member = $member;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // The Job only executes if the apiKey is set! This works sort of an
        // on/off switch
        if (!empty(config('diegocaprioli.larachimp.larachimp.api_key'))) {
            $manager = App::make(MailchimpManager::class);
            $manager->syncMember($this->member);
        }        
    }
}
