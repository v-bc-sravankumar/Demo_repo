<?php

namespace Unit\App\Model\Checkout;

use Errors\Problem;
require_once(dirname(__FILE__) . '/../../../Checkout/BaseCheckoutTest.php');

class ServiceTest extends \Unit\Checkout\BaseCheckoutTest
{
    public function testShopperObjectSetInConstructor()
    {
        $quote = $this->getMock('\ISC_QUOTE');
        $service = new \Checkout\Service($quote, "");

        $this->assertTrue($service->getShopper() instanceof \Store_Shopper);
    }

    public function testGetSupportedPaymentProvidersReturnsCorrectValues()
    {
        $quote = $this->getMock('\ISC_QUOTE');
        $service = new \Checkout\Service($quote, '0001', 'customer-token');
        $actual = $service->getSupportedPaymentProviders();

        $this->assertContains('checkout_authorizenet', $actual);

    }

    public function testGiftcertificatesComeBackInCorrectFormat()
    {

        $certificates["H33-W6I-R91-DWD"] = array(
            "code" => "H33-W6I-R91-DWD",
            "id" => "1",
            "amount" => "1000.0550",
            "balance" => "1000.0550",
            "expiry" => "0",
            "used" => 417,
            "remaining" => 583
        );

        $quote = $this->getMock('\ISC_QUOTE', array(
            'getAppliedGiftCertificates'
        ));
        $quote->expects($this->any())->method('getAppliedGiftCertificates')->will($this->returnValue($certificates));

        $service = $this->getMock('\Checkout\Service', array(
            'getQuote'
        ), array(
            $quote,
            '123',
            'token'
        ));
        $service->expects($this->any())->method('getQuote')->will($this->returnValue($quote));

        $expected["H33-W6I-R91-DWD"] = array(
            "code" => "H33-W6I-R91-DWD",
            "id" => "1",
            "amount" => 1000.055,
            "balance" => 1000.055,
            "expiry" => "0",
            "used" => 417,
            "remaining" => 583
        );

        $actual = $service->getAppliedGiftCertificates();

        $this->assertEmpty(array_diff($expected, $actual));

    }

    // expect error to be returned when no shipping address provided to service
    public function test_GetShippingQuotes_WithNoAddressProvided()
    {
        $mockQuote = $this->getQuoteMock();
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->exactly(1))->method("getAvailableShippingMethods")->will($this->returnValue(array()));
        $service->expects($this->exactly(1))->method("getFailedQuoteMessage")->will($this->returnValue("no address"));

        // test service method with addresses that return no shipping methods
        $actual = $service->getShippingQuotes();

