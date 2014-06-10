<?php

use Integration\Services\Bigcommerce\Auth\AuthServiceHelper;

class Unit_Lib_Store_Api_Version_2_Resource_Token extends Interspire_IntegrationTest
{

	protected $originalConfigs = array();

	/**
	 * @var AuthServiceHelper
	 */
	public $authServiceHelper = null;

	public function __construct()
	{
		$this->authServiceHelper = new AuthServiceHelper();
	}

	private function _getResource()
	{
		$that = $this;
		$controller = $this->getMock('\\Store_Api_Version_2_Resource_Token', array('getAuthService'), array());
		$controller->expects($this->any())
			->method('getAuthService')
			->withAnyParameters()
			->will($this->returnCallback(function() use ($that) {
				return $that->authServiceHelper->createAuthService();
			}));

		return $controller;

	}

	private function _postAction(array $input)
	{
		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
		), Interspire_Json::encode($input));
		return $this->_getResource()->postAction($request)->getData(true);
	}

	private $_dummyUsers = array();

	/**
	 * @param $username
	 * @param $email
	 * @param $password
	 * @return Store_User
	 */
	public function _createDummyUser($username, $email, $password)
	{
		$testSalt = uniqid();
		$testToken = Store_User::generateApiToken();
		$user = new Store_User();
		$user->setUsername($username);
		$user->setUserEmail($email);
		$user->setSalt($testSalt);
		$user->setUserPass(Security\Password::generateHash($password, $testSalt));
		$user->setUserStatus(1);
		$user->setUsertoken($testToken);
		$user->save();

		$_dummyUsers[] = $user;

		return $user;
	}

	public function setUp()
	{
		$preserveList = array(
			'StoreHash',
			'Feature_OAuthLogin',
		);
		foreach ($preserveList as $preserveKey) {
			$this->originalConfigs[$preserveKey] = Store_Config::get($preserveKey);
		}
		Store_Config::override('StoreHash', 'xxx');
		\Store::getStoreDb()->StartTransaction();
	}

	public function tearDown()
	{
		\Store::getStoreDb()->RollbackTransaction();
		// reload config after all the munging
		foreach ($this->originalConfigs as $key => $value) {
			Store_Config::override($key, $value);
		}
		// reset the data source
		$this->authServiceHelper->resetDataStore();

		/** @var Store_User $user */
		foreach ($this->_dummyUsers as $user) {
			$user->delete();
		}
	}

	/**
	 * Test login with username
	 */
	public function testLoginWithUsername()
	{
		$oauthLogin = Store_Feature::isEnabled('OAuthLogin');
		Store_Feature::override('OAuthLogin', false);

		$testUsername = 'test_'.uniqid();
		$testEmail = 'test+'.uniqid().'@bigcommerce.com';
		$testPassword = 'password1';

		$user = $this->_createDummyUser($testUsername, $testEmail, $testPassword);
		Store_Config::override('Feature_MobileEvents', false);
		$this->assertEquals(false, (bool)Store_Config::get('Feature_MobileEvents'));
		$res = $this->_postAction(array(
			'username' => $testUsername,
			'password' => $testPassword,
		));

		$this->assertEquals(3, count($res));
		$this->assertEquals($user->getUsertoken(), $res[0]);
		$this->assertEquals($user->getUsername(), $res[2]);
		$this->assertEquals(true, (bool)Store_Config::get('Feature_MobileEvents'));

		Store_Feature::override('OAuthLogin', $oauthLogin);
	}

	/**
	 * Test login with email and OAuth disabled
	 */
	public function testLoginWithEmail()
	{
		$oauthLogin = Store_Feature::isEnabled('OAuthLogin');
		Store_Feature::override('OAuthLogin', false);

		$testUsername = 'test_'.uniqid();
		$testEmail = 'test+'.uniqid().'@bigcommerce.com';
		$testPassword = 'password1';

		$user = $this->_createDummyUser($testUsername, $testEmail, $testPassword);

		$res = $this->_postAction(array(
			'username' => $testEmail,
			'password' => $testPassword,
		));

		$this->assertEquals(3, count($res));
		$this->assertEquals($user->getUsertoken(), $res[0]);
		$this->assertEquals($user->getUsername(), $res[2]);

		Store_Feature::override('OAuthLogin', $oauthLogin);
	}

	public function testLoginWithOAuthUserValid()
	{

		Store_Config::override('Feature_OAuthLogin', true);
		Store_Config::override('OAuth_ClientId', uniqid());
		Store_Config::override('OAuth_ClientSecret', uniqid());

		$testEmail = 'test+'.uniqid().'@bigcommerce.com';
		$testUser = array(
			'id' => 1,
			'username' => $testEmail,
			'email' => $testEmail,
			'password' => 'password',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][1] = $testUser;
		$this->_createDummyUser($testUser['username'], $testUser['email'], $testUser['password']);

		$res = $this->_postAction(array(
			'username' => $testEmail,
			'password' => 'password',
		));

        $apiUsername = preg_replace('/[^\w]/', '_', 'mobile_'.$testEmail);
		$user = Store_User::find("`username` = '$apiUsername'")->first();
		$this->assertEquals(3, count($res));
		$this->assertEquals($user->getUsertoken(), $res[0]);
		$this->assertEquals($user->getUsername(), $res[2]);

	}

	public function testLoginWithOAuthUserInvalidEmail()
	{

		Store_Config::override('Feature_OAuthLogin', true);

		$testEmail = 'test+'.uniqid().'@bigcommerce.com';
		$testUser = array(
			'id' => 1,
			'username' => $testEmail,
			'email' => $testEmail,
			'password' => 'password',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][1] = $testUser;
		$this->_createDummyUser($testUser['username'], $testUser['email'], $testUser['password']);

		$this->setExpectedException('Store_Api_Exception_Authentication_InvalidCredentials');

		$this->_postAction(array(
			'username' => 'foo',
			'password' => 'password',
		));

	}

	public function testLoginWithOAuthUserInvalidPassword()
	{

		Store_Config::override('Feature_OAuthLogin', true);

		$testEmail = 'test+'.uniqid().'@bigcommerce.com';
		$testUser = array(
			'id' => 1,
			'username' => $testEmail,
			'email' => $testEmail,
			'password' => 'password',
			'scopes_list' => 'test1 test2',
		);
		$this->authServiceHelper->dataStore['users'][1] = $testUser;
		$this->_createDummyUser($testUser['username'], $testUser['email'], $testUser['password']);

		$this->setExpectedException('Store_Api_Exception_Authentication_InvalidCredentials');

		$this->_postAction(array(
			'username' => $testEmail,
			'password' => 'wrong',
		));

	}

}
