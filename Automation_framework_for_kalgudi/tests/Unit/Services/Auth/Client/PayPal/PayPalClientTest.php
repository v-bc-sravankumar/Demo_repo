<?php

namespace Services\Auth\Client\PayPal;

use PayPal\Types\Common\ErrorData;
use PayPal\Types\Perm\GetAccessTokenResponse;
use PayPal\Types\Perm\GetBasicPersonalDataResponse;
use PayPal\Types\Perm\GetPermissionsResponse;
use PayPal\Types\Perm\PersonalData;
use PayPal\Types\Perm\RequestPermissionsResponse;
use PayPal\Types\Perm\PersonalDataList;
use Services\Auth\Client\AccessToken;
use Services\Auth\Client\RequestToken;

class PayPalClientTest extends \PHPUnit_Framework_TestCase
{
    public function testProductionAuthorizationUrlIsBuiltSuccessfully()
    {
        $value = 'AAAAAAAYEbOAzs.1TUEU';
        $client = new PayPalClient();
        $requestToken = new RequestToken($value);

        $url = $client->buildAuthorizationUrl($requestToken);

        $this->assertContains(PayPalClient::COMMAND_URL_PRODUCTION, $url);
        $this->assertContains($value, $url);
    }

    public function testTestAuthorizationUrlIsBuiltSuccessfully()
    {
        $value = 'AAAAAAAYEbOAzs.1TUEU';
        $service = new PayPalClient();
        $service->enableTestMode();
        $requestToken = new RequestToken($value);

        $url = $service->buildAuthorizationUrl($requestToken);

        $this->assertContains(PayPalClient::COMMAND_URL_SANDBOX, $url);
        $this->assertContains($value, $url);
    }

    public function testGetRequestTokenProducesSuccessfulResponse()
    {
        $tokenValue = 'AAAAAAAYEbOAzs.1TUEU';

        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new RequestPermissionsResponse();
        $responseInternal->token = $tokenValue;
        $service->expects($this->once())->method('RequestPermissions')->will($this->returnValue($responseInternal));

        $response = $client->getRequestToken('http://example.com', 'TEST_SCOPE');

        $expectedToken = new RequestToken($tokenValue);
        $this->assertTrue($response->getRequestToken()->sameValueAs($expectedToken));
        $this->assertNull($response->getErrorCode());
        $this->assertEmpty($response->getErrorMessage());
        $this->assertFalse($response->isError());
    }

    public function testGetRequestTokenProducesErroneousResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);
        $responseInternal = new RequestPermissionsResponse();

        $responseInternal->token = null;
        $errorData = new ErrorData();
        $errorData->errorId = 1;
        $errorData->message = 'Something went wrong';
        $responseInternal->error = array($errorData);

        $service->expects($this->once())->method('RequestPermissions')->will($this->returnValue($responseInternal));

        $response = $client->getRequestToken('http://example.com', 'TEST_SCOPE');

        $this->assertNull($response->getRequestToken());
        $this->assertEquals($errorData->errorId, $response->getErrorCode());
        $this->assertEquals($errorData->message, $response->getErrorMessage());
        $this->assertTrue($response->isError());
    }

    public function testGetAccessTokenProducesSuccessfulResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new GetAccessTokenResponse();
        $responseInternal->scope = array('TEST_SCOPE');
        $responseInternal->token = 'y3hOKweDLqtRv7hGxkH29I53UhZ0PVpEv3QfQYiureigvHUSIAM-KQ';
        $responseInternal->tokenSecret = '6tYeqzNSIgWVk7BR2U-9rvdClck';

        $service->expects($this->once())->method('GetAccessToken')->will($this->returnValue($responseInternal));

        $request = array(
            'request_token' => 'AAAAAAAYEbOAzs.1TUEU',
            'verification_code' => 'xzCGLRV4uI0KejuVdAyuSA',
        );

        $expectedToken = new AccessToken($responseInternal->token, $responseInternal->tokenSecret);
        $expectedScope = $responseInternal->scope;

        $response = $client->getAccessToken(new \Interspire_Request(array(), array(), $request));
        $this->assertEquals($expectedToken, $response->getAccessToken());
        $this->assertEquals($expectedScope, $response->getScope());
    }

    public function testGetAccessTokenProducesErroneousResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new GetAccessTokenResponse();
        $errorData = new ErrorData();
        $errorData->errorId = 1;
        $errorData->message = 'Something went wrong';
        $responseInternal->error = array($errorData);

        $service->expects($this->once())->method('GetAccessToken')->will($this->returnValue($responseInternal));

        $request = array(
            'request_token' => 'AAAAAAAYEbOAzs.1TUEU',
            'verification_code' => 'xzCGLRV4uI0KejuVdAyuSA',
        );

        $response = $client->getAccessToken(new \Interspire_Request(array(), array(), $request));

        $this->assertNull($response->getAccessToken());
        $this->assertNull($response->getScope());
        $this->assertEquals($errorData->errorId, $response->getErrorCode());
        $this->assertEquals($errorData->message, $response->getErrorMessage());
        $this->assertTrue($response->isError());
    }

    public function testGetTokenAuthoritiesProducesSuccessfulResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $scope = array('TEST_SCOPE');

        $responseInternal = new GetPermissionsResponse();
        $responseInternal->scope = $scope;

        $service->expects($this->once())->method('GetPermissions')->will($this->returnValue($responseInternal));

        $response = $client->getTokenScope(
            new AccessToken('Ss0cmmO-KZJR0lL.xkH29I53VARCYTaBFMNV9LvBVxrt0Vhw09XmAQ', 'tdyOnQJxOdCyBdJ1vtgpFPFsY1A'));

        $this->assertEquals($scope, $response->getScope());
    }

    public function testGetTokenAuthoritiesProducesErroneousResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new GetPermissionsResponse();
        $errorData = new ErrorData();
        $errorData->errorId = 1;
        $errorData->message = 'Something went wrong';
        $responseInternal->error = array($errorData);

        $service->expects($this->once())->method('GetPermissions')->will($this->returnValue($responseInternal));

        $response = $client->getTokenScope(
            new AccessToken('Ss0cmmO-KZJR0lL.xkH29I53VARCYTaBFMNV9LvBVxrt0Vhw09XmAQ', 'tdyOnQJxOdCyBdJ1vtgpFPFsY1A'));

        $this->assertNull($response->getScope());
        $this->assertEquals($errorData->errorId, $response->getErrorCode());
        $this->assertEquals($errorData->message, $response->getErrorMessage());
        $this->assertTrue($response->isError());
    }

    public function testGetOwnerDetailsProducesSuccessfulResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new GetBasicPersonalDataResponse();
        $responseInternal->response = new PersonalDataList();

        $payerId = new PersonalData();
        $payerId->personalDataKey = 'https://www.paypal.com/webapps/auth/schema/payerID';
        $payerId->personalDataValue = '3SBRLGMV9R7RA';
        $name = new PersonalData();
        $name->personalDataKey = 'http://axschema.org/contact/email';
        $name->personalDataValue = 'merchant@example.com';

        $responseInternal->response->personalData = array($payerId, $name);

        $service->expects($this->once())->method('GetBasicPersonalData')->will($this->returnValue($responseInternal));

        $response = $client->getOwnerDetails(
            new AccessToken('Ss0cmmO-KZJR0lL.xkH29I53VARCYTaBFMNV9LvBVxrt0Vhw09XmAQ', 'tdyOnQJxOdCyBdJ1vtgpFPFsY1A'));

        $this->assertEquals($name->personalDataValue, $response->getName());
        $this->assertEquals(array('payerId' => $payerId->personalDataValue), $response->getDetails());
        $this->assertFalse($response->isError());
    }

    public function testGetOwnerDetailsProducesErroneousResponse()
    {
        $service = $this->getMockBuilder('\PayPal\Service\PermissionsService')->disableOriginalConstructor()->getMock();
        $client = new PayPalClient();
        $client->setPermissionsService($service);

        $responseInternal = new GetPermissionsResponse();
        $errorData = new ErrorData();
        $errorData->errorId = 1;
        $errorData->message = 'Something went wrong';
        $responseInternal->error = array($errorData);

        $service->expects($this->once())->method('GetBasicPersonalData')->will($this->returnValue($responseInternal));

        $response = $client->getOwnerDetails(
            new AccessToken('Ss0cmmO-KZJR0lL.xkH29I53VARCYTaBFMNV9LvBVxrt0Vhw09XmAQ', 'tdyOnQJxOdCyBdJ1vtgpFPFsY1A'));

        $this->assertEquals($errorData->errorId, $response->getErrorCode());
        $this->assertEquals($errorData->message, $response->getErrorMessage());
        $this->assertTrue($response->isError());
    }

}
