<?php

namespace Integration\Services\Bigcommerce\Auth;

use Services\Bigcommerce\Auth\OAuthClient;
use Services\Bigcommerce\Auth\OAuthSession;
use Store_Config;

class OAuthClientTest extends \Interspire_IntegrationTest
{

	protected $originalConfigs = array();

	public function setUp()
	{
		$preserveList = array(
			'StoreHash',
			'OAuth_ClientId',
			'OAuth_ClientSecret',
			'Feature_OAuthLogin',
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

	public function testCreate()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = OAuthClient::create('client_id', 'client_secret', $scopes, 'http://example.org/callback');
		$this->assertEquals('client_id', $client->getClientId());
		$this->assertEquals('client_secret', $client->getClientSecret());
		$this->assertEquals($scopes, $client->getScopes());
		$this->assertEquals('http://example.org/callback', $client->getCallbackUrl());
	}

	public function testCreateFromConfig()
	{
		Store_Config::override('StoreHash', 'xxx');
		Store_Config::override('OAuth_ClientId', 'xxx_client_id');
		Store_Config::override('OAuth_ClientSecret', 'xxx_client_secret');
		$scopes = OAuthClient::getStoreScopes();
		$callbackUrl = OAuthClient::getStoreCallbackUrl();

		$client = OAuthClient::createFromConfig();

		$this->assertEquals(Store_Config::get('OAuth_ClientId'), $client->getClientId());
		$this->assertEquals(Store_Config::get('OAuth_ClientSecret'), $client->getClientSecret());
		$this->assertEquals($scopes, $client->getScopes());
		$this->assertEquals($callbackUrl, $client->getCallbackUrl());

	}

	public function testIsValidWhenValid()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = OAuthClient::create('client_id', 'client_secret', $scopes, 'http://example.org/callback');
		$this->assertTrue($client->isValid());
	}

	public function testIsValidMissingClientId()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = OAuthClient::create('', 'client_secret', $scopes, 'http://example.org/callback');
		$this->assertFalse($client->isValid());
	}

	public function testIsValidMissingClientSecret()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = OAuthClient::create('client_id', '', $scopes, 'http://example.org/callback');
		$this->assertFalse($client->isValid());
	}

	public function testIsValidMissingScopes()
	{
		$client = OAuthClient::create('client_id', 'client_secret', array(), 'http://example.org/callback');
		$this->assertFalse($client->isValid());
	}

	public function testIsValidMissingCallbackUrl()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = OAuthClient::create('client_id', 'client_secret', $scopes, '');
		$this->assertFalse($client->isValid());
	}

	public function testExchange()
	{
		$scopes = explode(' ', 'scope1 scope2 scope3');
		$client = $this->getMock('\Services\Bigcommerce\Auth\OAuthClient', array('createHttpClient'), array(
			'client_id', 'client_secret', $scopes, 'http://example.org/callback'
		));

		// mock out the bloody request
		$response = null;
		$httpClient = $this->getMock('\Interspire_Http_Client', array('post', 'getResponse'));

		// mock post
		$httpClient->expects($this->any())->method('post')
			->withAnyParameters()
			->will($this->returnCallback(function($uri, $payload = null) use (&$response, $httpClient) {

				$results = array(
					'access_token' => array(
						'user' => array(
							'id' => 1,
							'username' => 'test@example.com',
							'email' => 'test@example.com',
							'scopes_list' => $payload['scope'],
						),
						'scope' => $payload['scope'],
						'client_id' => $payload['client_id'],
						'access_token' => uniqid(),
					),
				);
				$response = new \Interspire_Http_Response(200, array(),
					json_encode($results));
				return $httpClient;
			}));

		$httpClient->expects($this->any())->method('getResponse')
			->withAnyParameters()
			->will($this->returnCallback(function() use (&$response) {
				return $response;
			}));

		$client->expects($this->any())
			->method('createHttpClient')
			->withAnyParameters()
			->will($this->returnValue($httpClient));

		// generate state
		$state = OAuthClient::createState(implode(' ', $scopes), array('lol' => 'cake'));

		// do exchange
		$access = $client->exchange(array(
			'code' => uniqid(),
			'state' => $state,
			'scope' =>implode(' ', $scopes),
		));

		$this->assertArrayHasKey('state_data', $access);

		$stateData = $access['state_data'];

		$this->assertArrayHasKey('lol', $stateData);
		$this->assertEquals('cake', $stateData['lol']);

		$this->assertArrayHasKey('access_token', $access);
		$oauthSession = OAuthSession::create($access['access_token']);

		$this->assertEquals('test@example.com', $oauthSession->getUser()->getEmail());
		$this->assertNotEmpty($oauthSession->getAccessToken());
		$this->assertNotEmpty($oauthSession->getScopes());

	}

	public function testCreateState()
	{
		$state = OAuthClient::createState('test1 test2', null);

		$stateData = $_SESSION[OAuthClient::getStateCacheKey($state)];

		$this->assertNotEmpty($stateData);
	}

	public function testValidateStateWhenValid()
	{
		$state = OAuthClient::createState('test1 test2', 'test_data');
		$stateData = OAuthClient::validateState($state, 'test1 test2');
		$this->assertEquals('test_data', $stateData);
	}

	/**
	 * @expectedException \Services\Bigcommerce\Auth\Error\InvalidOAuthStateException
	 */
	public function testValidateStateWhenInvalid()
	{
		OAuthClient::validateState('invalid_state', 'test1 test2');
	}

	public function testGetStoreScopes()
	{
		Store_Config::override('StoreHash', 'test_store', true);
		$this->assertEquals(array(
			'users_basic_information',
			'store_login',
			'store_share',
			'store_hooks',
			'store_apps',
			'store_installs',
			'store_users',
			'store_v2_default',
			'store_v2_products',
			'store_v2_orders',
			'store_v2_shipping',
			'store_v2_customers',
			'store_v2_marketing',
			'store_v2_content',
			'store_v2_themes',
			'store_v2_information',
		), OAuthClient::getStoreScopes());
	}

}
