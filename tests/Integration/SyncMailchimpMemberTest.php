<?php namespace DiegoCaprioli\Larachimp\Tests\Integration;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Jobs\SyncMailchimpMember;
use DiegoCaprioli\Larachimp\Models\LarachimpListMember;
use Illuminate\Foundation\Bus\DispatchesJobs;

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

	}
	

	public function test_syncs_a_user()
	{
		$member = new Member('test', 'test@siterocket.com', true);
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