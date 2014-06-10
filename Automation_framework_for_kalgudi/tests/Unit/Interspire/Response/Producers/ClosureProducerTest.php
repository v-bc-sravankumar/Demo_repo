<?php
namespace Unit\Interspire\Response\Producers;

class ClosureProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testWithClosure()
    {
		// Setup
		$spy = new \stdClass;
		$spy->called = false;

		$response = new \Interspire_Response();
		$response->setProducer(
			new \Interspire\Response\Producers\ClosureProducer(
				function($response) use ($spy) {
					$spy->called = true;
					return true;
				}
			)
		);

		// Exercise
		$this->assertTrue($response->send(), 'Returns value returned by closure.');
		$this->assertTrue($spy->called, 'Spy should report that it is called.');
    }

    public function testWithCallback()
    {
		// Setup
		$response = new \Interspire_Response();
		$response->setProducer(
			new \Interspire\Response\Producers\ClosureProducer(
				array(new ClosureProducerTest_DummyCallback, 'returnsFoo')
			)
		);

		// Exercise
		$this->assertEquals('foo', $response->send(), 'Returns value returned by callback.');
    }

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testWithUncallable()
	{
		// Explodes
		$response = new \Interspire_Response();
		$response->setProducer(
			new \Interspire\Response\Producers\ClosureProducer('not callable')
		);
	}
}

class ClosureProducerTest_DummyCallback
{
	public function returnsFoo($response)
	{
		return 'foo';
	}
}
