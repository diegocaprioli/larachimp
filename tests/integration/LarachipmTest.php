<?php

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;
use DiegoCaprioli\Larachimp\Providers\LarachimpServiceProvider;
use DiegoCaprioli\Larachimp\Services\Larachimp;
use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase;


class LarachimpTest extends TestCase {

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
		$dotenv = new Dotenv(__DIR__ . '/..');
		$dotenv->load();

	    $config = include(__DIR__ . '/../../config/larachimp.php');
	    $app['config']->set('diegocaprioli.larachimp.larachimp', $config);
	}

	public function test_it_connects_to_api_properly()
	{		
		$response = LarachimpFacade::request('GET', '');
	}

}