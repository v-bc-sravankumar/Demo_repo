<?php

abstract class Unit_EmailIntegration_Subscription_Base extends Interspire_IntegrationTest
{
	const TEST_EMAIL = 'gwilym.evans@bigcommerce.com';

	abstract public function getSubscriptionInstance ();

	/** @var Interspire_EmailIntegration_Subscription */
	public $sub;

	public function setUp()
	{
		parent::setUp();
		$this->sub = $this->getSubscriptionInstance();
	}

	/** @return ISC_QUOTE */
	public function createTestQuote ()
	{
		// this is used by both customer and order subscription tests

		$quote = new ISC_QUOTE;

		$item = new ISC_QUOTE_ITEM;
		$item->setQuote($quote);
		$item->setProductId(32); // sample-data reliant - apple mac pro
		$item->setQuantity(1);
		$quote->addItem($item);

		$address = new ISC_QUOTE_ADDRESS;
		$address->setQuote($quote);
		$quote->setBillingAddress($address);
		$address->setAddress1('address 1');
		$address->setAddress2('address 2');
		$address->setCity('city');
		$address->setZip('1234');
		$address->setCountryByName('Australia');
		$address->setCompany('company');
		$address->setEmail(self::TEST_EMAIL);
		$address->setFirstName('first');
		$address->setLastName('last');
		$address->setPhone('12345678');
		$address->setStateByName('New South Wales', 'Australia');
		$this->assertTrue($address->isComplete(), "Test billing address is not complete");

		$address = new ISC_QUOTE_ADDRESS_SHIPPING;
		$address->setQuote($quote);
		$quote->addShippingAddress($address);
		$address->setAddress1('address 1');
		$address->setAddress2('address 2');
		$address->setCity('city');
		$address->setZip('1234');
		$address->setCountryByName('Australia');
		$address->setCompany('company');
		$address->setEmail(self::TEST_EMAIL);
		$address->setFirstName('first');
		$address->setLastName('last');
		$address->setPhone('12345678');
		$address->setStateByName('New South Wales', 'Australia');
		$address->setShippingMethod(1.00, 'test', 'byweight');
		$address->setHandlingCost(0);
		$this->assertTrue($address->isComplete(), "Test shipping address is not complete");

		$this->assertTrue($quote->canBeFinalized(), "Test order cannot be finalised");

		return $quote;
	}

	public function testGetSubscriptionTypeLangIsExpectedType ()
	{
		$this->assertInternalType('string', $this->sub->getSubscriptionTypeLang());
	}

	public function testGetSubscriptionEventIdIsExpectedType ()
	{
		$this->assertInternalType('string', $this->sub->getSubscriptionEventId());
	}

	public function testGetSubscriptionFieldsIsExpectedType ()
	{
		$this->assertInternalType('array', $this->sub->getSubscriptionFields());
	}

	public function testSubscriptionFieldsHaveDescriptions ()
	{
		foreach ($this->sub->getSubscriptionFields() as $groupName => $groupFields) {
			foreach ($groupFields as $fieldName => /** @var Interspire_EmailIntegration_Field */$field) {
				$this->assertInternalType('string', $field->description, "Mappable field " . $groupName . " -> " . $field->id . " has a non-string description");
				$this->assertTrue(!empty($field->description), "Mappable field " . $groupName . " -> " . $field->id . " has an empty description");
			}
		}
	}

	public function testGetSubscriptionEmailIsExpectedValue ()
	{
		$this->assertEquals($this->sub->getSubscriptionEmail(), self::TEST_EMAIL);
	}

	public function testGetSubscriptionEmailFieldIsExpectedType ()
	{
		$this->assertInternalType('string', $this->sub->getSubscriptionEmailField());
	}

	public function testGetSubscriptionDataIsExpectedType ()
	{
		$this->assertInternalType('array', $this->sub->getSubscriptionData());
	}

	public function testSubscriptionDataIsPresentForAllMappableFields ()
	{
		$mappableFieldGroups = $this->sub->getSubscriptionFields();
		$mappableData = $this->sub->getSubscriptionData();

		foreach ($mappableFieldGroups as $groupName => $groupFields) {
			foreach ($groupFields as $fieldName => /** @var Interspire_EmailIntegration_Field */$field) {
				$this->assertEquals($fieldName, $field->id, "Array index of mappable fields does not match field id");
				$this->assertTrue(isset($mappableData[$field->id]), "Mappable field " . $groupName . " -> " . $field->id . " has no matching subscription data");
			}
		}
	}

	public function testMappableFieldIsPresentForAllSubscriptionData ()
	{
		$mappableFields = $this->sub->getFlatSubscriptionFields();
		$mappableData = $this->sub->getSubscriptionData();

		foreach ($mappableData as $fieldId => $fieldData) {
			$this->assertTrue(isset($mappableFields[$fieldId]), "Subscription data field " . $fieldId . " has no matching field map");
		}
	}
}
