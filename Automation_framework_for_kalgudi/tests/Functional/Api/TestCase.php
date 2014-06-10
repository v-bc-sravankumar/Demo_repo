<?php

abstract class Functional_Api_TestCase extends PHPUnit_Extensions_Functional_TestCase
{
	/**
	 * @var Interspire_DataFixtures
	 */
	protected $fixtures;

	public function setUp()
	{
		$this->fixtures = Interspire_DataFixtures::getInstance();
		$this->authenticate(getenv('API_USER'), getenv('API_PASSWORD'));
	}

	public function makeUrl($path)
	{
		// return a canned value for now as we haven't yet
		// resolved how to run these tests on Bamboo
		return 'http://bigcommerce.local' . $path;
	}
}