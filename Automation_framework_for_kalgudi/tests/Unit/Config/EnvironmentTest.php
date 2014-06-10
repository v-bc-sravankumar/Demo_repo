<?php

namespace Unit\Config;

use PHPUnit_Framework_TestCase;
use Config\Environment;
use Config\Properties;

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
	protected $currentEnvironment;

	public function setUp()
	{
		$this->currentEnvironment = Environment::name();

		Environment::setLoadPath(__DIR__ . '/environments');
		Environment::setBaseEnvironmentFile('');
	}

	public function tearDown()
	{
		Environment::setLoadPath(ISC_BASE_PATH . '/config/environments');
		Environment::setBaseEnvironmentFile(ISC_BASE_PATH . '/config/environment.php');
		Environment::define($this->currentEnvironment);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testErrorThrowWhenNoEnvironmentDefined()
	{
		Environment::define('');
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testErrorThrownWhenUnknownEnvironmentIsDefined()
	{
		Environment::define("octopus");
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testErrorThrownWhenBadEnvironmentIsDefined()
	{
		Environment::define("bad");
	}

	public function testDefineAllowedEnvironmentWithoutBaseEnvironment()
	{
		Environment::define('test');

		$this->assertTrue(Environment::is('test'));
		$this->assertTrue(Environment::get("test.environment_available"));
		$this->assertFalse(Environment::get("base.environment_available", false));
	}

	public function testDefineAllowedEnvironmentWithBaseEnvironment()
	{
		Environment::setBaseEnvironmentFile(__DIR__ . '/environments/base.php');
		Environment::define('test');

		$this->assertTrue(Environment::is('test'));
		$this->assertTrue(Environment::get("test.environment_available"));
		$this->assertTrue(Environment::get("base.environment_available"));
	}

	public function testDuplicateEnvironmentDefinition()
	{
		Environment::define('test');
		Environment::define('test');

		$this->assertTrue(Environment::is('test'));
		$this->assertTrue(Environment::get("test.environment_available"));
	}

	public function testOverrideAndRestore()
	{
		Environment::define('test');

		$this->assertTrue(Environment::get("test.environment_available"));

		Environment::override(new Properties(array('test'=>array('environment_available'=>false))));

		$this->assertFalse(Environment::get("test.environment_available"));

		Environment::restore();

		$this->assertTrue(Environment::get("test.environment_available"));
	}

	public function testDefineEnvironmentFromProperties()
	{
		Environment::define('properties');

		$this->assertTrue(Environment::is('properties'));
		$this->assertTrue(Environment::get("properties.environment_available"));
	}
}
