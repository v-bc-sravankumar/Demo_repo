<?php

namespace Integration\Controllers;

class GeographyControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \GeographyController
	 */
	protected $controller;

	public function setUp()
	{
		$this->controller = new \GeographyController();
	}

	protected function createRequest($method, $params=array(), $accept="application/json")
	{
		$server = array(
			'REQUEST_METHOD' => $method,
			'HTTP_ACCEPT' => $accept,
		);
		return new \Interspire_Request($params, array(), array(), $server);
	}

	public function testGetCountriesAction()
	{
		$this->controller->setRequest($this->createRequest('GET'));

		$countries = $this->controller->countriesAction();

		// Full countries list currently contains 242 countries.
		// This test will break if it doesn't (which is what we want).
		$this->assertCount(242, $countries);

		// Checking for ids because we're depending on these ids to be consistent
		// across multiple stores and many different objects in the app.
		//
		// Until we move to using ISO codes as the indexes/keys, we cannot actually
		// change these integer values.
		//
		foreach($countries as $country) {
			if ($country->id == 6) {
				$this->assertEquals("AU", $country->code);
				$this->assertEquals("Australia", $country->name);
			}
		}
	}

	public function testGetStatesAction()
	{
		$this->controller->setRequest($this->createRequest('GET'));

		$states = $this->controller->statesAction(array('countryCode' => 'AU'));
		$this->assertCount(8, $states);

		$states = $this->controller->statesAction(array('countryCode' => 'US'));
		$this->assertCount(65, $states);

		$states = $this->controller->statesAction(array('countryCode' => 'GB'));
		$this->assertCount(0, $states);
	}
}
