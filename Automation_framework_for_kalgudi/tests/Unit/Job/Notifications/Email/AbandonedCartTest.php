<?php
class Job_Notifications_Email_AbandonedCartTest extends PHPUnit_Framework_TestCase
{
	public function testExtractDiscountInfoIntoSnippet()
	{
		$file = ISC_BASE_PATH.'/language/en/admin/abandoned_cart.ini';
		ParseLangFile($file, true);

		$class = new ReflectionClass("Mock_Abandoned_Cart");
		$methodDiscInfo = $class->getMethod("_extractDiscountInfoIntoSnippet");
		$methodDiscInfo->setAccessible(true);

		$abCart = new Mock_Abandoned_Cart();

		$args = array('abandonedCartEmailId' =>1);
		$abCart->__set('args', $args);

		// Dollar amount off each item, type = 0
		$coupon0 = $this->getCoupon(0);
		$result = $methodDiscInfo->invoke($abCart, $coupon0);
		$this->assertEquals('*', substr($result, 0, 1), 'Type 0 not correct');

		// Percentage off each item, type = 1
		$coupon1 = $this->getCoupon(1);
		$result = $methodDiscInfo->invoke($abCart, $coupon1);
		$this->assertEquals('100.00&#37; off each item you purchase!', $result, 'Type 1 not correct');

		// Dollar amount off the total, type = 2
		$coupon2 = $this->getCoupon(2);
		$result = $methodDiscInfo->invoke($abCart, $coupon2);
		$this->assertEquals('*', substr($result, 0, 1), 'Type 2 not correct');

		// Dollar amount off the shipping total, type = 3
		$coupon3 = $this->getCoupon(3);
		$result = $methodDiscInfo->invoke($abCart, $coupon3);
		$this->assertEquals('a *100', substr($result, 0, 6), 'Type 3 not correct');

		// Free shipping, type = 4
		$coupon4 = $this->getCoupon(4);
		$result = $methodDiscInfo->invoke($abCart, $coupon4);
		$this->assertEquals('free shipping!', $result, 'Type 4 not correct');
	}

	private function getCoupon($type)
	{
		$mockCoupon = $this->getMock('Store_Coupon');
		$mockCoupon->expects($this->any())
			->method('getCoupon')
			->will($this->returnValue($mockCoupon));

		$mockCoupon->expects($this->any())
			->method('getAmount')
			->will($this->returnValue(100));

		$mockCoupon->expects($this->any())
			->method('getType')
			->will($this->returnValue($type));

		return $mockCoupon;
	}
}

class Mock_Abandoned_Cart extends \Job_Notifications_Email_AbandonedCart
{
	protected function getCurrencyFormattedPrice($amount)
	{
		return '*100';
	}
}