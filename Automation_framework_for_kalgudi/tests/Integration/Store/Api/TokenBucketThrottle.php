<?php

use Store\Api\Throttle\TokenBucketThrottle;

class Integration_Store_Api_TokenBucketThrottle extends PHPUnit_Framework_TestCase {

	/** @var Credis_Client */
	protected static $redis;

	public static function setUpBeforeClass()
	{
		Store_Config::override('HostingId', '123456789');
		self::$redis = new Credis_Client();
	}

	public static function tearDownAfterClass()
	{
		Store_Config::override('HostingId', Store_Config::getOriginal('HostingId'));
		self::$redis->close();
	}

	public function testRemainingWithMissingKey()
	{
		self::$redis->del('tokens:apithrottle:123456789');
		$throttle = new TokenBucketThrottle(500, 60);
		$this->assertEquals(500, $throttle->getRemainingRequests());
	}

	public function testIsThrottled()
	{
		self::$redis->set('tokens:apithrottle:123456789', time().':0');
		$throttle = new TokenBucketThrottle(1, 60);
		$this->assertTrue($throttle->isThrottled(), 'Expected to be throttled');
	}

	public function testIsNotThrottled()
	{
		self::$redis->set('tokens:apithrottle:123456789', time().':1');
		$throttle = new TokenBucketThrottle(1, 60);
		$this->assertFalse($throttle->isThrottled(), 'Expected to not be throttled');
	}

	public function testWasPreviousThrottled()
	{
		self::$redis->set('tokens:apithrottle:123456789', (time() - 61).':0');
		$throttle = new TokenBucketThrottle(1, 60);
		$this->assertFalse($throttle->isThrottled(), 'Expected to not be throttled');
	}

	public function testTouchDecrementsTokenCount()
	{
		self::$redis->set('tokens:apithrottle:123456789', time().':1');
		$throttle = new TokenBucketThrottle(1, 60);
		$throttle->touch();
		list($ts, $tokens) = explode(':', self::$redis->get('tokens:apithrottle:123456789'));
		$this->assertEquals(0, $tokens);
	}
}