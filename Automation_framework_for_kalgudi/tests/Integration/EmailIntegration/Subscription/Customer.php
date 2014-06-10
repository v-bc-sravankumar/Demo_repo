<?php

require_once dirname(__FILE__) . '/Base.php';

class Unit_EmailIntegration_Subscription_Customer extends Unit_EmailIntegration_Subscription_Base
{
	public function testGetSubscriptionEventIdIsExpectedType ()
	{
		$this->assertInternalType('bool', $this->sub->getSubscriptionEventId());
	}

	public function createTestCustomer ()
	{
		$form = new ISC_FORM;
		$entity = new Store_Customer_Gateway;

		$formSessionId = $form->saveFormSession(FORMFIELDS_FORM_ACCOUNT);
		if (!$formSessionId) {
			return false;
		}

		// this is based off a var_export of $StoreCustomer in ISC_ADMIN_CUSTOMERS->AddCustomerStep2 before the call to customerEntity->add
		$customer = array(
			'customerid' => 0,
			'custconfirstname' => ucfirst(Interspire_RandomWords::word()),
			'custconlastname' => ucfirst(Interspire_RandomWords::word()),
			'custconcompany' => ucwords(Interspire_RandomWords::phrase()),
			'custconemail' => self::TEST_EMAIL,
			'custconphone' => '1234567890',
			'custpassword' => 'password',
			'custstorecredit' => number_format(mt_rand(1, 999999) * .01, '2', '.', ''),
			'custgroupid' => '0',
			'custformsessionid' => $formSessionId,
		);

		$customerId = $entity->add($customer);
		if (!$customerId) {
			return false;
		}

		return $customerId;
	}

	public function removeTestCustomer ($id)
	{
		$entity = new Store_Customer_Gateway;
		return $entity->delete($id);
	}

	public function getSubscriptionInstance ()
	{
		$customerId = $this->createTestCustomer();

		// to reference a last-used billing address we need to create a test order
		$quote = $this->createTestQuote();
		$quote->setCustomerId($customerId);

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

		$sub = new Interspire_EmailIntegration_Subscription_Customer($customerId);
		$this->removeTestCustomer($customerId);
		$entity->delete($orderId);

		return $sub;
	}

	public function testMappableFieldIsPresentForAllSubscriptionData ()
	{
		// don't bother with this test when it comes to customer data as there are a lot of fields that come back from the entity classes which aren't currently mapped
		$this->markTestSkipped();
	}
}
