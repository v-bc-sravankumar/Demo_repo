<?php

namespace Unit\Payments;

use Exception;

use PHPUnit_Framework_TestCase;

use Payments\Transaction;
use Services\Payments\Result;
use Services\Payments\TransactionalPaymentService;
use Store\TransactionalCheckoutProviderInterface;

class TransactionText extends PHPUnit_Framework_TestCase
{
    public function testGetOrder()
    {
        $mockRepository = $this->getMockBuilder('Repository\Orders')
            ->setMethods(array('findOrderById'))
            ->getMock();

        $transaction = new Transaction();
        $transaction->setOrderId(1);

        $mockRepository->expects($this->at(0))
            ->method('findOrderById')
            ->with($this->equalTo(1));

        $transaction->getOrder($mockRepository);
    }

    public function testSetCurrencyId()
    {
        $mockTransaction = $this->getMockBuilder('Payments\Transaction')
            ->setMethods(array('getCurrency', 'setUsdRate', 'setAmountUsd'))
            ->getMock();
        $mockTransaction->expects($this->at(0))
            ->method('getCurrency')
            ->will($this->returnValue(array('currencycode' => 'test-code')));
        $mockTransaction->expects($this->at(1))
            ->method('setUsdRate')
            ->with($this->equalTo(0.5));
        $mockTransaction->expects($this->at(2))
            ->method('setAmountUsd')
            ->with($this->equalTo(250));

        $mockRateProvider = $this->getMockBuilder('ISC_CURRENCY')
            ->setMethods(array('FetchExchangeRate'))
            ->disableOriginalConstructor()
            ->getMock();
        $mockRateProvider->expects($this->at(0))
            ->method('FetchExchangeRate')
            ->with($this->equalTo('test-code'))
            ->will($this->returnValue(0.5));

        $mockTransaction->setAmount(500);
        $mockTransaction->setCurrencyId(1, $mockRateProvider);
    }

    public function testProcessDataProvider()
    {
        return array(
            // declined, incomplete, payment
            array(true, false, Transaction::TYPE_ORDER_PAYMENT),
            // declined, incomplete, refund
            array(true, false, Transaction::TYPE_ORDER_REFUND),
            // approved, incomplete, payment
            array(false, false, Transaction::TYPE_ORDER_PAYMENT),
            // approved, incomplete, refund
            array(false, false, Transaction::TYPE_ORDER_REFUND),
            // approved, completed, payment
            array(true, true, Transaction::TYPE_ORDER_PAYMENT),
            // approved, completed, refund
            array(true, true, Transaction::TYPE_ORDER_REFUND),
        );
    }

    /**
     * @dataProvider testProcessDataProvider
     */
    public function testProcess($declined, $completed, $type)
    {
        $mockTransaction = $this->getMockBuilder('Payments\Transaction')
            ->setMethods(array(
                'getType',
                'getProviderModule',
                'setMessage',
                'setProviderTransactionId',
                'setStatus',
            ))
            ->getMock();

        $mockModule = $this->getMockBuilder('Unit\Payments\TestModule')
            ->setMethods(array('getService'))
            ->getMock();

        $mockService = $this->getMockBuilder('Unit\Payments\TestService')
            ->setMethods(array('process', 'processRefund'))
            ->getMock();

        $result = new Result();
        if ($declined) {
            $result->markAsDeclined('test-reason');
        } else {
            $result->markAsApproved();

            if ($completed) {
                $result->markAsCompleted();
            }
        }
        $result->setTransactionId('test-service-transaction-id');

        $mockTransaction->expects($this->at(0))
            ->method('getProviderModule')
            ->will($this->returnValue($mockModule));
        $mockTransaction->expects($this->at(1))
            ->method('getType')
            ->will($this->returnValue($type));
        $mockTransaction->expects($this->at(2))
            ->method('setProviderTransactionId')
            ->with($this->equalTo('test-service-transaction-id'));
        $mockTransaction->expects($this->at(3))
            ->method('setStatus')
            ->with($this->equalTo(
                $declined ? Transaction::STATUS_FAILED
                    : ($completed ? Transaction::STATUS_COMPLETED
                        : Transaction::STATUS_PENDING)
            ));

        if ($declined) {
            $mockTransaction->expects($this->at(4))
                ->method('setMessage')
                ->with($this->equalTo('test-reason'));
        }

        $mockModule->expects($this->at(0))
            ->method('getService')
            ->will($this->returnValue($mockService));

        $mockService->expects($this->at(0))
            ->method(
                $type === Transaction::TYPE_ORDER_PAYMENT
                    ? 'process'
                    : 'processRefund'
            )
            ->with($this->equalTo($mockTransaction))
            ->will($this->returnValue($result));

        $mockTransaction->setType(Transaction::TYPE_ORDER_PAYMENT);

        $processResult = $mockTransaction->process(false);

        $this->assertEquals(!$declined, $processResult);
    }

