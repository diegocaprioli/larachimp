<?php

use DiegoCaprioli\Larachimp\Services\Larachimp;
use PHPUnit\Framework\TestCase;

class LarachimpTest extends TestCase {


	public function setUp()
	{
		$config = include(__DIR__ . '/../../config/larachimp.php');
		$this->larachimp = new Larachimp();
		$this->larachimp->initialize($config['apikey'], $config['baseuri']);
	}
	

	public function test_it_connects_to_api_properly()
	{		
		$response = $this->larachimp->request('GET', '');
	}

}