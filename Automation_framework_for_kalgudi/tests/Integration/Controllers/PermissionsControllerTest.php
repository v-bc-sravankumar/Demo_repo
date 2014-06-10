<?php

use Services\Auth\Client\GetRequestTokenResponse;
use Services\Auth\Client\RequestToken;
use Services\Auth\Client\PayPal\PayPalClient;
use Services\Auth\RemoteServiceException;

class PermissionsControllerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @param $requestedScope
     * @param $expectedScope
     * @dataProvider provideScopeData
     */
    public function testRequestActionAcceptsScalarAndArrayScopes($requestedScope, $expectedScope)
    {
        $controller = new PermissionsController();
        $request = array(
            'server' => PayPalClient::SERVICE_NAME,
            'scope' => $requestedScope,
        );
        $request = new Interspire_Request(array(), array(), $request);
        $mockResponse = $this->getMock('Interspire_Response');
        $request->setResponse($mockResponse);
        $controller->setResponse($mockResponse);
        $controller->setRequest($request);

        $controller->setConfigClass($this->getMockConfigClassForGet('ShopPath', 'http://example.com'));

        $getRequestTokenResponse = new GetRequestTokenResponse(new RequestToken('AAAAAAAYEbOAzs.1TUEU'));

        $mockPayPalClient = $this->getMock('\Services\Auth\Client\PayPal\PayPalClient',
            array('getRequestToken'));
        $mockPayPalClient->expects($this->once())
            ->method('getRequestToken')
            ->with('http://example.com/admin/permissions/accept?server=PayPalPermissions', $expectedScope)
            ->will($this->returnValue($getRequestTokenResponse));

        $authorizationServices = array(
            'PayPalPermissions' => $mockPayPalClient,
        );
        $controller->setAuthorizationServices($authorizationServices);

        $controller->requestAction();
    }

    public function testRequestActionPassesParametersToCallbackUrl()
    {
        $scope = 'TEST_SCOPE';
        $server = 'PayPalPermissions';
        $testMode = '1';
        $redirectKey = 'Payments.PayPalExpress';

        $controller = new PermissionsController();
        $request = array(
            'server' => $server,
            'scope' => $scope,
            'test-mode' => $testMode,
            'redirect-key' => $redirectKey,
        );
        $request = new Interspire_Request(array(), array(), $request);
        $mockResponse = $this->getMock('Interspire_Response');
        $request->setResponse($mockResponse);
        $controller->setResponse($mockResponse);
        $controller->setRequest($request);

        $controller->setConfigClass($this->getMockConfigClassForGet('ShopPath', 'http://example.com'));

        $getRequestTokenResponse = new GetRequestTokenResponse(new RequestToken('AAAAAAAYEbOAzs.1TUEU'));

        $mockPayPalClient = $this->getMock('\Services\Auth\Client\PayPal\PayPalClient',
            array('getRequestToken'));
        $mockPayPalClient->expects($this->once())
            ->method('getRequestToken')
            ->with($this->logicalAnd(
                $this->stringContains("server={$server}"),
                $this->stringContains("test-mode={$testMode}"),
                $this->stringContains("redirect-key={$redirectKey}")), array($scope))
            ->will($this->returnValue($getRequestTokenResponse));

        $authorizationServices = array(
            'PayPalPermissions' => $mockPayPalClient,
        );
        $controller->setAuthorizationServices($authorizationServices);

        $controller->requestAction();
    }

    public function testInvalidServerParameterToRequestActionProduces404Response()
    {
        $controller = new PermissionsController();
        $request = array(
            'server' => 'InvalidService',
            'scope' => 'TEST_SCOPE',
        );
        $request = new Interspire_Request(array(), array(), $request);
        $response = $this->getMock('Interspire_Response');
        $request->setResponse($response);

        $response->expects($this->once())->method('setStatus')->with(404);

        $controller->setRequest($request);
        $controller->setResponse($response);

        $controller->requestAction();
    }

    public function testErrorFromServiceDuringRequestActionProduces500Response()
    {
        $scope = 'TEST_SCOPE';
        $server = 'PayPalPermissions';

        $controller = new PermissionsController();
        $request = array(
            'server' => $server,
            'scope' => $scope,
        );
        $request = new Interspire_Request(array(), array(), $request);
        $response = $this->getMock('Interspire_Response');
        $request->setResponse($response);
        $controller->setResponse($response);
        $controller->setRequest($request);

        $controller->setConfigClass($this->getMockConfigClassForGet('ShopPath', 'http://example.com'));

        $getRequestTokenResponse = new GetRequestTokenResponse();
        $getRequestTokenResponse->setErrorCode(10000);
        $getRequestTokenResponse->setErrorMessage('Something bad happened.');

        $mockPayPalClient = $this->getMock('\Services\Auth\Client\PayPal\PayPalClient',
            array('getRequestToken'));
        $mockPayPalClient->expects($this->once())
            ->method('getRequestToken')
            ->will($this->returnValue($getRequestTokenResponse));

        $authorizationServices = array(
            'PayPalPermissions' => $mockPayPalClient,
        );
        $controller->setAuthorizationServices($authorizationServices);

        $response->expects($this->once())->method('setStatus')->with(500);

        $controller->requestAction();
    }

    public function testServiceExceptionDuringRequestActionProduces500Response()
    {
        $controller = new PermissionsController();
        $request = array(
            'server' => 'PayPalPermissions',
            'scope' => 'TEST_SCOPE',
        );
        $request = new Interspire_Request(array(), array(), $request);
        $response = $this->getMock('Interspire_Response');
        $request->setResponse($response);
        $controller->setResponse($response);
        $controller->setRequest($request);

        $controller->setConfigClass($this->getMockConfigClassForGet('ShopPath', 'http://example.com'));

        $mockPayPalClient = $this->getMock('\Services\Auth\Client\PayPal\PayPalClient',
            array('getRequestToken'));
        $mockPayPalClient->expects($this->once())
            ->method('getRequestToken')
            ->will($this->throwException(new RemoteServiceException));

        $authorizationServices = array(
            'PayPalPermissions' => $mockPayPalClient,
        );
        $controller->setAuthorizationServices($authorizationServices);

        $response->expects($this->once())->method('setStatus')->with(500);

        $controller->requestAction();
    }

    private function getMockConfigClassForGet($key, $value)
    {
        $mockConfigClass = $this->getMockClass('\Store_Config', array('get'));
        $mockConfigClass::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo($key))
            ->will($this->returnValue($value));
        return $mockConfigClass;
    }

    public function provideScopeData()
    {
        $arrayScope = array('ARRAY_SCOPE_1', 'ARRAY_SCOPE_2', 'ARRAY_SCOPE_3', 'ARRAY_SCOPE_4');
        $scalarScope = 'SCALAR_SCOPE';
        return array(
            array($arrayScope, $arrayScope),
            array($scalarScope, array($scalarScope)),
        );
    }
}