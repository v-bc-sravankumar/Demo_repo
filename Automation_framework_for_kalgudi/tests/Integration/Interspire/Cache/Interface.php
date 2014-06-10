<?php

abstract class Unit_Lib_Interspire_Cache_Interface extends Interspire_IntegrationTest
{
	abstract public function getCache();

	public $cache;

	public $key;

	public function setUp ()
	{
		parent::setUp();
		$this->cache = $this->getCache();
		if (!$this->cache) {
			$this->markTestSkipped("cache not available for " . get_class($this));
		}

		$this->key = 'TEST_CACHE_' . mt_rand(0, PHP_INT_MAX);
	}

	public function tearDown ()
	{
		if ($this->cache) {
			$this->cache->remove($this->key);
		}
		parent::tearDown();
	}

	public function testStringValue ()
	{
		$this->assertTrue($this->cache->set($this->key, 'foo'));
		$this->assertSame('foo', $this->cache->get($this->key));
	}

	public function testBoolValue ()
	{
		$this->assertTrue($this->cache->set($this->key, true));
		$this->assertSame('1', $this->cache->get($this->key));
	}

	public function testIntegerValue ()
	{
		$this->assertTrue($this->cache->set($this->key, 123));
		$this->assertSame('123', $this->cache->get($this->key));
	}

	public function testDoubleValue ()
	{
		// uses assertContains to work-around cross-system precision issue
		// arising before the float has been cast to a string
		$value = 1/3;
		$this->assertTrue($this->cache->set($this->key, $value));
		$this->assertContains('0.333333333333', $this->cache->get($this->key));
	}

	public function testArrayValue ()
	{
		$this->assertFalse($this->cache->set($this->key, array(1)));
	}

	public function testObjectValue ()
	{
		$this->assertFalse($this->cache->set($this->key, new stdClass));
	}

	public function testRelativeExpiryTimeCorrectlyExpires ()
	{
		$cache = $this->getCache();
		$key = 'expiring';
		$value = 'foo';
		$timeout = 3;

		$cache->remove($key);
		$this->assertTrue($cache->set($key, $value, $timeout));

		// unavoidable usage of sleep to correctly check cache timeout which is based on seconds and time()

		sleep(1);
		$this->assertSame($value, $cache->get($key), "key prematurely expired with timeout of $timeout");

		sleep(3);
		$this->assertFalse($cache->get($key), "key should have expired with timeout of $timeout but hasn't");

		$cache->remove($key);
	}

	public function testAbsoluteExpiryTimeIncorrectlyExpires ()
	{
		$cache = $this->getCache();
		$key = 'incorrect_expiry';
		$value = 'foo';
		$timeout = time() + 1;

		$cache->remove($key);
		$this->assertTrue($cache->set($key, $value, $timeout));

		if ($cache->get($key) != $value) {
			// a failure at this point isn't very significant, we shouldn't be
			// using timestamp based expirations since the cache interface
			// requires relative values
			$this->markTestSkipped("Key '$key' not present immediately after set, cannot verify unhappy path for expiration values.");
		}

		// unavoidable usage of sleep to correctly check cache timeout which is based on seconds and time()

		sleep(2);
		$this->assertSame($value, $cache->get($key), "key expired but shouldn't have when given timeout of $timeout");

		$cache->remove($key);
	}
}
