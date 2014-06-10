<?php

class Unit_Quote_Smoke extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		$this->assertGreaterThan(0, $this->fixtures->InsertQuery('tax_rates', array(
			'tax_zone_id' => 1,
			'name' => 'Unit_Quote_Smoke:10pct',
			'priority' => 0,
			'enabled' => 1,
			'default_rate' => 10,
		)), "Failed to insert test tax rate");
		ISC_TAX::flushStaticCache();
	}

	public function tearDown ()
	{
		parent::tearDown();
		$this->assertTrue($this->fixtures->Query("DELETE FROM [|PREFIX|]tax_rates WHERE name LIKE 'Unit_Quote_Smoke:%'"), "Failed to delete test tax rates");
		ISC_TAX::flushStaticCache();
	}

	public function buildQuote()
	{
		$quote = new ISC_QUOTE;

		$purefi = $quote->createItem();
		$purefi->setProductId(32);
		$purefi->setQuantity(2);
		$quote->addItem($purefi);

		$armband = $quote->createItem();
		$armband->setProductId(33);
		$armband->setQuantity(2);
		$quote->addItem($armband);

		$billing = $quote->getBillingAddress();
		$billing->setFirstName('first');
		$billing->setLastName('last');
		$billing->setAddress1('1');
		$billing->setCity('city');
		$billing->setPhone('12345678');
		$billing->setCountryByName('Australia');
		$billing->setStateByName('New South Wales');
		$billing->setZip('2010');

		$shipping = $quote->createShippingAddress();
		$shipping->setFirstName('first');
		$shipping->setLastName('last');
		$shipping->setAddress1('1');
		$shipping->setCity('city');
		$shipping->setPhone('12345678');
		$shipping->setCountryByName('Australia');
		$shipping->setStateByName('New South Wales');
		$shipping->setZip('2010');
		$shipping->setHandlingCost(1.1);
		$shipping->setShippingMethod(2.2, '.', 'peritem');

		return $quote;
	}

	public function validateQuote(ISC_QUOTE $quote)
	{
		$items = $quote->getItems();

		$purefi = $items[0];
		$data = $purefi->getProductData();
		$this->assertInternalType('array', $data, "Failed to get product data for first test item");
		$this->assertCurrencyEquals(89.0000, $data['prodcalculatedprice'], "First test product found but price is not as expected for test");

		$armband = $items[1];
		$data = $armband->getProductData();
		$this->assertInternalType('array', $data, "Failed to get product data for second test item");
		$this->assertCurrencyEquals(59.0000, $data['prodcalculatedprice'], "Second test product found but price is not as expected for test");

		$this->assertTrue($quote->getBillingAddress()->isComplete(), "Billing address is not complete");

		$this->assertTrue($quote->getShippingAddress()->isComplete(), "Shipping address is not complete");
		$this->assertTrue($quote->getShippingAddress()->hasShippingMethod(), "Shipping address has no shipping method");
	}

	public function validateQuoteTotals(ISC_QUOTE $quote, $expected)
	{
		$this->assertCurrencyEquals($expected['baseHandlingCost'], $quote->getBaseHandlingCost(), "Base handling cost mismatch");

		$this->assertCurrencyEquals($expected['baseShippingCostEx'], $quote->getBaseShippingCost(false), "Base shipping cost ex-tax mismatch");
		$this->assertCurrencyEquals($expected['baseShippingCostInc'], $quote->getBaseShippingCost(true), "Base shipping cost inc-tax mismatch");

		$this->assertCurrencyEquals($expected['baseSubTotal'], $quote->getBaseSubTotal(), "Base sub-total mismatch");

		$this->assertCurrencyEquals($expected['baseWrappingCostEx'], $quote->getBaseWrappingCost(false), "Base wrapping cost ex-tax mismatch");
		$this->assertCurrencyEquals($expected['baseWrappingCostInc'], $quote->getBaseWrappingCost(true), "Base wrapping cost inc-tax mismatch");

		$this->assertCurrencyEquals($expected['couponDiscount'], $quote->getCouponDiscount(), "Coupon discount mismatch");
		$this->assertCurrencyEquals($expected['discountAmount'], $quote->getDiscountAmount(), "Discount amount mismatch");
		$this->assertCurrencyEquals($expected['discountedBaseSubTotal'], $quote->getDiscountedBaseSubTotal(), "Discounted base sub-total mismatch");

		$this->assertCurrencyEquals($expected['giftCertificateTotal'], $quote->getGiftCertificateTotal(), "Gift certificate total mismatch");

		$this->assertCurrencyEquals($expected['handlingCostEx'], $quote->getHandlingCost(false), "Handling cost ex-tax mismatch");
		$this->assertCurrencyEquals($expected['handlingCostInc'], $quote->getHandlingCost(true), "Handling cost inc-tax mismatch");

		$this->assertCurrencyEquals($expected['shippingCostEx'], $quote->getShippingCost(false), "Shipping cost ex-tax mismatch");
		$this->assertCurrencyEquals($expected['shippingCostInc'], $quote->getShippingCost(true), "Shipping cost inc-tax mismatch");
		$this->assertCurrencyEquals($expected['shippingCostTax'], $quote->getShippingCostTax(), "Shipping cost tax mismatch");

		$this->assertCurrencyEquals($expected['subTotalEx'], $quote->getSubTotal(false), "Sub-total ex-tax mismatch");
		$this->assertCurrencyEquals($expected['subTotalInc'], $quote->getSubTotal(true), "Sub-total inc-tax mismatch");
		$this->assertCurrencyEquals($expected['subTotalTax'], $quote->getSubTotalTax(), "Sub-total tax mismatch");

		$this->assertCurrencyEquals($expected['wrappingCostEx'], $quote->getWrappingCost(false), "Wrapping cost ex-tax mismatch");
		$this->assertCurrencyEquals($expected['wrappingCostInc'], $quote->getWrappingCost(true), "Wrapping cost inc-tax mismatch");
		$this->assertCurrencyEquals($expected['wrappingCostTax'], $quote->getWrappingCostTax(), "Wrapping cost tax mismatch");

		$this->assertCurrencyEquals($expected['grandTotal'], $quote->getGrandTotal(), "Grand total mismatch");
		$this->assertCurrencyEquals($expected['grandTotalWithoutGiftCertificates'], $quote->getGrandTotalWithoutGiftCertificates(), "Grand total without gift certificates mismatch");
		$this->assertCurrencyEquals($expected['grandTotalWithStoreCredit'], $quote->getGrandTotalWithStoreCredit(), "Grand total with store credit mismatch");
		$this->assertCurrencyEquals($expected['taxTotal'], $quote->getTaxTotal(), "Tax total mismatch");
	}

	public function testPricesEnteredWithTaxDisplayedWithTax()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');

		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_INCLUSIVE);
		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_INCLUSIVE);

		$quote = $this->buildQuote();
		$this->validateQuote($quote);

		$expected = array(
			'baseHandlingCost' => 1.1,
			'baseShippingCostEx' => 2.2,
			'baseShippingCostInc' => 2.2,
			'baseSubTotal' => 296,
			'baseWrappingCostEx' => 0,
			'baseWrappingCostInc' => 0,
			'couponDiscount' => 0,
			'discountAmount' => 0,
			'discountedBaseSubTotal' => 296,
			'giftCertificateTotal' => 0,
			'handlingCostEx' => 1.1,
			'handlingCostInc' => 1.1,
			'shippingCostEx' => 2.2,
			'shippingCostInc' => 2.2,
			'shippingCostTax' => 0,
			'subTotalEx' => 269.09,
			'subTotalInc' => 296,
			'subTotalTax' => 26.91,
			'wrappingCostEx' => 0,
			'wrappingCostInc' => 0,
			'wrappingCostTax' => 0,
			'grandTotal' => 299.3,
			'grandTotalWithoutGiftCertificates' => 299.3,
			'grandTotalWithStoreCredit' => 299.3,
			'taxTotal' => 26.91,
		);

		$this->validateQuoteTotals($quote, $expected);
	}

	public function testPricesEnteredWithTaxDisplayedWithoutTax()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');

		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_INCLUSIVE);
		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_EXCLUSIVE);

		$quote = $this->buildQuote();
		$this->validateQuote($quote);

		$expected = array(
			'baseHandlingCost' => 1.1,
			'baseShippingCostEx' => 2.2,
			'baseShippingCostInc' => 2.2,
			'baseSubTotal' => 296,
			'baseWrappingCostEx' => 0,
			'baseWrappingCostInc' => 0,
			'couponDiscount' => 0,
			'discountAmount' => 0,
			'discountedBaseSubTotal' => 296,
			'giftCertificateTotal' => 0,
			'handlingCostEx' => 1.1,
			'handlingCostInc' => 1.1,
			'shippingCostEx' => 2.2,
			'shippingCostInc' => 2.2,
			'shippingCostTax' => 0,
			'subTotalEx' => 269.1,
			'subTotalInc' => 296.01,
			'subTotalTax' => 26.91,
			'wrappingCostEx' => 0,
			'wrappingCostInc' => 0,
			'wrappingCostTax' => 0,
			'grandTotal' => 299.31,
			'grandTotalWithoutGiftCertificates' => 299.31,
			'grandTotalWithStoreCredit' => 299.31,
			'taxTotal' => 26.91,
		);

		$this->validateQuoteTotals($quote, $expected);
	}

	public function testPricesEnteredWithoutTaxDisplayedWithTax()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');

		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_EXCLUSIVE);
		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_INCLUSIVE);

		$quote = $this->buildQuote();
		$this->validateQuote($quote);

		$expected = array(
			'baseHandlingCost' => 1.1,
			'baseShippingCostEx' => 2.2,
			'baseShippingCostInc' => 2.2,
			'baseSubTotal' => 296,
			'baseWrappingCostEx' => 0,
			'baseWrappingCostInc' => 0,
			'couponDiscount' => 0,
			'discountAmount' => 0,
			'discountedBaseSubTotal' => 296,
			'giftCertificateTotal' => 0,
			'handlingCostEx' => 1.1,
			'handlingCostInc' => 1.1,
			'shippingCostEx' => 2.2,
			'shippingCostInc' => 2.2,
			'shippingCostTax' => 0,
			'subTotalEx' => 296,
			'subTotalInc' => 325.6,
			'subTotalTax' => 29.6,
			'wrappingCostEx' => 0,
			'wrappingCostInc' => 0,
			'wrappingCostTax' => 0,
			'grandTotal' => 328.9,
			'grandTotalWithoutGiftCertificates' => 328.9,
			'grandTotalWithStoreCredit' => 328.9,
			'taxTotal' => 29.6,
		);

		$this->validateQuoteTotals($quote, $expected);
	}

	public function testPricesEnteredWithoutTaxDisplayedWithoutTax()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');

		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_EXCLUSIVE);
		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_INCLUSIVE);

		$quote = $this->buildQuote();
		$this->validateQuote($quote);

		$expected = array(
			'baseHandlingCost' => 1.1,
			'baseShippingCostEx' => 2.2,
			'baseShippingCostInc' => 2.2,
			'baseSubTotal' => 296,
			'baseWrappingCostEx' => 0,
			'baseWrappingCostInc' => 0,
			'couponDiscount' => 0,
			'discountAmount' => 0,
			'discountedBaseSubTotal' => 296,
			'giftCertificateTotal' => 0,
			'handlingCostEx' => 1.1,
			'handlingCostInc' => 1.1,
			'shippingCostEx' => 2.2,
			'shippingCostInc' => 2.2,
			'shippingCostTax' => 0,
			'subTotalEx' => 296,
			'subTotalInc' => 325.6,
			'subTotalTax' => 29.6,
			'wrappingCostEx' => 0,
			'wrappingCostInc' => 0,
			'wrappingCostTax' => 0,
			'grandTotal' => 328.9,
			'grandTotalWithoutGiftCertificates' => 328.9,
			'grandTotalWithStoreCredit' => 328.9,
			'taxTotal' => 29.6,
		);

		$this->validateQuoteTotals($quote, $expected);
	}
}
