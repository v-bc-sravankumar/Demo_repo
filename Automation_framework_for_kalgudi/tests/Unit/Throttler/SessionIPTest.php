<?php

class Unit_Throttler_SessionIPTest extends PHPUnit_Framework_TestCase
{
	private $cache = null;
	private $throttler = null;
	private $sessionSetting = null;
	private $ipSetting = null;

	public function setUp()
	{
		$this->sessionSetting = new Throttler_Setting(Throttler_Type::SESSION, 1, 1);
		$this->ipSetting = new Throttler_Setting(Throttler_Type::IP, 2, 1);
		$this->cache = new Interspire_Cache_Memory();
		$this->throttler = new Throttler_SessionIP(array(
			$this->sessionSetting,
			$this->ipSetting,
		));
	}

	public function tearDown()
	{
		$this->cache = null;
		$this->throttler = null;
		$this->sessionSetting = null;
		$this->ipSetting = null;
	}

	private function buildTestThrottler()
	{
		$this->throttler->setCache($this->cache);
		$this->throttler->enable();
	}

	public function testDisabledByDefault()
	{
		$this->assertFalse($this->throttler->isEnabled());
	}

	public function testEnable()
	{
		$this->buildTestThrottler();
		$this->throttler->enable();
		$this->assertTrue($this->throttler->isEnabled());
	}

	public function testDisable()
	{
		$this->buildTestThrottler();
		$this->throttler->enable();
		$this->throttler->disable();
		$this->assertFalse($this->throttler->isEnabled());
	}

	public function testProduceToken()
	{
		$this->buildTestThrottler();
		$token = $this->throttler->produceToken();
		$this->assertFalse(empty($token));
	}

	public function testValidateNotInCacheToken()
	{
		$this->buildTestThrottler();
		$ret = $this->throttler->validateToken('invalidtoken');
		$this->assertFalse($ret);
	}

	public function testValidateFreshToken()
	{
		$this->buildTestThrottler();

		$token = $this->throttler->produceToken();
		$ret = $this->throttler->validateToken($token);

		$this->assertTrue($ret);
	}

	public function testValidateAlreadyConsumedToken()
	{
		$this->buildTestThrottler();

		$token = $this->throttler->produceToken();
		$this->throttler->consumeToken($token);
		$ret = $this->throttler->validateToken($token);

		$this->assertFalse($ret);
	}

	public function testValidateExpiredToken()
	{
		$this->buildTestThrottler();
		$this->throttler->setTokenTimeout(1);

		$token = $this->throttler->produceToken();
		sleep(1);
		$ret = $this->throttler->validateToken($token);

		$this->assertFalse($ret);
	}

	private function recordUsage($session = null, $ip = null)
	{
		$this->throttler->recordUsage($this->makeRequest($session, $ip));
	}

	private function isThresholdReached($session = null, $ip = null)
	{
		return $this->throttler->isThresholdReached($this->makeRequest($session, $ip));
	}

	private function makeRequest($session = null, $ip = null)
	{
		$usage = array();
		if ($session) {
			$usage[] = new Throttler_Request(Throttler_Type::SESSION, $session);
		}
		if ($ip) {
			$usage[] = new Throttler_Request(Throttler_Type::IP, $ip);
		}
		return $usage;
	}

	public function testIsThresholdReachedWithZeroUsage()
	{
		$this->buildTestThrottler();
		$this->assertFalse($this->isThresholdReached('sessionid'));
	}

	public function testIsThresholdReachedWithThresholdUsage()
	{
		$this->buildTestThrottler();
		$this->recordUsage('sessionid');
		$this->assertTrue($this->isThresholdReached('sessionid'));
	}

	public function testIsThresholdReachedWithExpiredUsage()
	{
		$this->buildTestThrottler();
		$this->recordUsage('sessionid');
		sleep(2);
		$this->assertFalse($this->isThresholdReached('sessionid'));
	}

	public function testSessionAndIpThreholdNotReached()
	{
		$this->buildTestThrottler();
		$this->assertFalse($this->isThresholdReached('1st session', 'localhost'));
	}

	public function testSessionThresholdReachedBeforeIp()
	{
		$this->buildTestThrottler();
		$this->recordUsage('1st session', 'localhost');

		$this->assertTrue($this->isThresholdReached('1st session', 'localhost'));
		$this->assertFalse($this->isThresholdReached('2nd session', 'localhost'));
	}

	public function testIpThresholdReachedAfterSession()
	{
		$this->buildTestThrottler();
		$this->recordUsage('1st session', 'localhost');
		$this->recordUsage('2nd session', 'localhost');

		$this->assertTrue($this->isThresholdReached('3rd session', 'localhost'));
	}
}
