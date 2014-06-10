<?php

namespace Unit\Controllers;

class AccountConrollerTest extends \PHPUnit_Framework_TestCase
{

    public function registerParamsProvider()
    {
        return array(
            array(
                array(''),
                422,
                'Invalid email and password'
            ),
            array(
                array('email' => '', 'password' => ''),
                422,
                'Invalid email and password',
            ),
            array(
                array('email' => 'foo@bar.com', 'password' => ''),
                422,
                'Invalid password',
            ),
            array(
                array('email' => 'foo_bar.com', 'password' => 'password1'), // Note invalid password
                422,
                'Invalid email',
            ),
            array(
                array('email' => '', 'password' => 'password1'),
                422,
                'Invalid email',
            )
        );
    }

    /**
     * @dataProvider registerParamsProvider
     * @param unknown $body
     * @param unknown $status
     */
    public function testRegisterFailsWithMissingParameters($body, $status, $message)
    {

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', null, array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->registerAction();

        $this->assertEquals($status, $actual['status']);
        $this->assertEquals($message, $actual['detail']);

    }

    public function testRegisterFailsWithExistingEmail()
    {
        $body = array('email' => 'foo@bar.com', 'password' => 'password1');
        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', array('emailExists'), array($db));
        $controller->expects($this->once())->method('emailExists')->with($body['email'])->will($this->returnValue(new \Store_Customer));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->registerAction();

        $this->assertEquals(422, $actual['status']);
        $this->assertEquals('Already registered', $actual['detail']);
    }

    public function testRegistersNewUserIfEnoughDataPresent()
    {
        $body = array('email' => 'foo@bar.com', 'password' => 'password1');
        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('\Store_Customer', array('getId', 'getSalt'));
        $customer->expects($this->any())->method('getId')->will($this->returnValue(999));
        $customer->expects($this->any())->method('getSalt')->will($this->returnValue('bacon'));

        $controller = $this->getMock('\Storefront\AccountController', array('emailExists', 'registerNewUser', 'logIn'), array($db));
        $controller->expects($this->once())->method('emailExists')->with($body['email'])->will($this->returnValue(false));
        $controller->expects($this->once())->method('registerNewUser')->with($body['email'], $body['password'])->will($this->returnValue($customer));
        $controller->expects($this->once())->method('logIn')->with($customer)->will($this->returnValue(true));

        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->registerAction();

        $this->assertEquals(
            array(
                'registered' => true,
                'customer_token' => $customer->getStorefrontToken(),
            ), $actual);

    }

    public function testLogoutActionLogsOut()
    {
        $db = $this->getMock('\Db_Mysql');
        $controller = $this->getMock('\Storefront\AccountController', array('clearLoginIdentifier'), array($db));
        $controller->expects($this->once())->method('clearLoginIdentifier');
        $controller->setRequest(new \Interspire_Request);
        $controller->setResponse($this->getMock('Interspire_Response'));

        $actual = $controller->logoutAction();
        $this->assertEquals(true, $actual['logged_out']);
    }

    protected function getMockOrder($methods = null)
    {
        return $this->getMock('\Orders\Order', $methods);
    }

    /**
     * @dataProvider usernamePasswordCombo
     * @param unknown $body
     */
    public function testLoginActionBailsWithNoUsernameAndPassword($body)
    {
        $body = array();

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', null, array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->loginAction();

        $this->assertEquals(403, $actual['status']);
        $this->assertEquals('Invalid authentication details', $actual['title']);
        unset($db);

    }

    public function testSubscribeActionBailsIfNotLoggedIn()
    {
        $body = array();

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $controller = $this->getMock('\Storefront\AccountController', array('isLoggedIn'), array($db));
        $controller->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $actual = $controller->subscribeAction();

        $this->assertEquals(403, $actual['status']);

    }