        $this->assertTrue($actual instanceof \Errors\Problem);
        $this->assertEquals("no address", $actual->__get('detail'));
    }

    // expect error to be returned when no shipping address provided to service
    public function test_GetShippingQuotes_WithNoProvidersForAddress()
    {
        $mockQuote = $this->getQuoteMock(array($this->getMockEmptyAddress()));
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->exactly(1))->method("getAvailableShippingMethods")->will($this->returnValue(array()));
        $service->expects($this->exactly(1))->method("getFailedQuoteMessage")->will($this->returnValue("some error"));

        // test service method with addresses that return no shipping methods
        $actual = $service->getShippingQuotes();

        $this->assertTrue($actual instanceof \Errors\Problem);
        $this->assertEquals("some error", $actual->__get('detail'));
    }

    public function test_GetShippingQuoteArray_WithNoAddressProvided()
    {
        $mockQuote = $this->getQuoteMock();
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->exactly(1))->method("getAvailableShippingMethods")->will($this->returnValue(array()));
        $pm = $this->getProtectedMethod('\Checkout\Service','getShippingQuoteArray');
        $actual = $pm->invokeArgs($service, array());

        $this->assertEquals(array(), $actual);
    }

    public function test_GetShippingQuoteArray_WithNoProvidersForAddress()
    {
        $mockQuote = $this->getQuoteMock($this->getMockEmptyAddress());
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->exactly(1))->method("getAvailableShippingMethods")->will($this->returnValue(array()));

        $pm = $this->getProtectedMethod('\Checkout\Service','getShippingQuoteArray');
        $actual = $pm->invokeArgs($service, array());

        $this->assertEquals(array(), $actual);
    }

    protected function getMockLoggedInShopper($storeCredit)
    {
        $customer = $this->getMock('\Store_Customer', array('getStoreCredit'));
        $customer
            ->expects($this->any())
            ->method('getStoreCredit')
            ->will($this->returnValue($storeCredit));

        $shopper = $this->getMock('\Store_Shopper', array('getCustomer'));
        $shopper
            ->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        return $shopper;

    }

    public function testGetTotalToPayAddsUpToZero()
    {

        $quote = $this->getMock('ISC_QUOTE', array('getAppliedStoreCredit'));

        $quote
            ->expects($this->any())
            ->method('getAppliedStoreCredit')
            ->will($this->returnValue(150));

        $service = $this->getMock('\Checkout\Service', array('getTotalRows'), array($quote, ''));

        $totalRows['total']['value'] = 150;
        $service
            ->expects($this->any())
            ->method('getTotalRows')
            ->will($this->returnValue($totalRows));

        $actual = $service->getTotalToPay();
        $this->assertEquals($actual, 0);

    }

    public function testGetTotalToPayAddsUpToSomething()
    {

        $quote = $this->getMock('ISC_QUOTE', array('getAppliedStoreCredit'));
        $quote
            ->expects($this->any())
            ->method('getAppliedStoreCredit')
            ->will($this->returnValue(100));

        $service = $this->getMock('\Checkout\Service', array('getTotalRows'), array($quote, ''));

        $totalRows['total']['value'] = 150;
        $service
            ->expects($this->any())
            ->method('getTotalRows')
            ->will($this->returnValue($totalRows));

        $actual = $service->getTotalToPay();
        $this->assertEquals($actual, 50);

    }

    public function test_NormalizeStoreCredit_WhenTotalAdjustedToNeedMoreCreditAndAvailable()
    {

        $shopper = $this->getMockLoggedInShopper(200);

        $quote = $this->getMock('ISC_QUOTE', array('setAppliedStoreCredit', 'getAppliedStoreCredit'));
        $quote
            ->expects($this->any())
            ->method('getAppliedStoreCredit')
            ->will($this->returnValue(100));

        $quote
            ->expects($this->once())
            ->method('setAppliedStoreCredit')
            ->with(150);

        $service = $this->getMock('\Checkout\Service', array('getTotalRows'), array($quote, 'session_id', $shopper));

        $totalRows['total']['value'] = 150;
        $service
            ->expects($this->any())
            ->method('getTotalRows')
            ->will($this->returnValue($totalRows));

        $norm = $this->getProtectedMethod('\Checkout\Service','normalizeStoreCredit');
        $norm->invokeArgs($service, array());

    }

    public function test_NormalizeStoreCredit_WhenTotalAdjustedToNeedMoreCreditAndNoneAvailable()
    {

        $shopper = $this->getMockLoggedInShopper(100);

        $quote = $this->getMock('ISC_QUOTE', array('setAppliedStoreCredit', 'getAppliedStoreCredit'));
        $quote
            ->expects($this->any())
            ->method('getAppliedStoreCredit')
            ->will($this->returnValue(100));

        $quote
            ->expects($this->never())
            ->method('setAppliedStoreCredit');

        $service = $this->getMock('\Checkout\Service', array('getTotalRows'), array($quote, array(), $shopper));

        $totalRows['total']['value'] = 150;
        $service
            ->expects($this->any())
            ->method('getTotalRows')
            ->will($this->returnValue($totalRows));

        $norm = $this->getProtectedMethod('\Checkout\Service','normalizeStoreCredit');
        $norm->invokeArgs($service, array());

    }

    public function test_NormalizeStoreCredit_WhenTotalAdjustedToNeedLessCredit()
    {

        $shopper = $this->getMockLoggedInShopper(100);

        $quote = $this->getMock('ISC_QUOTE', array('setAppliedStoreCredit', 'getAppliedStoreCredit', 'getGrandTotal', 'getTotalToPay'));
        $quote
            ->expects($this->any())
            ->method('getAppliedStoreCredit')
            ->will($this->returnValue(100));

        $quote
            ->expects($this->once())
            ->method('setAppliedStoreCredit')
            ->with(50);

        $service = $this->getMock('\Checkout\Service', array('getTotalRows'), array($quote, array(), $shopper));

        $totalRows['total']['value'] = 50;
        $service
            ->expects($this->any())
            ->method('getTotalRows')
            ->will($this->returnValue($totalRows));

        $norm = $this->getProtectedMethod('\Checkout\Service','normalizeStoreCredit');
        $norm->invokeArgs($service, array());

    }
}