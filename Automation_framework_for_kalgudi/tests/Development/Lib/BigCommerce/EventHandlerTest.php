<?php
use BigCommerce\EventHandler;


class EventHandlerTest extends Interspire_UnitTest
{

	public function testInvoicePaid()
	{
		\Store_Config::override('UnpaidInvoiceId', 0);

		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Unpaid Recovery.Control Panel Warning Removed', array($handler, 'handle'));

		$handler->expects($this->exactly(1))
		->method('handle')
		->with($this->isInstanceOf('Interspire_Event'));

		BigCommerce\EventHandler::handleTrigger('invoice_paid');
	}

	public function testPlanUpgraded()
	{
		$handler = $this->getMock('stdClass', array('handle'));

		Interspire_Event::bind('Disk Usage Upgrade.Control Panel Warning Removed', array($handler, 'handle'));

		$handler->expects($this->exactly(1))
		->method('handle')
		->with($this->isInstanceOf('Interspire_Event'));

		BigCommerce\EventHandler::handleTrigger('plan_upgraded');
	}
}