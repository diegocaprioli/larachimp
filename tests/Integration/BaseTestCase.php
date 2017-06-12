<?php namespace DiegoCaprioli\Larachimp\Tests\Integration;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Providers\LarachimpServiceProvider;
use DiegoCaprioli\Larachimp\Services\Larachimp;

use Orchestra\Testbench\TestCase;


abstract class BaseTestCase extends TestCase {

	public function setUp()
	{		
		// dd("setUp 1");
		parent::setUp(); // It seems it calls all the get functions

		// My code goes here
		// dd("setUp 2");
	}	

	protected function getPackageProviders($app)
	{
	    return [LarachimpServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		//dd("getEnvironmentSetUp");
		if (file_exists(__DIR__ . '/../.env')) {
			$dotenv = new \Dotenv();
			$dotenv->load(__DIR__ . '/..');
		}

	    $config = include(__DIR__ . '/../../config/larachimp.php');
	    $app['config']->set('diegocaprioli.larachimp.larachimp', $config);	    
	}


}