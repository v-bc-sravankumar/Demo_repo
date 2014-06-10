<?php

class Unit_Interspire_EventTest extends PHPUnit_Framework_TestCase
{
	/** @var mixed storage for test data which is accessed between event binding and checked by individual tests */
	protected static $_testData;

	public static function callbackSmokeFirst (Interspire_Event $event)
	{
		/** @var Unit_Event_Event */
		$test = $event->data;
		$test->assertEquals('bar', $event->context['foo'], 'event context does not match');
		self::$_testData++;
		return false; // should preventDefault and stopPropagation
	}

	public static function callbackSmokeSecond (Interspire_Event $event)
	{
		// this callback should not run because first should prevent it
		self::$_testData++;
		$test->fail('callbackSmokeSecond is being called when it should have been prevented by callbackSmokeFirst');
	}

	/**
	 * General smoke test for Interspire_Event - tests basic binding, triggering and unbinding
	 *
	 */
	public function testSmoke ()
	{
		// test binding
		$this->assertEquals(null, Interspire_Event::bind('Unit_Event_Event_Smoke', array(__CLASS__, 'callbackSmokeFirst'), array('foo' => 'bar')));
		$this->assertEquals(null, Interspire_Event::bind('Unit_Event_Event_Smoke', array(__CLASS__, 'callbackSmokeSecond')));

		// test triggering with bound events
		self::$_testData = 0;
		$event = Interspire_Event::trigger('Unit_Event_Event_Smoke', $this);
		$this->assertTrue($event->isDefaultPrevented());
		$this->assertTrue($event->isPropagationStopped());
		unset($event);
		$this->assertEquals(1, self::$_testData);

		// test unbinding
		$this->assertEquals(null, Interspire_Event::unbind('Unit_Event_Event_Smoke', array(__CLASS__, 'callbackSmokeFirst')));
		$this->assertEquals(null, Interspire_Event::unbind('Unit_Event_Event_Smoke', array(__CLASS__, 'callbackSmokeSecond')));

		// test triggering after events have been unbound
		self::$_testData = 0;
		$event = Interspire_Event::trigger('Unit_Event_Event_Smoke', $this);
		$this->assertFalse($event->isDefaultPrevented());
		$this->assertFalse($event->isPropagationStopped());
		unset($event);
		$this->assertEquals(0, self::$_testData);
	}

	public function testCanBindWildcardEvent ()
	{
		Interspire_Event::bind('Unit_Event_Wildcard.*', array('echo'));
	}

	public function testCannotTriggerWildcardEvent ()
	{
		$this->setExpectedException('Interspire_Event_Exception');
		Interspire_Event::trigger('Unit_Event_Wildcard.*');
	}

	public function testWildcardBindingIsTriggered ()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Unit_Event_Wildcard.*', array($handler, 'handle'));

		$handler->expects($this->exactly(4))
				->method('handle')
				->with($this->isInstanceOf('Interspire_Event'));

		Interspire_Event::trigger('Unit_Event_Wildcard.alpha');
		Interspire_Event::trigger('Unit_Event_Wildcard.beta');
		Interspire_Event::trigger('Unit_Event_Wildcard.beta');
		Interspire_Event::trigger('Unit_Event_Wildcard.gamma');
	}

	public function testWildcardAtStartOfEventName ()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('*_Unit_Event_Wildcard', array($handler, 'handle'));

		$handler->expects($this->exactly(4))
				->method('handle')
				->with($this->isInstanceOf('Interspire_Event'));

		Interspire_Event::trigger('Alpha_Unit_Event_Wildcard');
		Interspire_Event::trigger('Beta_Unit_Event_Wildcard');
		Interspire_Event::trigger('Beta_Unit_Event_Wildcard');
		Interspire_Event::trigger('Gamma_Unit_Event_Wildcard');
	}

	public function testWildcardInMiddleOfEventName ()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Unit_Event_*_Wildcard', array($handler, 'handle'));

		$handler->expects($this->exactly(4))
				->method('handle')
				->with($this->isInstanceOf('Interspire_Event'));

		Interspire_Event::trigger('Unit_Event_Alpha_Wildcard');
		Interspire_Event::trigger('Unit_Event_Beta_Wildcard');
		Interspire_Event::trigger('Unit_Event_Beta_Wildcard');
		Interspire_Event::trigger('Unit_Event_Gamma_Wildcard');
	}

	public function testMultipleWildcardsInEventName ()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Unit_Event_*_Multiple_*_Wildcard', array($handler, 'handle'));

		$handler->expects($this->exactly(4))
				->method('handle')
				->with($this->isInstanceOf('Interspire_Event'));

		Interspire_Event::trigger('Unit_Event_Alpha_Multiple_Alpha_Wildcard');
		Interspire_Event::trigger('Unit_Event_Alpha_Multiple_Beta_Wildcard');
		Interspire_Event::trigger('Unit_Event_Beta_Multiple_Alpha_Wildcard');
		Interspire_Event::trigger('Unit_Event_Beta_Multiple_Beta_Wildcard');
	}

	public function testWildcardsAreNotNamespaces ()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Unit_Event_Namespace.*', array($handler, 'handle'));

		$handler->expects($this->exactly(2))
				->method('handle')
				->with($this->isInstanceOf('Interspire_Event'));

		Interspire_Event::trigger('Unit_Event_Namespace');
		Interspire_Event::trigger('Unit_Event_Namespace.');
		Interspire_Event::trigger('Unit_Event_Namespace.Foo');
	}
}
