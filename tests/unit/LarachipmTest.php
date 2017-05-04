<?php

use DiegoCaprioli\Larachimp\Larachimp;
use PHPUnit\Framework\TestCase;

class LarachimpTest extends TestCase {


	public function test_it_connects_to_api_properly()
	{		
		$config = include(__DIR__ . '/../../src/config/larachimp.php');
		$larachimp = new Larachimp($config['apikey'], $config['baseuri']);
		$response = $larachimp->request('GET', '');
	}

}