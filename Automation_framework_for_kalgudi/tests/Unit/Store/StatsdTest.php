<?php

class Unit_Store_StatsdTest extends PHPUnit_Framework_TestCase
{
	public function testConstructWithNoEnvrionment ()
	{
		$statsd = new Store_Statsd('');
		$this->assertFalse($statsd->getEnabled(), "statsd is incorrectly enabled");
	}

	public function testConstructWithHostEnvironment ()
	{
		$statsd = new Store_Statsd('127.0.0.1');
		$this->assertTrue($statsd->getEnabled(), "statsd is not enabled");
		$this->assertSame('127.0.0.1', $statsd->getHost(), "host mismatch");
		$this->assertSame(8125, $statsd->getPort(), "port mismatch");
	}

	public function testConstructWithHostPortEnvrionment ()
	{
		$statsd = new Store_Statsd('127.0.0.1:1234');
		$this->assertTrue($statsd->getEnabled(), "statsd is not enabled");
		$this->assertSame('127.0.0.1', $statsd->getHost(), "host mismatch");
		$this->assertSame(1234, $statsd->getPort(), "port mismatch");
	}

	public function testConstructWithOnlyPortEnvironment()
	{
		$statsd = new Store_Statsd(':1234');
		$this->assertFalse($statsd->getEnabled(), "statsd is incorrectly enabled");
	}
}
