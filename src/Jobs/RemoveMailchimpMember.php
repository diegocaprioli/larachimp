<?php namespace DiegoCaprioli\Larachimp\Jobs;

use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use DiegoCaprioli\Larachimp\Services\MailchimpManager;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

/**
 * Removes a member from the Mailchip list
 */
class RemoveMailchimpMember implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * The old email that needs to be replaced with the new one on the member
     * 
     * @var string
     */
    private $email;

    /**
     * Create a new job instance.
     * 
     * @param string $email
     */
    public function __construct($email)
    {        
        $this->email = $email;
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
            $manager->removeListMember($this->email);
        }        
    }
}
