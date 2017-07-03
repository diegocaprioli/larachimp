<?php namespace DiegoCaprioli\Larachimp\Tests\Integration;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Jobs\RemoveMailchimpMember;
use DiegoCaprioli\Larachimp\Jobs\SyncMailchimpMember;
use DiegoCaprioli\Larachimp\Jobs\UpdateMailchimpMemberEmail;
use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use DiegoCaprioli\Larachimp\Services\MailchimpManager;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\App;

class SyncMailchimpMemberTest extends BaseTestCase {
	
	use DispatchesJobs;


	protected function getEnvironmentSetUp($app)
	{

		parent::getEnvironmentSetUp($app);

		// Set up the queue configuration
		$app['config']->set('queue', [
			'default' => 'sync',
			'connections' => [
				'sync' => [
					'driver' => 'sync',
				],
			],
			'failed' => [
		        'database' => 'sqlite',
		        'table'    => 'failed_jobs',
		    ],
		]);

		// Database with sqlite in memeory
		$app['config']->set('database', [
			'default' => 'testbench',
			'connections' => [
				'testbench' => [
					'driver' => 'sqlite',
					'database' => ':memory:',
					'prefix' => '',
				],
			],			
		]);

		// Set the cipher
		$app['config']->set('app.key', 'SomeRandomStringOf32Characters12');
		$app['config']->set('app.cipher', 'AES-256-CBC');

	}

	protected function instanciateNewMember($subscribed = false)
	{
		$faker = \Faker\Factory::create();
		return new Member($faker->firstName, $faker->lastName, 'test-' . $faker->username . '@siterocket.com', $subscribed);
	}
	

	public function test_deletes_a_member()
	{
		$member = $this->instanciateNewMember();
		$manager = new MailchimpManager(new VerySimpleLogger());
        $manager->syncMember($member);

        $memberObject = $manager->searchMember($member->getEmail());
        $this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);

        // Remove
        $manager->removeListMember($memberObject->email_address);

        // Search again
        $memberObject = $manager->searchMember($member->getEmail());
        $this->assertEmpty($memberObject, 'The member was not deleted!');        
	}


	public function test_syncs_a_user()
	{		
		$member = $this->instanciateNewMember();
		$manager = new MailchimpManager(new VerySimpleLogger());
        $manager->syncMember($member);

        $memberObject = $manager->searchMember($member->getEmail());
        $this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);
        $this->assertEquals('unsubscribed', $memberObject->status);

        // Sync again:
        $member->receiveNews = true;
        $manager->syncMember($member);

        $memberObject = $manager->searchMember($member->getEmail());
        $this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);
        $this->assertEquals('subscribed', $memberObject->status);

        // Remove member so clean up a bit...
        $manager->removeListMember($memberObject->email_address);
	}

	public function test_updates_a_members_email()
	{
		// Sync a user		
		$member = $this->instanciateNewMember(true);
		$manager = new MailchimpManager(new VerySimpleLogger());		
        $manager->syncMember($member);
		
		// Change the email
		$oldEmail = $member->email;
		$faker = \Faker\Factory::create();
		$member->email = 'test-changed' . $faker->username . '@siterocket.com';

		// Request a change of email
		$memberReturned = $manager->updateMembersEmail($member, $oldEmail);
		$this->assertNotEmpty($memberReturned);
		$this->assertTrue(isset($memberReturned->email_address));
		$this->assertEquals($member->email, $memberReturned->email_address);
				
		// Search for the new email, assert it's there
		$memberObject = $manager->searchMember($member->getEmail());
		$this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);

        // Search for the old email, assert it's NOT there
		$oldMemberObject = $manager->searchMember($oldEmail);
		$this->assertEmpty($oldMemberObject, 'A member was found when it shouldn\'t: ' . var_export($memberObject, true));

		// Remove member so we clean up a bit...
		$manager->removeListMember($memberObject->email_address);
		
	}

	public function test_sync_and_remove_user_with_queued_job() {
		$member = $this->instanciateNewMember();
		$this->dispatch(new SyncMailchimpMember($member));
		
		// remove the member to clean up...
		$this->dispatch(new RemoveMailchimpMember($member->email));
	}

	public function test_updates_member_email_with_queued_job()
	{
		$member = $this->instanciateNewMember();
		$this->dispatch(new SyncMailchimpMember($member));
		
		// update email
		$oldEmail = $member->email;
		$faker = \Faker\Factory::create();
		$member->email = 'test-changed' . $faker->username . '@siterocket.com';
		$this->dispatch(new UpdateMailchimpMemberEmail($member, $oldEmail));
		
		// remove the member to clean up...
		$this->dispatch(new RemoveMailchimpMember($member->email));
	}


}


class Member implements LarachimpListMember {

	public $firstName;
	public $lastName;
	public $email;
	public $receiveNews;

	public function __construct($first, $last, $email, $receiveNews)
	{
		$this->firstName = $first;
		$this->lastName = $last;
		$this->email = $email;
		$this->receiveNews = $receiveNews;
	}

	public function getFirstName() { return $this->firstName; }
	public function getLastName() { return $this->lastName; }
    public function getEmail() { return $this->email; }
    public function isSubscribedToMailchimpList() { return $this->receiveNews; }

}

class VerySimpleLogger implements Log {
	public function alert($message, array $context = []) { $this->write($message); }
	public function critical($message, array $context = []) { $this->write($message); }
	public function error($message, array $context = []) { $this->write($message); }
	public function warning($message, array $context = []) { $this->write($message); }
	public function notice($message, array $context = []) { $this->write($message); }
	public function info($message, array $context = []) { $this->write($message); }
	public function debug($message, array $context = []) { $this->write($message); }
	public function log($level, $message, array $context = []) { $this->write($message); }
	public function useFiles($path, $level = 'debug') {}
	public function useDailyFiles($path, $days = 0, $level = 'debug') {}

	public function write($message) { var_export($message); }
}