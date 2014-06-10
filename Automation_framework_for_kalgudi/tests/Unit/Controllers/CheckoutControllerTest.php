<?php
namespace Unit\Controllers;

use Store\Controllers;
use Errors\Problem;
use \Errors\InvalidProperties;
use \Errors\MethodNotAllowed;
use \Errors\NotFound;
use Store\Cart\Analytics\AnalyticsTracker;
use Settings\CheckoutController;

require_once(dirname(__FILE__) . '/../Checkout/BaseCheckoutTest.php');

class CheckoutControllerTest extends \Unit\Checkout\BaseCheckoutTest
{

    public function testRespondWithErrorDoesntSendsStatsdEvent()
    {
        $controller = $this->getMock('\Storefront\CheckoutController', array('incrementStatsdCounter'));
        $controller->setRequest(new \Interspire_Request());
        $controller->setResponse(new \Interspire_Response());
        $controller
            ->expects($this->never())
            ->method('incrementStatsdCounter');
        $controller->respondWithError(new \Errors\Problem('Some error'));
    }

    public function testRespondWithErrorSendsStatsdEvent()
    {
        $controller = $this->getMock('\Storefront\CheckoutController', array('incrementStatsdCounter'));
        $controller->setRequest(new \Interspire_Request());
        $controller->setResponse(new \Interspire_Response());
        $controller->setCurrentAction('grr');
        $controller
            ->expects($this->once())
            ->method('incrementStatsdCounter')
            ->with('api.checkout.error.grr.ffs');
        $controller->respondWithError(new \Errors\Problem(array('statsdKey' => 'ffs')));
    }

    // 4 scenarios
    // request with no checkoutSessionId and $_SESSION['...'] set
    // expect return true and set $controller.sessionID = $_SESSION
    public function testInitiateCheckoutSessionNoParamWithSession()
    {
        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response, true);

        $initMethod = $this->getProtectedMethod('\StoreFront\CheckoutController', 'initiateCheckoutSession');

        // DO NOT update $_SESSION value
        $controller->expects($this->never())->method('setSessionValueForCheckoutID');

        // invoke initialiseCheckoutSession with no id
        $actual = $initMethod->invokeArgs($controller, array(null));

        // ensure controller->sessionId is set to $_SESSION value
        $this->assertEquals($this->id, $this->getProtectedMethod('\StoreFront\CheckoutController', 'getCheckoutSessionId')->invokeArgs($controller, array()));

