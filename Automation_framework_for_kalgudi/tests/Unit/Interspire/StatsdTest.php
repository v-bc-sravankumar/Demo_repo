<?php

class Unit_Interspire_StatsdTest extends PHPUnit_Framework_TestCase
{
	private function getEnabledMockStatsObject($methodsToMock)
	{
		$statsd = $this->getMock('Interspire_Statsd', $methodsToMock);
		$statsd->setEnabled(true)
			   ->setHost('localhost')
			   ->setPort(1234);
		return $statsd;
	}

	public function testTiming()
	{
		$dataSent = 'foo:2000|ms';

		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->exactly(1))
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->equalTo($dataSent))
			   ->will($this->returnValue(true));

		$this->assertTrue($statsd->timing('foo', 2000));
	}

	public function testSecondsTiming()
	{
		$dataSent = 'foo:2000|ms';

		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->exactly(1))
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->equalTo($dataSent))
			   ->will($this->returnValue(true));

		$this->assertTrue($statsd->secondsTiming('foo', 2));
	}

	public function testIncrementWitMockedSocketSend()
	{
		$data = array('api.products');
		$dataSent = 'api.products:1|c';

		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->exactly(1))
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->equalTo($dataSent));

		$this->assertTrue($statsd->increment($data, 1));
	}

	public function testDecrement()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->once())
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->anything());
		$data = array('api.products');
		$this->assertTrue($statsd->decrement($data));
	}

	public function testDecrementWithString()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->once())
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->anything());
		$data = 'api.products';
		$this->assertTrue($statsd->decrement($data));
	}

	public function testUpdateStatsWithNoStats()
	{
		$statsd = new Interspire_Statsd();
		$this->assertFalse($statsd->updateStats(null));
	}

	public function testUpdateStatsWhenDisabled()
	{
		$statsd = new Interspire_Statsd();
		$statsd->setEnabled(false);
		$this->assertFalse($statsd->updateStats(array()));
	}

	public function testUpdateStatsWithString()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->once())
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->anything());
		$data = 'api.products';
		$this->assertTrue($statsd->updateStats($data));
	}

	public function testUpdateStats()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->once())
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->anything());
		$data = array('api.products');
		$this->assertTrue($statsd->updateStats($data));
	}

	public function testUpdateStatsWithDelta()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->exactly(3))
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->anything());
		$data = array('api.products', 'api.brands', 'api.optionSet');
		$this->assertTrue($statsd->updateStats($data, 2));
	}

	public function testUpdateStatsWithDeltaAndSampleRate()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_writeDataToSocket'));
		$statsd->expects($this->exactly(2))
			   ->method('_writeDataToSocket')
			   ->with($this->anything(), $this->isType('string'));
		$data = array('api.products', 'api.brands');
		$this->assertTrue($statsd->updateStats($data, 2, 1));
	}

	public function testUpdateStatsWithDeltaAndSampleRateAndMockedSend()
	{
		$statsd = $this->getEnabledMockStatsObject(array('_send'));
		$statsd->expects($this->exactly(1))
			   ->method('_send')
			   ->with($this->isType('array'), $this->anything())
			   ->will($this->returnValue(true));
		$data = array('api.products', 'api.products');
		$this->assertTrue($statsd->updateStats($data, 2, 0.5));
	}
}
