<?php

namespace Integration\Model\Orders;

use Orders\Order;

class OrderTest extends \PHPUnit_Framework_TestCase
{
	/** @var Order */
	private static $order;

	/**
	 * @return Order
	 */
	public static function setUpBeforeClass()
	{
		$orderData = array(
			//'orderid' => 1000,
		    'ordtoken' => 'g3b9lka7g5ja88f85c0on70bcb51fsbje',
		    'ordcustid' => '1',
		    'orddate' => '1359957672',
		    'ordlastmodified' => '1359957672',
		    'subtotal_ex_tax' => '29.9500',
		    'subtotal_inc_tax' => '29.9500',
		    'subtotal_tax' => '0.0000',
		    'total_tax' => '0.0000',
		    'base_shipping_cost' => '0.0000',
		    'shipping_cost_ex_tax' => '0.0000',
		    'shipping_cost_inc_tax' => '0.0000',
		    'shipping_cost_tax' => '0.0000',
		    'shipping_cost_tax_class_id' => '2.0000',
		    'base_handling_cost' => '0.0000',
		    'handling_cost_ex_tax' => '0.0000',
		    'handling_cost_inc_tax' => '0.0000',
		    'handling_cost_tax' => '0.0000',
		    'handling_cost_tax_class_id' => '2.0000',
		    'total_ex_tax' => '29.9500',
		    'total_inc_tax' => '29.9500',
		    'ordstatus' => 11,
		    'ordtotalqty' => 1,
		    'ordtotalshipped' => 0,
			'orderpaymentmethod' => 'Heartland Payment System',
		    'orderpaymentmodule' => 'checkout_hps',
		    'ordpayproviderid' => '1234567',
		    'ordpaymentstatus' => 'captured',
		    'ordrefundedamount' => '0.0000',
		    'ordbillfirstname' => '',
		    'ordbilllastname' => '',
		    'ordbillcompany' => '',
		    'ordbillstreet1' => '',
		    'ordbillstreet2' => '',
		    'ordbillsuburb' => '',
		    'ordbillstate' => 'Texas',
		    'ordbillzip' => '75024',
		    'ordbillcountryid' => '226',
		    'ordbillcountrycode' => 'US',
		    'ordbillstateid' => '57',
		    'ordbillphone' => '',
		    'ordbillemail' => 'testing@bigcommerce.com',
			'ordisdigital' => 0,
		    'orddateshipped' => 0,
			'ordstorecreditamount' => '0.0000',
		    'ordgiftcertificateamount' => '0.0000',
		    'ordinventoryupdated' => '0',
			'ordonlygiftcerts' => '0',
		    'ordcurrencyid' => '1',
		    'orddefaultcurrencyid' => '1',
		    'ordcurrencyexchangerate' => '1.0000000000',
		    'ordvendorid' => 0,
		    'ordformsessionid' => 0,
		    'orddiscountamount' => '0.0000',
		    'shipping_address_count' => '1',
		    'coupon_discount' => '0.0000',
		    'deleted' => '0',
		    'order_source' => 'manual',
		);

		self::$order = new Order();

		if($newOrderId = $GLOBALS["ISC_CLASS_DB"]->InsertQuery('orders', $orderData)) {
			self::$order = self::$order->findById($newOrderId);
		} else {
			self::markTestSkipped();
		}
	}

	public static function tearDownAfterClass()
	{
		self::$order->delete();
	}

	public function testIncreaseRefundAmount()
	{
		$refundAmount = 9.95;

		$originalRefundAmount = self::$order->get('ordrefundedamount');
		$orderTotalAmount = self::$order->get('total_inc_tax');
		$this->assertEquals(29.95, $orderTotalAmount);
		$this->assertEquals(0.00, $originalRefundAmount);

		self::$order->increaseRefundedAmount($refundAmount, 2);
		$newRefundAmount = self::$order->get('ordrefundedamount');
		$expectedRefundAmount = $originalRefundAmount + $refundAmount;
		$this->assertEquals($expectedRefundAmount, $newRefundAmount);

		try {
			self::$order->increaseRefundedAmount(20.01, 2);

			$this->fail('An expected exception has not been raised for increasing refund amount to greater than total order amount.');
		} catch (\InvalidArgumentException $e) {

		}

		self::$order->increaseRefundedAmount(20.00, 2);
		$newRefundAmount = self::$order->get('ordrefundedamount');
		$this->assertEquals($orderTotalAmount, $newRefundAmount);
	}

}