        $this->assertTrue($actual);
    }

    // request with no checkoutSessionId and NO set $_SESSION['...']
    // expect new session id to be generated, set on $controller  and return true
    public function testInitiateCheckoutSessionNoParamWithoutSession()
    {
        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response, false);

        $initMethod = $this->getProtectedMethod('\StoreFront\CheckoutController', 'initiateCheckoutSession');

        // expect new session to have been set
        $controller->expects($this->once())->method('setSessionValueForCheckoutID')->with($this->newSessionId);

        // invoke initialiseCheckoutSession with no id
        $actual = $initMethod->invokeArgs($controller, array(null));

        $this->assertTrue($actual);
    }

    // request with invalid checkoutId
    // expect return false
    public function testInitiateCheckoutSessionInvalid()
    {
        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);

        $initMethod = $this->getProtectedMethod('\StoreFront\CheckoutController', 'initiateCheckoutSession');

        // invoke initialiseCheckoutSession with invalid id
        $actual = $initMethod->invokeArgs($controller, array('id' => '1'));

        $this->assertFalse($actual);
    }

    // request with valid checkoutId
    // expect $controller.sessionId is set and return true;
    public function testInitiateCheckoutSessionValid()
    {
        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);

        $initMethod = $this->getProtectedMethod('\StoreFront\CheckoutController', 'initiateCheckoutSession');

        // invoke initialiseCheckoutSession with valid id
        $actual = $initMethod->invokeArgs($controller, array('id' => $this->id));

        // assert $controller->sessionId has been set
        $this->assertEquals($this->id, $this->getProtectedMethod('\StoreFront\CheckoutController', 'getCheckoutSessionId')->invokeArgs($controller, array()));

        $this->assertTrue($actual);
    }

    // this tests logic in the startAction that calls the finalizeAction when a session variable is set
    // after returning to finishorder.php from a hosted payment provider that does not immediately update
    // the order or transaction status
    public function testStartActionFinalizesOrderWhenReturningFromAsyncHostedProvider()
    {
        $request = new \Interspire_Request();
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getSessionObject')->will($this->returnValue(array('foo')));
        // expect order to be finalized
        $service->expects($this->once())->method('finalizeOrderFromToken')->will($this->returnValue(true));

        $controller = $this->getControllerMock($service, $request, $response);
        // expect action to check flag from session
        $controller->expects($this->once())->method('isFinishOrderFlagSet')->will($this->returnValue(true));
        // expect flag to be unset after finalising
        $controller->expects($this->once())->method('unsetFinishOrderFlag');

        // invoke action to test expected invocations
        $controller->startAction();
    }

    public function testStartActionDoesNotFinalizeOrderWhenNotReturningFromAsyncHostedProvider()
    {
        $request = new \Interspire_Request();
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getSessionObject')->will($this->returnValue(array('foo')));
        $service->expects($this->never())->method('finalizeOrderFromToken');

        $controller = $this->getControllerMock($service, $request, $response);
        // expect action to check flag from session
        $controller->expects($this->once())->method('isFinishOrderFlagSet')->will($this->returnValue(false));
        $controller->expects($this->never())->method('unsetFinishOrderFlag');

        // invoke action to test expected invocations
        $controller->startAction();
    }


    public function testStartActionReturnsSessionIfThereIsNoStockProblem()
    {
        $request = new \Interspire_Request();
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getSessionObject')->will($this->returnValue(array('foo')));

        $controller = $this->getControllerMock($service, $request, $response);
        $controller->expects($this->once())->method('stopTracking')->with('pre_checkout');

        $actual = $controller->startAction();
        $this->assertEquals(array('foo'), $actual);
    }

    public function testStartActionReturnsProblemIfThereIsAStockProblem()
    {
        $problem = new Problem(array());

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getSessionObject')->will($this->returnValue(array('foo')));
        $service->expects($this->any())->method('isThereAStockProblem')->will($this->returnValue($problem));

        $request = new \Interspire_Request(array(), array(), array(), array(), '');
        $response = new \Interspire_Response();
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->startAction();
        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testShopperAction()
    {
        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"email":"foo@example.com"}'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->once())->method('setShopperEmail');
        $service->expects($this->once())->method('getShopperObject')->will($this->returnValue(array("email"=>"foo@example.com")));

        $controller = $this->getControllerMock($service, $request, $response);
        $controller->expects($this->once())->method('isEmailUsed')->will($this->returnValue(false));
        $actual = $controller->shopperAction();

        $this->assertEquals(array("email"=>"foo@example.com"), $actual);
    }

    public function testShopperActionEmailFail()
    {
        $problem = new \Errors\InvalidProperties(array(
            'detail' => 'Email address is already in use by another customer',
            'statsdKey' => 'email_already_in_use'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"email":"foo@example.com"}'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getControllerMock($service, $request, $response);
        $controller->expects($this->once())->method('isEmailUsed')->will($this->returnValue(true));
        $actual = $controller->shopperAction();

        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testShopperActionStoresPasswordInSessionIfPresent()
    {

        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"email":"foo@example.com", "password": "password1"}'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getMock('\Storefront\CheckoutController',array('isEmailUsed', 'getHashedPassword', 'savePasswordForLater', 'getQuote'), array($service));

        $controller->setRequest($request);
        $controller->setResponse($response);

        $passwordHash = array('salt' => 'mmm', 'encpassword' => '11234');

        $controller->expects($this->any())->method('getQuote')->will($this->returnValue($this->getQuoteMock()));
        $controller->expects($this->once())->method('getHashedPassword')->will($this->returnValue($passwordHash));
        $controller->expects($this->once())->method('isEmailUsed')->will($this->returnValue(false));
        $controller->expects($this->once())->method('savePasswordForLater')->with($passwordHash);

        $actual = $controller->shopperAction();

    }

    public function testStoreCreditFailsWithNoCreditElement()
    {

        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"email":"foo@example.com", "password": "password1"}'));

        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getMock('\Storefront\CheckoutController',array('getQuote'), array($service));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->applyStoreCreditAction();

        $this->assertEquals(422, $actual['status']);

    }

    /**
     * Attempt to apply $60 credit to an order with only $50 as the total,
     * Prove that only $50 is passed to the applyCreditToQuote() method.
     */
    public function testStoreCreditIsAdjustedToTheGrandTotal()
    {
        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"credit":"60"}'));

        $response = $this->getMock('Interspire_Response');

        $methods = array(
            'getGiftCertificateTotal',
            'getAppliedStoreCredit',
            'getCouponDiscount',
            'getGrandTotal'
        );

        $quote = $this->getMock('ISC_QUOTE', $methods);
        $quote->expects($this->any())->method('getGiftCertificateTotal')->will($this->returnValue(0));
        $quote->expects($this->any())->method('getAppliedStoreCredit')->will($this->returnValue(0));
        $quote->expects($this->any())->method('getCouponDiscount')->will($this->returnValue(0));
        $quote->expects($this->any())->method('getGrandTotal')->will($this->returnValue(60));

        foreach($methods as $m) {
            $quote->expects($this->any())->method($m)->will($this->returnValue(10));
        }

        $service = $this->getMockedService($quote, array('getSessionObject', 'getGrandTotal'));
        $service->expects($this->once())->method('getGrandTotal')->will($this->returnValue(50.00));

        $customer = $this->getMock('\Store_Customer', array('getStoreCredit'));
        $customer->expects($this->any())->method('getStoreCredit')->will($this->returnValue(100));

        $controller = $this->getMock('\Storefront\CheckoutController',array('getLoggedInCustomer', 'applyCreditToQuote'), array($service));
        $controller->expects($this->any())->method('getLoggedInCustomer')->will($this->returnValue($customer));
        $controller->expects($this->any())->method('applyCreditToQuote')->with(50)->will($this->returnValue($customer));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->applyStoreCreditAction();
    }

    public function testStoreCreditFailsWithInsufficientCredit()
    {

        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"credit":"130"}'));

        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $customer = $this->getMock('\Store_Customer', array('getStoreCredit'));
        $customer->expects($this->any())->method('getStoreCredit')->will($this->returnValue(129.99));

        $controller = $this->getMock('\Storefront\CheckoutController',array('getLoggedInCustomer'), array($service));
        $controller->expects($this->any())->method('getLoggedInCustomer')->will($this->returnValue($customer));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->applyStoreCreditAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertStringStartsWith('Insufficient', $actual['title']);

    }

    public function testStoreCreditReturnsSessionWhenEverythingIsAsItShouldBe()
    {

        $request = $this->getMock('Interspire_Request', array('getBody'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"credit":"5.55"}'));

        $response = $this->getMock('Interspire_Response');

        $expected = array(1, 2, 3);

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getSessionObject')->will($this->returnValue($expected));
        $service->expects($this->once())->method('getGrandTotal')->will($this->returnValue(100.00));

        $customer = $this->getMock('\Store_Customer', array('getStoreCredit'));
        $customer->expects($this->any())->method('getStoreCredit')->will($this->returnValue(55.5555));

        $controller = $this->getMock('\Storefront\CheckoutController',array('getQuote', 'getLoggedInCustomer', 'applyCreditToQuote'), array($service));
        $controller->expects($this->any())->method('getLoggedInCustomer')->will($this->returnValue($customer));
        $controller->expects($this->any())->method('applyCreditToQuote')->with(5.55)->will($this->returnValue(true));

        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->applyStoreCreditAction();

        $this->assertEquals($expected, $actual);

    }

    public function testPasswordHashFunctionReturnsCorrectStructure()
    {

        $class = new \ReflectionClass('\Storefront\CheckoutController');
        $method = $class->getMethod('getHashedPassword');
        $method->setAccessible(true);

        $service = $this->getMockedService();

        $obj = new \Storefront\CheckoutController($service);
        $actual = $method->invokeArgs($obj, array('password1'));

        $this->assertArrayHasKey('salt', $actual);
        $this->assertArrayHasKey('encpassword', $actual);

    }

    public function testGetAddressAction()
    {
        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->once())->method('getAddresses')->will($this->returnValue(array()));

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->addressAction();
        $this->assertEquals(array(), $actual);
    }

    public function testPutAddressActionFails()
    {
        $problem = new MethodNotAllowed(array(
            'detail'  => 'PUT is not allowed by this resource',
            'statsdKey' => 'method_not_allowed.PUT'
        ));

        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('PUT'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->addressAction();
        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testPostAddressActionForGuestShopperNonArrayInput()
    {
        $problem = new InvalidProperties(array(
            'detail' => 'Parameters should be in an array',
            'statsdKey' => 'params_should_be_array'
        ));

        $json = '{"email":"chaitanya.kuber@bigcommerce.com","type":["shipping","billing"],"full_name":"Chaitanya Kuber","address1":"1-3 Smail St","address2":"","city":"Ultimo","state_code":"NSW","zip_postcode":2007,"country_code":"AU","phone":43563663435}';

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue($json));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getControllerMock($this->getMockedService(), $request, $response);
        $actual = $controller->addressAction();

        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testPostAddressActionForGuestShopper()
    {
        $json = '[{"email":"chaitanya.kuber@bigcommerce.com","type":["shipping","billing"],"full_name":"Chaitanya Kuber","address1":"1-3 Smail St","address2":"","city":"Ultimo","state_code":"NSW","zip_postcode":2007,"country_code":"AU","phone":43563663435}]';
        $address = json_decode($json);

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue($json));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->once())->method('saveAddresses')->will($this->returnValue(true));
        $service->expects($this->once())->method('getAddresses')->will($this->returnValue($address));

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->addressAction();

        $this->assertEquals($address, $actual);
    }


    public function testShippingAction_GetShippingQuotes()
    {
        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('GET'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService($this->getQuoteMock());
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->shippingAction();
        $this->assertTrue(is_array($actual));
        $this->assertEquals(422, $actual['status']);
    }

    public function testShippingAction_PutFails()
    {
        $problem = new MethodNotAllowed(array(
            'detail'  => 'PUT is not allowed by this resource',
            'statsdKey' => 'method_not_allowed.PUT'
        ));

        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('PUT'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->shippingAction();
        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testShippingAction_SelectQuote()
    {
        $json = '{"shipping_quote_id":0,"shipping_address_id":"5333b62936044"}';

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue($json));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service
            ->expects($this->once())
            ->method('saveShippingMethods')
            ->with($this->equalTo(json_decode($json)))
            ->will($this->returnValue(true));
        $service
            ->expects($this->once())
            ->method('getSessionObject')
            ->will($this->returnValue(array()));

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->shippingAction();

        $this->assertEquals(array(), $actual);
    }

    public function testShippingAction_SelectQuote_Fail()
    {
        $json = '{shipping_address_id":"5333b62936044"}';
        $problem = new InvalidProperties(array(
            'detail' => 'Missing shipping quote or address identifiers',
            'statsdKey' => 'missing_quote_or_address_ids'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue($json));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->shippingAction();

        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testSessionAction()
    {
        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $service->expects($this->once())->method("getSessionObject")->will($this->returnValue(array()));

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->sessionAction(array('id' => $this->id));
        $this->assertEquals(array(), $actual);
    }

    public function testSessionAction_WithInvalidId()
    {
        $problem = new NotFound(array(
            'title'  => 'Checkout session could not be found',
            'detail' => 'Add one or more items to your cart to continue',
            'statsdKey' => 'session_not_found'
        ));

        $request = $this->getMock('Interspire_Request');
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->sessionAction(array('id' => '1'));
        $this->assertEquals($problem->toArray(), $actual);
    }

    public function testCustomerMessage_FailsWithNoMessage()
    {
        $problem = new InvalidProperties(array(
            'detail' => 'Missing message',
            'statsdKey' => 'missing_message'
        ));

        $service = $this->getMockedService();

        $request = $this->getMock('Interspire_Request', array('getMethod', 'getBody'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue('POST'));
        $request->expects($this->any())->method('getBody')->will($this->returnValue('{}'));

        $response = $this->getMock('Interspire_Response');

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->customerMessageAction();
        $this->assertEquals($problem->toArray(), $actual);

    }

    public function testCustomerMessage_FailsWithEmptyMessage()
    {
        $problem = new InvalidProperties(array(
            'detail' => 'Missing message',
            'statsdKey' => 'missing_message'
        ));

        $service = $this->getMockedService();
        $service->expects($this->once())->method('setCustomerMessage')->with('');

        $request = $this->getMock('Interspire_Request', array('getMethod', 'getBody'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue('POST'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"customer_message": ""}'));

        $response = $this->getMock('Interspire_Response');

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->customerMessageAction();

        $this->assertEquals(array('customer_message' => ''), $actual);

    }

    public function testCustomerMessage_POST_CallsServiceCorrectly()
    {
        $expected = array(
        	'customer_message' => 'I am a message'
        );

        $service = $this->getMockedService();
        $service->expects($this->once())->method('setCustomerMessage')->with('I am a message');

        $request = $this->getMock('Interspire_Request', array('getMethod', 'getBody'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $request->expects($this->once())->method('getBody')->will($this->returnValue(json_encode($expected)));

        $response = $this->getMock('Interspire_Response');

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->customerMessageAction();

    }

    public function testCustomerMessage_GET_CallsServiceCorrectly()
    {
        $expected = array(
            'customer_message' => 'Deliver next tuesday'
        );

        $service = $this->getMockedService();
        $service->expects($this->any())->method('getCustomerMessage')->with($expected['customer_message'])->will($this->returnValue($expected['customer_message']));

        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_GET));

        $response = $this->getMock('Interspire_Response');

        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->customerMessageAction();

    }

    public function testCouponsAction_WithNoCode()
    {

        $problem = new \Errors\InvalidProperties(array(
            'detail' => 'Missing coupon code',
            'statsdKey' => 'missing_coupon_code'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"foo":"bar"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->couponsAction();

        $this->assertEquals($problem->toArray(), $actual);

    }

    public function testCouponsAction_WithEmptyCode()
    {

        $problem = new \Errors\InvalidProperties(array(
            'detail' => 'Missing coupon code',
            'statsdKey' => 'missing_coupon_code'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":""}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->couponsAction();

        $this->assertEquals($problem->toArray(), $actual);

    }

    public function testCouponsAction_AppliesCouponViaService()
    {

        $coupon = '{
        "58EE1BBA62EF39B": {
            "id": "2",
            "code": "58EE1BBA62EF39B",
            "name": "10% off order total",
            "discountType": "1",
            "discountAmount": "10.0000",
            "expiresDate": "0",
            "totalDiscount": 49
        }
    }';

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":"58EE1BBA62EF39B"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService(null, array(
            'applyCoupon'
        ));
        $service->expects($this->once())->method('applyCoupon')->with("58EE1BBA62EF39B")->will($this->returnValue($coupon));

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->couponsAction();

        $this->assertEquals($coupon, $actual);

    }

    public function testCouponAction_DeletesCouponViaService()
    {
        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":"58EE1BBA62EF39B"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_DELETE));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService(null, array('removeCoupon'));
        $service->expects($this->once())->method('removeCoupon')->with("58EE1BBA62EF39B")->will($this->returnValue(array()));

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->couponsAction();

        $this->assertEquals($actual, array());

    }

    public function testCouponAction_WithExceptionThrownByQuote()
    {
        $coupon = '{
        "58EE1BBA62EF39B": {
            "id": "2",
            "code": "58EE1BBA62EF39B",
            "name": "10% off order total",
            "discountType": "1",
            "discountAmount": "10.0000",
            "expiresDate": "0",
            "totalDiscount": 49
        }
    }';

        $quote = $this->getMock('ISC_QUOTE', array('applyCoupon'));
        $quote->expects($this->any())->method('applyCoupon')->will($this->throwException(new \ISC_QUOTE_EXCEPTION('Some exceptional message')));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":"58EE1BBA62EF39B"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService($quote);
        $service->expects($this->any())->method('applyCoupon')->with("58EE1BBA62EF39B");

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->couponsAction();

        $this->assertEquals(422, (int)$actual['status']);
        $this->assertEquals('Some exceptional message', $actual['detail']);

    }

    public function testGiftCertificatesAction_WithNoCode()
    {

        $problem = new \Errors\InvalidProperties(array(
            'detail' => 'Missing gift certificate code',
            'statsdKey' => 'missing_certificate_code'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"foo":"bar"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->giftCertificatesAction();

        $this->assertEquals($problem->toArray(), $actual);

    }

    public function testGiftCertificatesAction_WithEmptyCode()
    {

        $problem = new \Errors\InvalidProperties(array(
                'detail' => 'Missing gift certificate code',
                'statsdKey' => 'missing_certificate_code'
        ));

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":""}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService();

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->giftCertificatesAction();

        $this->assertEquals($problem->toArray(), $actual);

    }

    public function testGiftCertificatesAction_AppliesGiftCertificateViaService()
    {

        $coupon = '{ "H33-W6I-R91-DWD" : { "amount" : "1000.0000",
          "balance" : "1000.0000",
          "code" : "H33-W6I-R91-DWD",
          "expiry" : "0",
          "id" : "1",
          "remaining" : 583,
          "used" : 417
        } }';

        $request = $this->getMock('Interspire_Request', array('getBody', 'getMethod'));
        $request->expects($this->once())->method('getBody')->will($this->returnValue('{"code":"H33-W6I-R91-DWD"}'));
        $request->expects($this->once())->method('getMethod')->will($this->returnValue(\Interspire_Request::HTTP_METHOD_POST));
        $response = $this->getMock('Interspire_Response');

        $service = $this->getMockedService(null, array('applyGiftCertificate'));
        $service->expects($this->once())->method('applyGiftCertificate')->with("H33-W6I-R91-DWD")->will($this->returnValue($coupon));

        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->giftCertificatesAction();

        $this->assertEquals($coupon, $actual);

    }

    public function testGetExpressCheckoutUrl_WithInvalidProvider()
    {
        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $mockQuote = $this->getQuoteMock();
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->once())->method('getSupportedPaymentProviders')->will($this->returnValue(array('')));
        $controller = $this->getControllerMock($service, $request, $response);
        $actual = $controller->expressCheckoutAction(array('provider'=>"taxidermy"));


        $this->assertEquals('Invalid payment provider', $actual['detail']);
    }

    public function testGetExpressCheckoutUrl_WithPaypalExpress()
    {
        $request = $this->getMock('Interspire_Request', array('getMethod'));
        $request->expects($this->any())->method('getMethod')->will($this->returnValue('POST'));
        $response = $this->getMock('Interspire_Response');

        $mockQuote = $this->getQuoteMock();
        $service = $this->getMockedService($mockQuote);
        $service->expects($this->exactly(1))->method('loadOrder')->will($this->returnValue(array('akofn')));
        $service->expects($this->exactly(1))->method('loadProvider')->will($this->returnValue($this->getMockCheckoutModule('paypalexpress', false, true)));
        $service->expects($this->once())->method('getSupportedPaymentProviders')->will($this->returnValue(array('checkout_paypalexpress')));
        $controller = $this->getControllerMock($service, $request, $response);

        $actual = $controller->expressCheckoutAction(array('provider'=>"paypalexpress"));
        $this->assertEquals('expressUrl', $actual['targetUrl']);
    }

}
