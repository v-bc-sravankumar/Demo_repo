<?php

namespace Integration\Controllers;

use Config\Secrets;
use Interspire_Request;
use Services\Bigcommerce\Auth\Error\InvalidOAuthStateException;
use Services\Bigcommerce\Auth\Error\InvalidPasswordException;
use Services\Bigcommerce\Auth\Error\MissingUserException;
use Store_Config;

class LoginControllerTest extends \Interspire_IntegrationTest
{

    protected $originalConfigs = array();

    public function setUp()
    {
        $preserveList = array(
            'StoreHash',
            'OAuth_ClientId',
            'OAuth_ClientSecret',
            'Feature_OAuthLogin',
            'PlanName',
        );
        foreach ($preserveList as $preserveKey) {
            $this->originalConfigs[$preserveKey] = Store_Config::get($preserveKey);
        }
    }

    public function tearDown()
    {
        // reload config after all the munging
        foreach ($this->originalConfigs as $key => $value) {
            Store_Config::override($key, $value);
        }

    }

    public function createController($authMock=null, $oauthClient = null)
    {
        return new \LoginController($authMock, $oauthClient);
    }

    public function testRedirectAction()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        // setup controller
        $controller = $this->createController();
        $controller->setRequest(new \Interspire_Request());
        $resp = new \Interspire_Response();
        $controller->setResponse($resp);

        // fire up
        $controller->redirectAction();
        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertEquals('/oauth2/stores/xxx/login', $parts['path']);

        parse_str($parts['query'], $query);

