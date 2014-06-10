<?php


namespace Integration\Services\Bigcommerce\Auth;

use Config\Environment;
use Config\Secrets;
use Services\Bigcommerce\Auth\Entity\Right;
use \Services\Bigcommerce\Auth\AuthService;
use Store_Config;

class AuthServiceTest extends \Interspire_IntegrationTest
{

	/**
	 * @var AuthServiceHelper
	 */
	protected $authServiceHelper = null;

	public function __construct()
	{
		$this->authServiceHelper = new AuthServiceHelper();
	}

	public function tearDown()
	{
		// reset the data source
		$this->authServiceHelper->resetDataStore();
		// reload config after all the munging
		Store_Config::cancelAll();
	}

	/**
	 * @return \Services\Bigcommerce\Auth\AuthService
	 */
	protected function getAuthService()
	{
		return $this->authServiceHelper->createAuthService();
	}

	public function testCreateFromEnvironment()
	{
		$authService = AuthService::createFromEnvironment();

		$secrets = new Secrets();
		$this->assertEquals($secrets->get('auth.client_id'), $authService->getClientId());
		$this->assertEquals($secrets->get('auth.client_secret'), $authService->getClientSecret());
		$this->assertEquals($secrets->get('auth.access_token'), $authService->getClientToken());
	}

	public function testCreateUser()
	{
		$expected = array(
			'id' => 1,
			'username' => 'test@example.com',
			'email' => 'test@example.com',
		);

		$authService = $this->getAuthService();
		$user = $authService->createUser($expected['email'], 'password');

		$this->assertEquals($expected['id'], $user->getId());
		$this->assertEquals($expected['username'], $user->getUsername());
		$this->assertEquals($expected['email'], $user->getEmail());
	}

	public function testGetUser()
	{
		// setup the data store
		$expected = array(
			'id' => 1,
			'username' => 'test@example.com',
			'email' => 'test@example.com',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][1] = $expected;

		$authService = $this->getAuthService();
		$user = $authService->getUser(1);
		$this->assertEquals($expected['id'], $user->getId());
		$this->assertEquals($expected['username'], $user->getUsername());
		$this->assertEquals($expected['email'], $user->getEmail());
		$this->assertEquals($expected['scopes_list'], $user->getScopesList());
	}

	public function testFindUserByEmail()
	{
		$expected = array(
			'id' => 2,
			'username' => 'test@example.com',
			'email' => 'test@example.com',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][1] = array(
			'id' => 1,
			'username' => 'excluded@example.com',
			'email' => 'excluded@example.com',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][2] = $expected;

		$authService = $this->getAuthService();
		$user = $authService->findUserByEmail('test@example.com');
		$this->assertEquals($expected['email'], $user->getEmail());
	}

	public function testVerifyPasswordValid()
	{

		$this->authServiceHelper->dataStore['users'][1] = array(
			'id' => 1,
			'username' => 'test@example.com',
			'email' => 'test@example.com',
			'password' => 'password',
			'scopes_list' => 'test1 test2',
		);

		$authService = $this->getAuthService();
		$user = $authService->verifyPassword('test@example.com', 'password');
		$this->assertNotEmpty($user);
		$this->assertEquals($this->authServiceHelper->dataStore['users'][1], $user);

	}

	/**
	 * @expectedException \Services\Bigcommerce\Auth\Error\MissingUserException
	 */
	public function testVerifyPasswordWithMissingUser()
	{
		$this->getAuthService()->verifyPassword('missing-user@example.com', 'password');
	}

	/**
	 * @expectedException \Services\Bigcommerce\Auth\Error\InvalidPasswordException
	 */
	public function testVerifyPasswordWithInvalidPassword()
	{
		$this->authServiceHelper->dataStore['users'][1] = array(
			'id' => 1,
			'username' => 'test@example.com',
			'email' => 'test@example.com',
			'password' => 'password',
			'scopes_list' => 'test1 test2',
		);

		$this->getAuthService()->verifyPassword('test@example.com', 'wrong');
	}

	public function testCreateScope()
	{
		$expectedRights = array(
			array(
				'http_method_id' => 'get',
				'pattern' => '/test',
			),
		);
		$authService = $this->getAuthService();
		$scope = $authService->createScope('test_scope', $expectedRights);
		$this->assertEquals('test_scope', $scope->getName());
		/* @var Right $right */
		$right = current($scope->getRights());
		$this->assertEquals($expectedRights[0]['http_method_id'], $right->getHttpMethod());
		$this->assertEquals($expectedRights[0]['pattern'], $right->getPattern());
	}

	public function testBootstrapStore()
	{

		Store_Config::schedule('StoreHash', 'xxx');
		$authService = $this->getAuthService();
		$resp = $authService->bootstrapStore('http://example.com');
		$this->assertArrayHasKey('access_token', $resp);
		$access = $resp['access_token'];
		$this->assertArrayHasKey('access_token', $access);
		$this->assertArrayHasKey('user', $access);
		$this->assertArrayHasKey('scope', $access);
		$this->assertArrayHasKey('client_id', $access);
		$this->assertArrayHasKey('client_secret', $access);

	}

}
