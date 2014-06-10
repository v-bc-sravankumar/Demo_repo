<?php

class Unit_Controllers_PaymentProviderControllerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PayPalController
     */
    protected $controller;

    public function testGetConfigInvalidProvider()
    {
        // Setup.
        $request = $this->getRequest();
        $this->setUpController($request, $mock);
        $request->setUserParam('provider','invalidprovider');

        // Trigger.
        $this->controller->indexAction();

        // Verify.
        $this->assertEquals(404, $request->getResponse()->getStatus());
    }

    public function testGetConfig()
    {
        // Create a mock for the underlying service.
        $mock = $this->getMock('\Services\Payments\PayPalExpress\PayPalExpress');
        $mock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue(''));

        // Setup.
        $request = $this->getRequest();
        $request->setUserParam('provider','paypalec');
        $this->setUpController($request, $mock);

        // Trigger.
        $this->controller->indexAction();

        // Verify.
        $this->assertEquals(200, $request->getResponse()->getStatus());
    }

    public function testInvalidVerb()
    {
        // Setup.
        $request = $this->getRequest('POST');
        $request->setUserParam('provider','paypalec');
        $this->setUpController($request);

        // Trigger.
        $this->controller->indexAction();

        // Verify.
        $this->assertEquals(405, $request->getResponse()->getStatus());
    }

    public function testDisabling()
    {
        // Create a mock for the underlying service.
        $mock = $this->getMock('\Services\Payments\PayPalExpress\PayPalExpress');
        $mock->expects($this->once())
            ->method('disable');

        // Setup.
        $request = $this->getRequest('DELETE');
        $request->setUserParam('provider','paypalec');
        $this->setUpController($request, $mock);

        // Trigger.
        $this->controller->disableAction();
    }

    public function testEnabling()
    {
        // Create a mock for the underlying service.
        $mock = $this->getMock('\Services\Payments\PayPalExpress\PayPalExpress');
        $mock->expects($this->once())
            ->method('enable');

        // Setup.
        $request = $this->getRequest('POST');
        $request->setUserParam('provider','paypalec');
        $this->setUpController($request, $mock);

        // Trigger.
        $this->controller->enableAction();
    }

    public function testUpdateConfig()
    {
        // Generate the expected value.
        $value = array("username" => 'foo');

        // Create a mock for the underlying service.
        $mock = $this->getMock('\Services\Payments\PayPalExpress\PayPalExpress');
        $mock->expects($this->once())
            ->method('update')
            ->with($this->equalTo($value))
            ->will($this->returnValue(''));

        // Setup.
        $request = $this->getRequest('PUT', '{"username": "foo"}');
        $this->setUpController($request, $mock);
        $request->setUserParam('provider','paypalec');

        // Trigger.
        $this->controller->indexAction();

        // Verify.
        $this->assertEquals(200, $request->getResponse()->getStatus());
    }

    /**
     * Convenience method for obtaining a request with the given method type and body.
     * @param string $method The HTTP verb for the request.
     * @param string $body The HTTP body data.
     * @return Interspire_Request An appropriately configured request.
     */
    private function getRequest($method = 'GET', $body = null)
    {
        return new Interspire_Request(null, null, null, array(
            'HTTP_ACCEPT' => 'application/json',
            'REQUEST_METHOD' => $method,
        ), $body);
    }

    /**
     * Configure the given request for use with our controller.
     * @param Interspire_Request $request The request to use.
     * @param mixed $mock A mock service instance.
     */
    private function setUpController($request, $serviceMock = null)
    {
        // Create a new controller instance.
        $this->controller = new PaymentProviderController();

        $this->controller->setService($serviceMock);

        // Configure the controller with our dummy request.
        $this->controller->setRequest($request);
        $this->controller->setResponse($request->getResponse());
    }
}