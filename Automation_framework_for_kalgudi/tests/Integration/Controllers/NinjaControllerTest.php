<?php

namespace Integration\Controllers;


use Config\Secrets;
use Interspire_Request;
use Store_Config;

class NinjaControllerTest extends \Interspire_IntegrationTest
{

    protected $originalConfigs = array();

    public function setUp()
    {
        $preserveList = array(
            'StoreHash',
            'Feature_WebhooksProduction',
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

    public function createController()
    {
        return new \NinjaController();
    }

    public function testFeatureFlagAction()
    {

        Store_Config::override('Feature_WebhooksProduction', false);

        $req = new Interspire_Request(array('feature' => 'WebhooksProduction', 'enable' => '1'), null, null, null);

        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController();
        $controller->setRequest($req);
        $controller->setResponse($resp);

        // fire up
        $controller->featureFlagAction();

        $response = json_decode($resp->getBody(), true);
        $this->assertTrue($response['success']);
        $this->assertTrue(\Store_Feature::isEnabled('WebhooksProduction'));

    }

    public function testCheckPermissionWithHAWKHeadersValidId()
    {

        $secrets = new Secrets();
        $clientId = 'hooks_registry';
        $clientSecret = $secrets->get('api.hmac_keys.hooks_registry');

        $type = 'header';
        $host = 'example.com';
        $port = '80';
        $method = 'GET';
        $resource = '/admin/ninja/feature-flag';
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

        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController();
        $controller->setRequest($req);
        $controller->setResponse($resp);

        $class = new \ReflectionClass($controller);
        $checkPermission = $class->getMethod('checkPermission');
        $checkPermission->setAccessible(true);
        $this->assertTrue($checkPermission->invoke($controller));

    }

    public function testCheckPermissionWithHAWKHeadersInvalidId()
    {

        $secrets = new Secrets();
        $clientId = 'api_proxy';
        $clientSecret = $secrets->get('api.hmac_keys.api_proxy');

        $type = 'header';
        $host = 'example.com';
        $port = '80';
        $method = 'GET';
        $resource = '/admin/ninja/feature-flag';
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

        $resp = new \Interspire_Response();

        // setup controller
        $controller = $this->createController();
        $controller->setRequest($req);
        $controller->setResponse($resp);

        $class = new \ReflectionClass($controller);
        $checkPermission = $class->getMethod('checkPermission');
        $checkPermission->setAccessible(true);
        $this->assertFalse($checkPermission->invoke($controller));

    }

}
