<?php

class Unit_Throttler_UsageTest extends PHPUnit_Framework_TestCase
{

	public function testIncrement()
	{
		$usage = new Throttler_Usage();
		$this->assertEquals(0,$usage->getCount());
		$this->assertEquals(0,count($usage->getTimestamps()));

		$usage->increment();
		$this->assertEquals(1,$usage->getCount());
		$this->assertEquals(1,count($usage->getTimestamps()));

		$usage->increment();
		$this->assertEquals(2,$usage->getCount());
		$this->assertEquals(2,count($usage->getTimestamps()));
	}

	public function testDecrement()
	{
		$usage = new Throttler_Usage();
		$usage->increment();
		$usage->increment();
		$this->assertEquals(2,$usage->getCount());
		$this->assertEquals(2,count($usage->getTimestamps()));

		$usage->decrement();
		$this->assertEquals(1,$usage->getCount());
		$this->assertEquals(1,count($usage->getTimestamps()));

		$usage->decrement();
		$this->assertEquals(0,$usage->getCount());
		$this->assertEquals(0,count($usage->getTimestamps()));

		// check boundary
		$usage->decrement();
		$this->assertEquals(0,$usage->getCount());
		$this->assertEquals(0,count($usage->getTimestamps()));
	}

	public function testToString()
	{
		$usage = new Throttler_Usage();
		$ret = $usage->toString();
		$this->assertEquals('0',$ret);

		$usage->increment();
		$ret = $usage->toString();
		$pat = '/^\d{1}(,\d+)+$/';
		$this->assertEquals(1,preg_match($pat,$ret));

		$usage->increment();
		$ret = $usage->toString();
		$this->assertEquals(1,preg_match($pat,$ret));
	}

	public function testfromString()
	{
		$usage = new Throttler_Usage();
		$usage = $usage->toString();
		$usage = Throttler_Usage::fromString($usage);
		$this->assertEquals(0,$usage->getCount());
		$this->assertEquals(0,count($usage->getTimestamps()));

		$usage->increment();
		$usage = $usage->toString();
		$usage = Throttler_Usage::fromString($usage);
		$this->assertEquals(1,$usage->getCount());
		$this->assertEquals(1,count($usage->getTimestamps()));

		$usage->increment();
		$usage = $usage->toString();
		$usage = Throttler_Usage::fromString($usage);
		$this->assertEquals(2,$usage->getCount());
		$this->assertEquals(2,count($usage->getTimestamps()));
	}

	public function testIsWithinInterval()
	{
		$usage = new Throttler_Usage();
		$ret = $usage->isWithinInterval(1);
		$this->assertTrue($ret);

		// timestamped at 0 time
		$usage->increment();
		$ret = $usage->isWithinInterval(1);
		$this->assertTrue($ret);

		// 2 seconds later
		sleep(2);
		$ret = $usage->isWithinInterval(1);
		$this->assertFalse($ret);

		// add a new timestamp, but the earliest one should be used to compare
		$usage->increment();
		$ret = $usage->isWithinInterval(1);
		$this->assertFalse($ret);

		// remove the earliest one, and use next one for comparison
		$usage->decrement();
		$usage->increment();
		$ret = $usage->isWithinInterval(1);
		$this->assertTrue($ret);
	}

}