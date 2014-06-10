<?php

namespace Unit\Controllers;

class AccountConrollerPasswordActionTest extends \PHPUnit_Framework_TestCase
{

    protected function getProtectedMethod($className, $methodName) {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    protected function getMockOrder($methods = null)
    {
        return $this->getMock('\Orders\Order', $methods);
    }

    protected function getMockControllerForPasswordTests($body)
    {
        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', array(
            'subscribeToMailingList',
            'subscribeToOrderMailingList',
            'updateOrderExtraInfo',
            'getOnetimeTokenFromSession',
            'clearOnetimeTokenFromSession',
            'loadCustomerFromOrder',
            'createCustomerFromOrder',
            'loadOrder'
        ), array($db));

        $controller->setRequest($request);
        $controller->setResponse($response);

        return $controller;

    }

    public function testPasswordActionFailsWithoutToken()
    {
        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), '');
        $response = $this->getMock('Interspire_Response');

        $controller = new \Storefront\AccountController($db);
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->passwordAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertEquals('Incorrect token', $actual['title']);

        unset($db);
    }

    public function testPasswordActionContainsOrderId()
    {

        $body = array(
            'onetime_token' => 'token-123',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession'), array($db));
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->passwordAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertEquals('Missing order id', $actual['title']);

        unset($db);

    }

    public function testPasswordActionContainsPassword()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 1,
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession'), array($db));
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->passwordAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertEquals('Missing customer password', $actual['title']);

    }

    public function testPasswordActionFailsIfCantFindCustomer()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 1,
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');
        $request->setResponse($response);

        $controller = $this->getMockControllerForPasswordTests(array()); // $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession', 'loadOrder', 'loadCustomerFromOrder'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $order = $this->getMockOrder();

        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->expects($this->any())->method('loadOrder')->will($this->returnValue($order));
        $controller->expects($this->any())->method('loadCustomerFromOrder')->with($order)->will($this->returnValue(false));

        $actual = $controller->passwordAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertEquals('Customer not found', $actual['title']);

        unset($db);

    }

    public function testPasswordActionSavesNewPasswordCorrectly()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 1,
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('Store_Customer', array('save', 'setPassword', 'setSalt', 'setPasswordHash'));
        $customer->expects($this->once())->method('setPassword')->with('password');
        $customer->expects($this->any())->method('setSalt')->will($this->returnValue($customer));
        $customer->expects($this->any())->method('setPasswordHash')->will($this->returnValue($customer));
        $customer->expects($this->once())->method('save')->will($this->returnValue(true));

        $order = $this->getMockOrder(array('getId', 'getCustomerId'));
        $order->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $controller = $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession', 'clearOnetimeTokenFromSession', 'loadOrder', 'loadCustomerFromOrder'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->expects($this->once())->method('clearOnetimeTokenFromSession');
        $controller->expects($this->once())->method('loadOrder')->will($this->returnValue($order));
        $controller->expects($this->once())->method('loadCustomerFromOrder')->with($order)->will($this->returnValue($customer));

        $actual = $controller->passwordAction();

        $this->assertEquals(true, $actual);

        unset($db);

    }

    public function testPasswordActionCallsCreateCustomerFromOrder()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 1,
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $order = $this->getMockOrder(array('getCustomerId'));
        $order->expects($this->any())->method('getCustomerId')->will($this->returnValue(0));

        $controller = $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession', 'loadOrder', 'createCustomerFromOrder', 'loadCustomerFromOrder'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->expects($this->any())->method('loadOrder')->will($this->returnValue($order));
        $controller->expects($this->once())->method('createCustomerFromOrder')->with($order);
        $controller->expects($this->never())->method('loadCustomerFromOrder');

        $controller->passwordAction();

    }

    public function testCreateCustomerFromOrder()
    {
        $orderRow = array(
            'orderid' => 186,
            'ordbillfirstname' => 'Tim',
            'ordbilllastname' => 'Massey',
            'ordbillphone' => '1123456',
            'ordbillemail' => 'tim.massey@bigcommerce.com',
        );

        $order = new \Orders\Order($orderRow);

        $body = array();

        $controller = $this->getMockControllerForPasswordTests(array());

        $createCustomerFromOrder = $this->getProtectedMethod('Storefront\AccountController', 'createCustomerFromOrder');
        $actualCustomer = /* @var $actualCustomer \Store_Customer */ $createCustomerFromOrder->invokeArgs($controller, array($order));

        $this->assertEquals($orderRow['ordbillfirstname'], $actualCustomer->getFirstName());
        $this->assertEquals($orderRow['ordbilllastname'], $actualCustomer->getLastName());
        $this->assertEquals($orderRow['ordbillemail'], $actualCustomer->getEmail());
        $this->assertEquals($orderRow['ordbillphone'], $actualCustomer->getPhone());

    }

    public function testPasswordActionCallsLoadCustomerFromOrder()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 1,
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $order = $this->getMockOrder(array('getCustomerId'));
        $order->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $controller = $this->getMock('\Storefront\AccountController', array('getOnetimeTokenFromSession', 'loadOrder', 'createCustomerFromOrder', 'loadCustomerFromOrder'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->expects($this->any())->method('loadOrder')->will($this->returnValue($order));
        $controller->expects($this->once())->method('loadCustomerFromOrder')->with($order);
        $controller->expects($this->never())->method('createCustomerFromOrder');

        $controller->passwordAction();

    }

    public function testPasswordActionSubscribesWhenTicked()
    {
        $body = array(
                'onetime_token' => 'token-123',
                'order_id' => 111,
                'password' => 'password',
                'subscribe' => 1
            );

        $extraInfo = array(
            'mail_format_preference' => \Interspire_EmailIntegration_Subscription::FORMAT_PREF_NONE,
            'join_mailing_list' => true,
            'join_order_list' => true,
        );

        $order = $this->getMockOrder(array('getCustomerId'));
        $order->expects($this->once())->method('getCustomerId')->will($this->returnValue(1));

        $customer = $this->getMock('\Store_Customer', array('save'));
        $customer->expects($this->once())->method('save')->will($this->returnValue(true));

        $controller = $this->getMockControllerForPasswordTests($body);
        $controller->expects($this->any())->method('getOnetimeTokenFromSession')->will($this->returnValue($body['onetime_token']));
        $controller->expects($this->any())->method('loadOrder')->will($this->returnValue($order));
        $controller->expects($this->once())->method('loadCustomerFromOrder')->with($order)->will($this->returnValue($customer));
        $controller->expects($this->once())->method('subscribeToMailingList')->with($customer);
        $controller->expects($this->once())->method('subscribeToOrderMailingList')->with($body['order_id']);
        $controller->expects($this->once())->method('updateOrderExtraInfo')->with($body['order_id'], $extraInfo);

        $actual = $controller->passwordAction();

    }

    public function testPasswordActionDoesntSubscribeWhenNotTicked()
    {
        $body = array(
            'onetime_token' => 'token-123',
            'order_id' => 111,
            'password' => 'password',
            'subscribe' => 0
        );

        $extraInfo = array(
            'mail_format_preference' => \Interspire_EmailIntegration_Subscription::FORMAT_PREF_NONE,
            'join_mailing_list' => true,
            'join_order_list' => true,
        );

        $customer = $this->getMock('\Store_Customer', array('save'));

        $controller = $this->getMockControllerForPasswordTests($body);

        $order = $this->getMockOrder(array('getCustomerId'));
        $order
            ->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $controller
            ->expects($this->any())
            ->method('getOnetimeTokenFromSession')
            ->will($this->returnValue($body['onetime_token']));

        $controller
            ->expects($this->any())
            ->method('loadOrder')
            ->will($this->returnValue($order));

        $controller->expects($this->once())->method('loadCustomerFromOrder')->with($order)->will($this->returnValue($customer));

        $controller->expects($this->never())->method('subscribeToMailingList');
        $controller->expects($this->never())->method('subscribeToOrderMailingList');
        $controller->expects($this->never())->method('updateOrderExtraInfo');

        $actual = $controller->passwordAction();

    }

}
