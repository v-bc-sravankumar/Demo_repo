<?php

class Unit_Quote_Coupons extends Interspire_IntegrationTest
{
	const COUPON_TYPE_PERCENT = 1;
	const COUPON_TYPE_FIXED_TOTAL = 2;
	const COUPON_TYPE_FIXED_ITEM = 0;

	const COUPON_APPLIES_TO_CATEGORIES = 'categories';
	const COUPON_APPLIES_TO_PRODUCTS = 'products';

	protected $quote = null;
	public function setUp()
	{
		parent::setUp();

		$this->quote = new ISC_QUOTE;
		//$this->quote->createShippingAddress();
	}

	public function addQuoteItem($name, $price, $quantity = 1, $productId = 0)
	{
		try {
			$item = $this->quote->createItem();
			$item
				->setName($name)
				->setBasePrice($price, true)
				->setQuantity($quantity)
				->setInventoryCheckingEnabled(false)
				->setType(PT_PHYSICAL);

			if($productId > 0) {
				$item->setProductId($productId);
			}
			$this->quote->addItem($item);
		}
		catch(ISC_QUOTE_EXCEPTION $e) {
			$this->fail('Error adding test item to quote: '.$e);
		}

		$this->assertTrue($this->quote->hasItem($item->getId()), 'Quote does not have test item.');
	}

	public function getTestCoupon($name = 'TEST', $id = 1)
	{
		return array(
			'couponcode' => $name,
			'couponname' => $name,
			'couponid' => $id,
			'couponenabled' => 1,
			'couponnumuses' => 1,
			'couponmaxuses' => 0,
			'couponappliesto' => 'categories',
			'appliesto' => array(0),
			'couponexpires' => 0,
			'coupontype' => self::COUPON_TYPE_PERCENT,
			'appliesto' => array(),
		);
	}

	public function testTimeExpiredCouponCannotBeApplied()
	{
		$this->setExpectedException('ISC_QUOTE_EXCEPTION', null, ISC_QUOTE_EXCEPTION::COUPON_EXPIRED_TIME);

		$coupon = $this->getTestCoupon();
		$coupon['couponexpires'] = 20;

		$this->quote->applyCoupon($coupon);
	}

	public function testNumUsesExpiredCouponCannotBeApplied()
	{
		$this->setExpectedException('ISC_QUOTE_EXCEPTION', null, ISC_QUOTE_EXCEPTION::COUPON_EXPIRED_USES);

		$coupon = $this->getTestCoupon();
		$coupon['couponnumuses'] = 20;
		$coupon['couponmaxuses'] = 1;

		$this->quote->applyCoupon($coupon);
	}

	public function testMinimumTotalCouponCanBeAppliedWithGreaterTotal()
	{
		$this->addQuoteItem('Test item', 100);

		$coupon = $this->getTestCoupon();
		$coupon['couponminpurchase'] = 50;
		$this->quote->applyCoupon($coupon);
	}

	public function testMinimumTotalCouponWhenTotalIsLessThanMinimum()
	{
		$this->setExpectedException('ISC_QUOTE_EXCEPTION', null, ISC_QUOTE_EXCEPTION::COUPON_MIN_PURCHASE);

		$this->addQuoteItem('Test item', 100);

		$coupon = $this->getTestCoupon();
		$coupon['couponminpurchase'] = 200;
		$this->quote->applyCoupon($coupon);
	}

	public function testDisabledCouponCannotBeApplied()
	{
		$this->setExpectedException('ISC_QUOTE_EXCEPTION', null, ISC_QUOTE_EXCEPTION::COUPON_DISABLED);

		$coupon = $this->getTestCoupon();
		$coupon['couponenabled'] = 0;

		$this->quote->applyCoupon($coupon);
	}

	public function testPercentageBasedCouponTotals()
	{
		$this->addQuoteItem('Test item', 10.00);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_PERCENT;
		$coupon['couponamount'] = 10;
		$coupon['appliesto'] = array(0);

		$this->quote->applyCoupon($coupon);

		$this->assertEquals(10.00, $this->quote->getSubTotal());
		$this->assertEquals(1, $this->quote->getCouponDiscount());
		$this->assertEquals(9, $this->quote->getGrandTotal());
	}

