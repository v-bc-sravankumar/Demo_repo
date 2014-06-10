<?php

namespace Unit\Checkout;

use Store\Controllers;
use Errors\Problem;
use \Errors\InvalidProperties;
use \Errors\MethodNotAllowed;
use \Errors\NotFound;
use Store\Cart\Analytics\AnalyticsTracker;
use Settings\CheckoutController;

class BaseCheckoutTest extends \PHPUnit_Framework_TestCase
{
    protected $id;
    protected $newSessionId;

    protected function setUp()
    {
        $this->id = bin2hex(openssl_random_pseudo_bytes(32));
        $this->newSessionId = bin2hex(openssl_random_pseudo_bytes(32));
    }

    protected function getMockEmptyAddress() {
        $methods = array(
            'getShippingMethod',
            'getAvailableShippingMethods',
            'assignStoreDB',
            );
        $addr = $this->getMock('ISC_QUOTE_ADDRESS_SHIPPING', $methods);

        $addr->expects($this->any())->method('getShippingMethod')->will($this->returnValue(array()));
        $addr->expects($this->any())->method('getAvailableShippingMethods')->will($this->returnValue(array()));

        return $addr;
    }

    protected function getMockedService($quote = null, $methods = array())
    {

        $quote = is_null($quote) ? new \ISC_QUOTE() : $quote;

        if(empty($methods)) {
            $methods = array(
                'isThereAStockProblem',
                'getSessionObject',
                'setShopperEmail',
                'getShippingQuoteArray',
                'getShopperObject',
                'getBillingAddress',
                'getAddresses',
                'saveAddresses',
                'setAddressOnQuote',
                'saveShippingMethods',
                'setCustomerMessage',
                'getFailedQuoteMessage',
                'getAvailableShippingMethods',
                'getShippingAddresses',
                'stopAnalyticsTracking',
                'getGrandTotal',
                'getSupportedPaymentProviders',
                'loadOrder',
                'loadProvider',
                'finalizeOrderFromToken',
            );
        }

        $service = $this->getMock('\Checkout\Service', $methods, array($quote, array(), false));
		$service->expects($this->any())->method("stopAnalyticsTracking")->will($this->returnValue(array()));

        return $service;
    }

    protected function getMockCheckoutModule($providerName, $isHosted = false, $isExpress = false)
    {
        $methods = array();
        $provider = $this->getMock(strtoupper("\CHECKOUT_".$providerName), $methods, array(), '', false);
        $provider->expects($this->any())->method("SetOrderData")->will($this->returnValue(null));
        if ($isExpress)
        {
            $provider->expects($this->any())->method("getExpressCheckoutUrl")->will($this->returnValue("expressUrl"));
        }
        if ($isHosted)
        {
            $provider->expects($this->any())->method("getHiddenFieldArray")->will($this->returnValue(
                array(
                    'key1'=>'val1',
                    'key2'=>'val2',
                )));
        }
        return $provider;
    }

    protected function getControllerMock($service, $request, $response, $hasCheckoutSessionId = true)
    {
        $controller = $this->getMock('\Storefront\CheckoutController',
            array(
                'stopTracking',
                'getQuote',
                'isEmailUsed',
                'getCheckoutSessionId',
                'getSessionValueForCheckoutId',
                'hasCheckoutSessionId',
                'setSessionValueForCheckoutID',
                'generateRandomSessionId',
                'isFinishOrderFlagSet',
                'unsetFinishOrderFlag',
                'getOrderToken',
            ),
            array($service));

        $controller->setRequest($request);
        $controller->setResponse($response);


        // mock presence/absence of $_SESSION['checkout_session_id'] true by default
        $controller->expects($this->any())->method('hasCheckoutSessionId')->will($this->returnValue(
            $hasCheckoutSessionId));

        // mock return value of $_SESSION['checkout_session_id']
        $controller->expects($this->any())->method('getSessionValueForCheckoutId')->will($this->returnValue(
            $hasCheckoutSessionId ? $this->id : null));

        // mock return of $controller->sessionId
        $controller->expects($this->any())->method('getCheckoutSessionId')->will($this->returnValue($this->id));

        // mock generation of random session ids
        $controller->expects($this->any())->method('generateRandomSessionId')->will($this->returnValue($this->newSessionId));

        $controller->expects($this->any())->method('getQuote')->will($this->returnValue($this->getQuoteMock()));

        return $controller;
    }

    protected function getQuoteMock($returnAddresses = array(), $methods = null)
    {
        $quote = $this->getMock('ISC_QUOTE', array(
            'setCustomerId',
            'getMetaData',
            'isDigital',
            'getShippingAddresses',
        ));
        $quote->expects($this->any())->method('setCustomerId')->with(100);
        $quote->expects($this->any())->method('getMetaData')->will($this->returnValue(array()));
        $quote->expects($this->any())->method('isDigital')->will($this->returnValue(false));
        $quote->expects($this->any())->method('getShippingAddresses')->will($this->returnValue($returnAddresses));


        return $quote;
    }

    protected static function getProtectedMethod($className, $name) {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}