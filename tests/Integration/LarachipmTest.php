<?php namespace DiegoCaprioli\Larachimp\Tests\Integration;

use DiegoCaprioli\Larachimp\Facades\LarachimpFacade;

class LarachimpTest extends BaseTestCase {
	

	public function test_it_connects_to_api_properly()
	{		
		$response = LarachimpFacade::get('');
	}

}