	public function testDollarOffOrderTotalBasedCouponTotals()
	{
		$this->addQuoteItem('Test item', 10.00);
		$this->addQuoteItem('Test item 2', 5);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_TOTAL;
		$coupon['couponamount'] = 5;
		$coupon['appliesto'] = array(0);

		$this->quote->applyCoupon($coupon);

		$this->assertEquals(15, $this->quote->getSubTotal());
		$this->assertEquals(5, $this->quote->getCouponDiscount());
		$this->assertEquals(10, $this->quote->getGrandTotal());
	}

	public function testDollarOffEachItemBasedCouponTotals()
	{
		$this->addQuoteItem('Test item', 10.00);
		$this->addQuoteItem('Test item 2', 5);
		$this->addQuoteItem('Test item 3', 1);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 5;
		$coupon['appliesto'] = array(0);

		$this->quote->applyCoupon($coupon);

		$this->assertEquals(16, $this->quote->getSubTotal());
		$this->assertEquals(11, $this->quote->getCouponDiscount());
		$this->assertEquals(5, $this->quote->getGrandTotal());
	}

	public function testCouponDiscountGreaterThanOrderTotalIsFree()
	{
		$this->addQuoteItem('Test item', 100);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 200;
		$coupon['appliesto'] = array(0);

		$this->quote->applyCoupon($coupon);

		$this->assertEquals(100, $this->quote->getSubTotal());
		$this->assertEquals(100, $this->quote->getCouponDiscount());
		$this->assertEquals(0, $this->quote->getGrandTotal());
	}

	public function testApplyingCouponTwiceDoesNotGiveDoubleDiscounts()
	{
		$this->addQuoteItem('Test item', 100);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 200;
		$coupon['appliesto'] = array(0);

		// Apply the coupon twice
		$this->quote->applyCoupon($coupon);
		$this->quote->applyCoupon($coupon);

		$this->assertEquals(100, $this->quote->getSubTotal());
		$this->assertEquals(100, $this->quote->getCouponDiscount());
		$this->assertEquals(0, $this->quote->getGrandTotal());
	}

	public function testProductBasedCouponCodeAppliesToMatchingProducts()
	{
		// Add two products, with different IDs
		$this->addQuoteItem('Test Item', 300, 1, 2);
		$this->addQuoteItem('Test Item 2', 100, 1, 4);

		// Add a coupon code that applies per item. It should ONLY apply to
		// the item with product ID 2, and therefore there should be a subtotal
		// of $200.
		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 200;
		$coupon['couponappliesto'] = self::COUPON_APPLIES_TO_PRODUCTS;
		$coupon['appliesto'] = array(2);

		$this->quote->applyCoupon($coupon);
		$this->assertEquals(400, $this->quote->getSubTotal());
		$this->assertEquals(200, $this->quote->getCouponDiscount());
		$this->assertEquals(200, $this->quote->getGrandTotal());
	}

	public function testCouponCodeThatDoesNotApplyToQuoteThrowsError()
	{
		$this->setExpectedException('ISC_QUOTE_EXCEPTION', null, ISC_QUOTE_EXCEPTION::COUPON_DOES_NOT_APPLY);

		$this->addQuoteItem('Test Item', 300, 1, 2);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 200;
		$coupon['couponappliesto'] = self::COUPON_APPLIES_TO_PRODUCTS;
		$coupon['appliesto'] = array(4);

		$this->quote->applyCoupon($coupon);
	}

	public function testRemovingCouponCodeRemovesDiscounts()
	{
		$this->addQuoteItem('Test item', 100);

		$coupon = $this->getTestCoupon();
		$coupon['coupontype'] = self::COUPON_TYPE_FIXED_ITEM;
		$coupon['couponamount'] = 200;
		$coupon['appliesto'] = array(0);

		// Apply the coupon
		$this->quote->applyCoupon($coupon);

		// Make sure it has applied
		$this->assertEquals(100, $this->quote->getSubTotal());
		$this->assertEquals(100, $this->quote->getCouponDiscount());
		$this->assertEquals(0, $this->quote->getGrandTotal());

		// Make sure it can be removed
		$this->quote->removeCoupon($coupon['couponcode']);
		$this->assertEquals(100, $this->quote->getSubTotal());
		$this->assertEquals(0, $this->quote->getCouponDiscount());
		$this->assertEquals(100, $this->quote->getGrandTotal());
	}
}