    public function testSubscribeActionCallsCorrectMethodIfLoggedIn()
    {
        $body = array();

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('\Store_Customer');

        $controller = $this->getMock('\Storefront\AccountController', array('isLoggedIn', 'findCustomerByShopToken', 'subscribeToMailingList'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);

        $controller->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $controller->expects($this->once())->method('findCustomerByShopToken')->will($this->returnValue($customer));
        $controller->expects($this->once())->method('subscribeToMailingList')->with($customer);

        $actual = $controller->subscribeAction();

        $this->assertEquals(true, $actual['subscribed']);

    }

    public function usernamePasswordCombo()
    {
        return array(
            array(''),
            array('email' => 'foo@bar.com'),
            array('password' => 'password'),
            array('email' => 'foo@bar.com', 'password' => 'password'),
        );
    }

    public function testLoginActionStartsSessionWhenPasswordMatches()
    {
        $body = array(
            'email' => 'foo@bar.com',
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('Store_Customer', array('validatePassword', 'getId', 'save'));
        $customer->expects($this->once())->method('validatePassword')->with('password')->will($this->returnValue(true));
        $customer->expects($this->any())->method('getId')->will($this->returnValue(100));

        $quote = $this->getMock('ISC_QUOTE', array('setCustomerId', 'getMetaData'));
        $quote->expects($this->once())->method('setCustomerId')->with(100);
        $quote->expects($this->once())->method('getMetaData')->will($this->returnValue(array()));

        $controller = $this->getMock('\Storefront\AccountController',
            array('findCustomerByEmailAddress',
                  'startCustomerSession',
                  'stopTracking',
                  'getQuote'
                  ),
            array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $controller->expects($this->once())->method('findCustomerByEmailAddress')->with('foo@bar.com')->will($this->returnValue($customer));
        $controller->expects($this->once())->method('startCustomerSession')->with($customer);
        $controller->expects($this->once())->method('stopTracking')->with($quote);

        $actual = $controller->loginAction();

        $this->assertEquals(100, $actual['customer_id']);
        $this->assertEquals('foo@bar.com', $actual['email']);
        unset($db);

    }

    public function testLoginActionUsingInvalidPasswordAndImportPasswordIsEmpty()
    {
        $body = array(
            'email' => 'foo@bar.com',
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('Store_Customer', array('validatePassword', 'getImportPassword'));
        $customer->expects($this->once())->method('validatePassword')->with('password')->will($this->returnValue(false));
        $customer->expects($this->once())->method('getImportPassword')->will($this->returnValue(''));


        $controller = $this->getMock('\Storefront\AccountController', array('findCustomerByEmailAddress'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->once())->method('findCustomerByEmailAddress')->with('foo@bar.com')->will($this->returnValue($customer));

        $actual = $controller->loginAction();

        $this->assertEquals(403, $actual['status']);
        $this->assertEquals('Invalid authentication details', $actual['title']);
        unset($db);

    }

    public function testLoginActionUsingInvalidPasswordAndImportPasswordDoesntMatch()
    {
        $body = array(
            'email' => 'foo@bar.com',
            'password' => 'password',
        );

        $db = $this->getMock('\Db_Mysql');
        $request = new \Interspire_Request(array(), array(), array(), array(), json_encode($body));
        $response = $this->getMock('Interspire_Response');

        $customer = $this->getMock('Store_Customer', array('validatePassword', 'getImportPassword'));
        $customer->expects($this->once())->method('validatePassword')->with('password')->will($this->returnValue(false));
        $customer->expects($this->any())->method('getImportPassword')->will($this->returnValue('import:password'));

        $controller = $this->getMock('\Storefront\AccountController', array('findCustomerByEmailAddress', 'validateImportPassword'), array($db));
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->expects($this->once())->method('findCustomerByEmailAddress')->with('foo@bar.com')->will($this->returnValue($customer));
        $controller->expects($this->once())->method('validateImportPassword')->with('password', 'import:password')->will($this->returnValue(false));

        $actual = $controller->loginAction();

        $this->assertEquals(403, $actual['status']);
        $this->assertEquals('Invalid authentication details', $actual['title']);
        unset($db);

    }

}