<?php

use Job_AbandonedOrder_Monitor as Monitor;
use Orders\Order;
use Store_Currency as Currency;

class Unit_Job_AbandonedOrder_MonitorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param int $orderStatus The status of the order when the job is performed.
     * @param int $invokedCount The number of times we expect the trigger method to be called.
     * @dataProvider providePerformTestData
     */
    public function testPerformTriggersEventWithIncompleteOrder($orderStatus, $invokedCount)
    {
        $orderId = 9999;
        $currencyId = 9998;
        $currencyCode = 'SBQ';

        $logger = $this->getMock('Psr\\Log\\LoggerInterface');
        $monitor = $this->getMockBuilder('Job_AbandonedOrder_Monitor')->setMethods(array(
            'getOrder', 'getOrderCurrency'
        ))->setConstructorArgs(array(array('orderId' => $orderId), $logger))->getMock();

        $orderTotal = 1;
        $order = new Order(array(
            'orderid' => $orderId,
            'ordstatus' => $orderStatus,
            'total_inc_tax' => $orderTotal,
            'ordcurrencyid' => $currencyId,
        ));

        $currency = new Currency(array(
            'currencyid' => $currencyId,
            'currencycode' => $currencyCode,
        ));

        $eventData = array(
            'order' => array(
                'id' => $orderId,
                'total_inc_tax' => $orderTotal,
                'currency' => array(
                    'id' => $currencyId,
                    'code' => $currencyCode,
                ),
            ),
        );

        $eventClass = $this->getMockClass('\\Store_Event', array('trigger'));
        $monitor->setEventClass($eventClass);

        $monitor->expects($this->any())->method('getOrder')->will($this->returnValue($order));
        $monitor->expects($this->any())->method('getOrderCurrency')->with($order)->will($this->returnValue($currency));
        $invocationMocker = $eventClass::staticExpects($this->exactly($invokedCount))->method('trigger');
        if ($invokedCount) {
            $invocationMocker->with(Store_Event::EVENT_ORDER_ABANDONED, $eventData);
        }

        $monitor->perform();
    }

    public function providePerformTestData()
    {
        return array(
            array(Order::STATUS_INCOMPLETE, 1),
            array(Order::STATUS_AWAITING_FULFILLMENT, 0),
        );
    }
}