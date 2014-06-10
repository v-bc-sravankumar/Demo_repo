<?php

class Unit_Lib_Store_Api_Authentication extends Interspire_IntegrationTest
{
	/**
	* @expectedException Store_Api_Exception_Authentication_CredentialsNotSupplied
	*/
	public function testAuthTypeNotSupplied()
	{
		$request = new Interspire_Request(null, null, null, array('HTTPS' => 'on'));
		$api = new Store_Api();
		$api->authenticate($request);
	}

	/**
	* @expectedException Store_Api_Exception_Authentication_CredentialsNotSupplied
	*/
	public function testCredentialsNotSupplied()
	{
		$server = array(
			'AUTH_TYPE' => 'Basic',
			'HTTPS' => 'on',
		);

		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();
		$api->authenticate($request);
	}

	/**
	* @expectedException Store_Api_Exception_Authentication_InvalidCredentials
	*/
	public function testInvalidCredentials()
	{
		$server = array(
			'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode("admin:foo"),
			'HTTPS' => 'on',
		);

		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();
		$api->authenticate($request);
	}

	public function testValidCredentials()
	{
		$key = $this->getApiKey();

		$server = array(
			'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode("admin:$key"),
			'HTTPS' => 'on',
		);

		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();
		$api->authenticate($request);
	}

	public function testAuthTypeDefaultsToBasic()
	{
		$key = $this->getApiKey();

		$server = array(
			'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode("admin:$key"),
			'HTTPS' => 'on',
		);

		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();
		$api->authenticate($request);
	}

	public function testValidHmacCredentials()
	{
		$secrets = new Config\Secrets();
		$clientId = 'api_proxy';
		$clientSecret = $secrets->get('api.hmac_keys.api_proxy');

		$type = 'header';
		$host = 'example.com';
		$port = '443';
		$method = 'GET';
		$resource = '/api/v2/products/1';
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
		);
		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();

		$api->authenticate($request);
	}

	public function testValidHawkAuthAssignsHawkResponseToRequestObject()
	{
		// set up the valid hawk request
		$secrets = new Config\Secrets();
		$clientId = 'api_proxy';
		$clientSecret = $secrets->get('api.hmac_keys.api_proxy');

		$type = 'header';
		$host = 'example.com';
		$port = '443';
		$method = 'GET';
		$resource = '/api/v2/products/1';
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
		);
		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();
		$api->authenticate($request);

		$this->assertInstanceOf('Dflydev\Hawk\Server\Response', $request->getUserParam('hawk_response'));
	}

	/**
	 * @expectedException Store_Api_Exception_Authentication_InvalidHawkAuthorization
	 */
	public function testHmacInvalidCredentials()
	{
		$host = 'example.com';
		$method = 'GET';
		$resource = '/api/v2/products/1';
		$nonce = 'j4h3g2';
		$ts = time();
		$authString = 'Hawk id="' . $clientId .
			'", ts="' . $ts .
			'", nonce="' . $nonce .
			'", mac="INVALID MAC!"';

		$server = array(
			'SERVER_NAME' => $host,
			'HTTPS' => 'on',
			'REQUEST_METHOD' => $method,
			'REQUEST_URI' => $resource,
			'CONTENT_TYPE' => 'application/json',
			'HTTP_AUTHORIZATION' => $authString,
		);
		$request = new Interspire_Request(null, null, null, $server);
		$api = new Store_Api();

		$api->authenticate($request);
	}
}
