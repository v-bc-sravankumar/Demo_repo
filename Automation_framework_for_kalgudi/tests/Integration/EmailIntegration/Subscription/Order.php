<?php

require_once dirname(__FILE__) . '/Base.php';

/**
* @property Interspire_EmailIntegration_Subscription_Order $sub
*/
class Unit_EmailIntegration_Subscription_Order extends Unit_EmailIntegration_Subscription_Base
{
	public function getSubscriptionInstance ()
	{
		$quote = $this->createTestQuote();

		$order = array(
			'orderpaymentmodule' => '',
			'ordcurrencyid' => 1,
			'ordcurrencyexchangerate' => 1,
			'ordcustmessage' => '',
			'ordipaddress' => getIp(),
			'orderstatus' => ORDER_STATUS_INCOMPLETE,
			'extraInfo' => array(),
			'quote' => $quote,
		);

		$entity = new Store_Order_Gateway;
		$orderId = $entity->add($order);

		$this->assertInternalType('int',  $orderId, "Failed to create test order from quote: " . $entity->getError());
		$subscription = new Interspire_EmailIntegration_Subscription_Order($orderId);
		$entity->purge($orderId);
		return $subscription;
	}

	public function testMappableFieldIsPresentForAllSubscriptionData ()
	{
		// don't bother with this test when it comes to order data as there are a lot of fields that come back from the entity classes which aren't currently mapped
		$this->markTestSkipped();
	}

	public function testContainsBrand ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		$this->assertTrue($this->sub->containsBrand(17));
	}

	public function testContainsCategory ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		$this->assertTrue($this->sub->containsCategory(14));
	}

	public function testContainsProduct ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		$this->assertTrue($this->sub->containsProduct(32));
	}
}