    /**
     * @dataProvider testProcessDataProvider
     */
    public function testProcessCallback($declined, $completed)
    {
        $mockTransaction = $this->getMockBuilder('Payments\Transaction')
            ->setMethods(array(
                'getOrder',
                'getProviderModule',
                'setCurrencyId',
                'setMessage',
                'setProviderTransactionId',
                'setStatus',
            ))
            ->getMock();

        $mockModule = $this->getMockBuilder('Unit\Payments\TestModule')
            ->setMethods(array('getService'))
            ->getMock();

        $mockService = $this->getMockBuilder('Unit\Payments\TestService')
            ->setMethods(array('processCallback'))
            ->getMock();

        $result = new Result();
        if ($declined) {
            $result->markAsDeclined('test-reason');
        } else {
            $result->markAsApproved();

            if ($completed) {
                $result->markAsCompleted();
            }
        }
        $result->setTransactionId('test-service-transaction-id');

        $mockTransaction->expects($this->at(0))
            ->method('getProviderModule')
            ->will($this->returnValue($mockModule));
        $mockTransaction->expects($this->at(1))
            ->method('setProviderTransactionId')
            ->with($this->equalTo('test-service-transaction-id'));
        $mockTransaction->expects($this->at(2))
            ->method('setStatus')
            ->with($this->equalTo(
                $declined ? Transaction::STATUS_FAILED
                    : ($completed ? Transaction::STATUS_COMPLETED
                        : Transaction::STATUS_PENDING)
            ));

        if ($declined) {
            $mockTransaction->expects($this->at(3))
                ->method('setMessage')
                ->with($this->equalTo('test-reason'));
        }

        $mockModule->expects($this->at(0))
            ->method('getService')
            ->will($this->returnValue($mockService));

        $mockService->expects($this->at(0))
            ->method('processCallback')
            ->with($this->equalTo($mockTransaction))
            ->will($this->returnValue($result));

        $processResult = $mockTransaction->processCallback(array(), false);

        $this->assertEquals(!$declined, $processResult);
    }

    /**
     * @dataProvider testProcessDataProvider
     */
    public function testProcessDelayedCapture($declined, $completed)
    {
        $mockTransaction = $this->getMockBuilder('Payments\Transaction')
            ->setMethods(array(
                'getProviderModule',
                'getStatus',
                'setCurrencyId',
                'setMessage',
                'setProviderTransactionId',
                'setStatus',
            ))
            ->getMock();

        $mockModule = $this->getMockBuilder('Unit\Payments\TestModule')
            ->setMethods(array('getService'))
            ->getMock();

        $mockService = $this->getMockBuilder('Unit\Payments\TestService')
            ->setMethods(array('processDelayedCapture'))
            ->getMock();

        $result = new Result();
        if ($declined) {
            $result->markAsDeclined('test-reason');
        } else {
            $result->markAsApproved();

            if ($completed) {
                $result->markAsCompleted();
            }
        }
        $result->setTransactionId('test-service-transaction-id');

        $mockTransaction->expects($this->at(0))
            ->method('getStatus')
            ->will($this->returnValue(Transaction::STATUS_PENDING));
        $mockTransaction->expects($this->at(1))
            ->method('getProviderModule')
            ->will($this->returnValue($mockModule));
        $mockTransaction->expects($this->at(2))
            ->method('setProviderTransactionId')
            ->with($this->equalTo('test-service-transaction-id'));
        $mockTransaction->expects($this->at(3))
            ->method('setStatus')
            ->with($this->equalTo(
                $declined ? Transaction::STATUS_FAILED
                    : ($completed ? Transaction::STATUS_COMPLETED
                        : Transaction::STATUS_PENDING)
            ));

        if ($declined) {
            $mockTransaction->expects($this->at(4))
                ->method('setMessage')
                ->with($this->equalTo('test-reason'));
        }

        $mockModule->expects($this->at(0))
            ->method('getService')
            ->will($this->returnValue($mockService));

        $mockService->expects($this->at(0))
            ->method('processDelayedCapture')
            ->with($this->equalTo($mockTransaction))
            ->will($this->returnValue($result));

        $processResult = $mockTransaction->processDelayedCapture(
            array(),
            false
        );

        $this->assertEquals(!$declined, $processResult);
    }

    public function testProcessVoid()
    {
        $this->markTestIncomplete();
    }
}

class TestModule implements TransactionalCheckoutProviderInterface
{
    public function getService()
    {
        throw new Exception('Not implemented.');
    }

    public function getTransactionId()
    {
        throw new Exception('Not implemented.');
    }
}

class TestService extends TransactionalPaymentService
{
    public function __construct()
    {
    }

    public function process(Transaction $transaction)
    {
        throw new Exception('Not implemented.');
    }

    public function processCallback(Transaction $transaction, array $data)
    {
        throw new Exception('Not implemented.');
    }

    public function getTransactionIdForCallback(array $data)
    {
        throw new Exception('Not implemented.');
    }

    public function getCheckoutId()
    {
        throw new Exception('Not implemented.');
    }

    public function getModuleId()
    {
        throw new Exception('Not implemented.');
    }

    public function isSupported()
    {
        throw new Exception('Not implemented.');
    }
}
