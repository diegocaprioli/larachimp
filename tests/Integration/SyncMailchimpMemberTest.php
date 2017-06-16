<?php namespace DiegoCaprioli\Larachimp\Tests\Integration;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Jobs\SyncMailchimpMember;
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
	
	public function test_syncs_a_user()
	{
		$faker = \Faker\Factory::create();
		$member = new Member($faker->name, 'test-' . $faker->username . '@siterocket.com', false);
		$manager = new MailchimpManager(new VerySimpleLogger());
        $manager->syncMember($member);

        $memberObject = $manager->searchMember($member);
        $this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);
        $this->assertEquals('unsubscribed', $memberObject->status);

        // Sync again:
        $member->receiveNews = true;
        $manager->syncMember($member);

        $memberObject = $manager->searchMember($member);
        $this->assertNotEmpty($memberObject);
        $this->assertTrue(isset($memberObject->email_address));
        $this->assertEquals($member->email, $memberObject->email_address);
        $this->assertEquals('subscribed', $memberObject->status);
	}


	public function test_sync_user_with_queued_job() {
		$member = new Member('test', 'test@siterocket.com', false);
		$this->dispatch(new SyncMailchimpMember($member));
	}


}


class Member implements LarachimpListMember {

	public $name;
	public $email;
	public $receiveNews;

	public function __construct($name, $email, $receiveNews)
	{
		$this->name = $name;
		$this->email = $email;
		$this->receiveNews = $receiveNews;
	}

	public function getName() { return $this->name; }
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