        // check for session_id
        $expectedSessionId = hash_hmac('SHA256', session_id(), Store_Config::get('OAuth_ClientSecret'));
        $this->assertEquals($expectedSessionId, $query['session_id']);

    }

    public function testStaffAction()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        // setup controller
        $controller = $this->createController();
        $controller->setRequest(new \Interspire_Request());
        $resp = new \Interspire_Response();
        $controller->setResponse($resp);

        // fire up
        $controller->staffAction();
        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertEquals('/oauth2/stores/xxx/login', $parts['path']);

        parse_str($parts['query'], $query);

        // check query string contains provider
        $this->assertArrayHasKey('provider', $query);
        $this->assertEquals('crowd', $query['provider']);

        // check for session_id
        $expectedSessionId = hash_hmac('SHA256', session_id(), Store_Config::get('OAuth_ClientSecret'));
        $this->assertEquals($expectedSessionId, $query['session_id']);
    }

    public function testLegacyloginActionWithValidPostData()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        $authToken = 'ec0e2603172c73a8b644bb9456c1ff6e';
        $email     = 'someone+user@example.com';
        $password  = 'password1';

        $authMock = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array('verifyPassword'));
        $authMock->expects($this->once())
          ->method('verifyPassword')
          ->with($this->equalTo($email), $this->equalTo($password))
          ->will($this->returnValue(array('auth_token' => $authToken)));

        $req = new \Interspire_Request(null, array(
            'username' => $email,
            'password' => $password,
        ));
        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController($authMock);
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->legacyloginAction();

        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertEquals('/oauth2/stores/xxx/login', $parts['path']);

        parse_str($parts['query'], $query);
        // check query string contains the expected auth token
        $this->assertArrayHasKey('auth_token', $query);
        $this->assertEquals('ec0e2603172c73a8b644bb9456c1ff6e', $query['auth_token']);

        // check for session_id
        $expectedSessionId = hash_hmac('SHA256', session_id(), Store_Config::get('OAuth_ClientSecret'));
        $this->assertEquals($expectedSessionId, $query['session_id']);
    }

    public function testLegacyloginWithInvalidPostData()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        $authToken = 'ec0e2603172c73a8b644bb9456c1ff6e';
        $email     = 'someone+user@example.com';
        $password  = 'wrongpassword';

        $authMock = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array('verifyPassword'));
        $authMock->expects($this->once())
          ->method('verifyPassword')
          ->with($this->equalTo($email), $this->equalTo($password))
          ->will($this->throwException(new MissingUserException()));

        $req = new \Interspire_Request(null, array(
            'username' => $email,
            'password' => $password,
        ));
        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController($authMock);
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->legacyloginAction();

        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertStringEndsWith('/admin', $parts['path']);
    }

    public function testLegacyIdleloginWithInvalidPostData()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        $authToken = 'ec0e2603172c73a8b644bb9456c1ff6e';
        $email     = 'someone+user@example.com';
        $password  = 'wrongpassword';

        $authMock = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array('verifyPassword'));
        $authMock->expects($this->once())
          ->method('verifyPassword')
          ->with($this->equalTo($email), $this->equalTo($password))
          ->will($this->throwException(new InvalidPasswordException()));

        $req = new \Interspire_Request(null , array(
                'username' => $email,
                'password' => $password,
                'loginForm' => 'idletimeout',
        ));
        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController($authMock);
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->legacyloginAction();

        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertStringEndsWith('/admin/login/idle', $parts['path']);
    }

    public function testInvalidStateException()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));

        $oauthClientMock = $this->getMock('\Services\Bigcommerce\Auth\OAuthClient', array('exchange'),
            array(uniqid(), uniqid(), array('test'), uniqid()));
        $oauthClientMock->expects($this->once())
            ->method('exchange')
            ->withAnyParameters()
            ->will($this->throwException(new InvalidOAuthStateException()));

        // setup controller
        $controller = $this->createController(null, $oauthClientMock);
        $parts = parse_url(Store_Config::get('ShopPathSSL'));
        $controller->setRequest(new \Interspire_Request(null, null, null, array('SERVER_NAME' => $parts['host'])));
        $resp = new \Interspire_Response();
        $controller->setResponse($resp);

        // fire up
        $controller->callbackAction();
        $location = $resp->getHeader('Location');
        $parts = parse_url($location);

        $this->assertEquals('/oauth2/stores/xxx/login', $parts['path']);

    }

    public function testInfoActionWithHAWKHeaders()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('OAuth_ClientId', uniqid(true));
        Store_Config::override('OAuth_ClientSecret', uniqid(true));
        Store_Config::override('PlanName', 'Gold');

        $secrets = new Secrets();
        $clientId = 'api_proxy';
        $clientSecret = $secrets->get('api.hmac_keys.api_proxy');

        $type = 'header';
        $host = 'example.com';
        $port = '80';
        $method = 'GET';
        $resource = '/admin/auth/info';
        $nonce = 'j4h3g2';
        $bodyHash = '';
        $ext = '';
        $ts = time();
        $normalizedRequest = 'hawk.1.' . $type . "\n".
            $ts . "\n".
            $nonce . "\n".
            $method . "\n".
            $resource . "\n".
            strtolower($host) . "\n".
            $port . "\n".
            $bodyHash . "\n".
            $ext . "\n";

        $mac = base64_encode(hash_hmac('sha256', $normalizedRequest, $clientSecret, true));
        $authString = 'Hawk id="' . $clientId .
            '", ts="' . $ts .
            '", nonce="' . $nonce .
            '", mac="' . $mac . '"';

        $server = array(
            'SERVER_NAME' => $host,
            'HTTPS' => 'on',
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $resource,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => $authString,
            'SERVER_PORT' => '80',
        );
        $req = new Interspire_Request(null, null, null, $server);

        $authMock = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array());

        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController($authMock);
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->infoAction();

        $info = json_decode($resp->getBody(), true);
        $this->assertEquals('Gold', $info['plan_name']);

    }

    public function testInfoActionWithoutHAWKHeaders()
    {
        // override config so we have a valid oauth client
        $req = new Interspire_Request(null, null, null, null);

        $authMock = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array());

        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController($authMock);
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->infoAction();

        $info = json_decode($resp->getBody(), true);
        $this->assertArrayNotHasKey('plan_name', $info);
        $this->assertArrayHasKey('store_hash', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('url', $info);
        $this->assertArrayHasKey('logo', $info);
        $this->assertArrayHasKey('ssl_host', $info);
        $this->assertArrayHasKey('primary_domain', $info);
        $this->assertArrayHasKey('alternate_urls', $info);

    }

}
