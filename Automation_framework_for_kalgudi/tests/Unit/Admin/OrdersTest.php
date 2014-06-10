<?php

class Unit_Interspire_OrdersTest extends PHPUnit_Framework_TestCase
{
    /** @var array $orderData */
    private static $orderData;

    /** @var TestAdminOrders $ordersMock */
    private static $ordersMock;

    public static function setUpBeforeClass()
    {
        self::$orderData = array(
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
            'ordrefundedamount' => '10.0000',
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

        self::$ordersMock = new TestAdminOrders();
    }

    public function testEmptyRefundAmount()
    {
        $this->setExpectedException('Exception', GetLang('EnterRefundAmount'));
        self::$ordersMock->processRefundAmount('', 'partial', self::$orderData);
    }

    public function testInvalidRefundAmount()
    {
        $this->setExpectedException('Exception', GetLang('InvalidRefundAmount'));
        self::$ordersMock->processRefundAmount(30, 'partial', self::$orderData);
    }

    public function testNonNumericRefundAmount()
    {
        $this->setExpectedException('Exception', GetLang('InvalidRefundAmountFormat'));
        self::$ordersMock->processRefundAmount('a', 'partial', self::$orderData);
    }

    public function testInvalidRefundAmountFormat()
    {
        $this->setExpectedException('Exception', GetLang('InvalidRefundAmountFormat'));
        self::$ordersMock->processRefundAmount(-1, 'partial', self::$orderData);
    }

    public function testRefundFloatAmount()
    {
        $refundAmount = self::$ordersMock->processRefundAmount(19.95, 'partial', self::$orderData);
        $this->assertEquals($refundAmount, 19.95);
    }

    /**
     * Testing a known float point calculation whereby if no rounding is used this refund would throw an exception
     */
    public function testRefundFloatCalc()
    {
        $orderData = array(
            'ordrefundedamount' => '111.0100',
            'total_inc_tax' => '111.1300',
        ) + self::$orderData;

        $refundAmount = self::$ordersMock->processRefundAmount('0.12', 'partial', $orderData, 2);
        $this->assertEquals($refundAmount, '0.12');
    }

    public function testRefundBalance()
    {
        $refundAmount = self::$ordersMock->processRefundAmount('', '', self::$orderData, 2);
        $this->assertEquals($refundAmount, 19.95);
    }
}

class TestAdminOrders extends ISC_ADMIN_ORDERS
{
    public function __construct()
    {
        // Do nothing. Just used to disable original constructor.
    }

    /**
     * Checks if provided refund amount is valid and not any more than the total amount that may be refunded for this order.
     * Throws an exception if refund amount is invalid.
     *
     * @param string $refundAmt
     * @param string $refundType
     * @param array $order
     * @param int $decimalPlaces
     * @return float
     * @throws Exception
     */
    public function processRefundAmount($refundAmt, $refundType, $order, $decimalPlaces = null)
    {
        return parent::processRefundAmount($refundAmt, $refundType, $order, $decimalPlaces);
    }
}
