<?php

class Unit_Quote_BulkPricing extends Interspire_IntegrationTest
{
	const TEST_PRODUCT_ID = 28;

	public function setUp ()
	{
		parent::setUp();
		$this->addTestDiscounts(self::TEST_PRODUCT_ID);
	}

	public function tearDown ()
	{
		$this->removeTestDiscounts(self::TEST_PRODUCT_ID);
		parent::tearDown();
	}

	public function getTestQuote ()
	{
		$quote = new ISC_QUOTE;

		$billing = $quote->getBillingAddress();
		$billing->setFirstName('first');
		$billing->setLastName('last');
		$billing->setAddress1('1');
		$billing->setCity('city');
		$billing->setPhone('12345678');
		$billing->setCountryByName('Australia');
		$billing->setStateByName('New South Wales');
		$billing->setZip('2010');
		$this->assertTrue($billing->isComplete(), "Billing address is not complete");

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
		$this->assertTrue($shipping->isComplete(), "Shipping address is not complete");
		$this->assertTrue($shipping->hasShippingMethod(), "Shipping address has no shipping method");

		return $quote;
	}

	public function addTestDiscounts ($productId)
	{
		// @todo would be better as a dataProvider for test????ItemBulkPricing

		$this->fixtures->InsertQuery('product_discounts', array(
			'discountprodid' => $productId,
			'discountquantitymin' => 10,
			'discountquantitymax' => 19,
			'discounttype' => 'percent',
			'discountamount' => 10,
		));

		$this->fixtures->InsertQuery('product_discounts', array(
			'discountprodid' => $productId,
			'discountquantitymin' => 20,
			'discountquantitymax' => 29,
			'discounttype' => 'percent',
			'discountamount' => 15,
		));

		$this->fixtures->InsertQuery('product_discounts', array(
			'discountprodid' => $productId,
			'discountquantitymin' => 30,
			'discountquantitymax' => 0,
			'discounttype' => 'percent',
			'discountamount' => 20,
		));
	}

	public function removeTestDiscounts ($productId)
	{
		$this->fixtures->Query("DELETE FROM [|PREFIX|]product_discounts WHERE discountprodid = " . (int)$productId);
	}

	public function getTestProduct ($productId)
	{
		$product = new ISC_PRODUCT($productId);

		return $product;
	}

	public function getTestQuoteItem (ISC_QUOTE $quote, $productId)
	{
		$item = $quote->createItem();

		$product = $this->getTestProduct($productId);
		$item->setProductId($product->GetProductId());

		return $item;
	}

	/**
	 * Make sure that bulk pricing rules apply when one item adds up to a bulk pricing quantity and that the discount
	 * is correctly applied in the cart.
	 */
	public function testSingleItemBulkPricing ()
	{
		$quote = $this->getTestQuote();

		$product = $this->getTestProduct(self::TEST_PRODUCT_ID);

		$item = $this->getTestQuoteItem($quote, self::TEST_PRODUCT_ID);
		$item->setQuantity(1);
		$quote->addItem($item);
		$this->assertEquals($product->getPrice(), $item->getPrice(), "pricing mismatch at qty 1", 0.0001);

		$item->setQuantity(10);
		$this->assertEquals($product->getPrice() * .9, $item->getPrice(), "pricing mismatch at qty 10", 0.0001);

		$item->setQuantity(20);
		$this->assertEquals($product->getPrice() * .85, $item->getPrice(), "pricing mismatch at qty 20", 0.0001);

		$item->setQuantity(30);
		$this->assertEquals($product->getPrice() * .8, $item->getPrice(), "pricing mismatch at qty 30", 0.0001);

		$item->setQuantity(40);
		$this->assertEquals($product->getPrice() * .8, $item->getPrice(), "pricing mismatch at qty 40", 0.0001);
	}

	/**
	 * Make sure that bulk pricing rules apply when several items add up to a product total which should trigger the
	 * rule.
	 */
	public function testMultipleItemBulkPricing ()
	{
		$quote = $this->getTestQuote();

		$product = $this->getTestProduct(self::TEST_PRODUCT_ID);

		$itemA = $this->getTestQuoteItem($quote, self::TEST_PRODUCT_ID);
		$itemA->setQuantity(1);
		$quote->addItem($itemA);

		$itemB = $this->getTestQuoteItem($quote, self::TEST_PRODUCT_ID);
		$itemB->setQuantity(1)->setEventDate(1,1,2011); // set an event date to get a different hash
		$quote->addItem($itemB);

		$this->assertNotEquals($itemA->getHash(), $itemB->getHash(), "item A and B hash are the same, should be different to run test properly");

		$this->assertEquals($product->getPrice(), $itemA->getPrice(), "pricing A mismatch at qty 2", 0.0001);
		$this->assertEquals($product->getPrice(), $itemB->getPrice(), "pricing B mismatch at qty 2", 0.0001);

		$itemA->setQuantity(5);
		$itemB->setQuantity(5);
		$this->assertEquals($product->getPrice() * .9, $itemA->getPrice(), "pricing A mismatch at qty 10", 0.0001);
		$this->assertEquals($product->getPrice() * .9, $itemB->getPrice(), "pricing B mismatch at qty 10", 0.0001);

		$quote->removeItem($itemA->getId());
		$this->assertEquals($product->getPrice(), $itemB->getPrice(), "pricing B mismatch at qty 2 (after remove)", 0.0001);
	}
}
