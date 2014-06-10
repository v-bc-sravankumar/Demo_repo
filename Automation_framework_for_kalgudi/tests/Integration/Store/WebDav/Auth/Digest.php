<?php
class Unit_Lib_Store_WebDav_Auth_Digest extends Interspire_UnitTest
{
	private $authDigest;
	private $oauthFlagValue;

	public function setUp()
	{
		$this->authDigest = new Store_WebDav_Auth_Backend_Digest();
		$this->oauthFlagValue = Store_Feature::isEnabled('OAuthLogin');
	}

	public function tearDown()
	{
		Store_Feature::override('OAuthLogin', $this->oauthFlagValue);
	}

	private function _getMockUser($webdavEnabled, $username, $password = 'test', $realm = 'realm')
	{
		// used for non-oauth logins
		$blowfishKey = md5($password);
		$crypter = new Interspire_Mcrypt($blowfishKey);
		$this->authDigest->setCrypter($crypter);

		$userData = array(
			"pk_userid"      	=> "4",
			"username"       	=> "testuser",
			"useremail"      	=> "test@bigcommerce.com",
			"userpass"       	=> "3d68a5fa0fbcc00874740d0f1c27337f",
			"userfirstname"  	=> "test",
			"userlastname"   	=> "user",
			"userstatus"     	=> "1",
			"webdav_digest"  	=> base64_encode($crypter->encrypt(md5($username . ':realm:' . $password))),
			"webdav_password"	=> $password,
			"webdav_enabled" 	=> $webdavEnabled,
		);

		if (Store_Feature::isEnabled('OAuthLogin')) {
			$map = array(
				array('username', $username, '*', false),
				array('useremail', $username, '*', $userData),
			);
		}
		else {
			$map = array(
				array('username', $username, '*', $userData),
			);
		}

		$user = $this->getMock('ISC_ADMIN_USER', array('getUserByField'));
		$user->expects($this->any())
				->method('getUserByField')
				->will($this->returnValueMap($map));

		return $user;
	}

	private function _getMockUserFail($username)
	{
		$user = $this->getMock('ISC_ADMIN_USER', array('getUserByField'));

		if (Store_Feature::isEnabled('OAuthLogin')) {
			$map = array(
				array('username', $username, '*', false),
				array('useremail', $username, '*', false),
			);
		}
		else {
			$map = array(
				array('username', $username, '*', false),
			);
		}

		$user = $this->getMock('ISC_ADMIN_USER', array('getUserByField'));
		$user->expects($this->any())
				->method('getUserByField')
				->will($this->returnValueMap($map));

		return $user;
	}

	private function _getMockCache($key, $value)
	{
		$cache = $this->getMock('Interspire_Cache_Memory');

		$cache->expects($this->once())
			->method('get')
			->with($this->equalTo($key))
			->will($this->returnValue($value));

		return $cache;
	}

	public function oauthDataProvider()
	{
		return array(
			array('oauth' => false),
			array('oauth' => true),
		);
	}

	/**
	 * @dataProvider oauthDataProvider
	 */
	public function testGetDigestAuthenticatedStaffUser($oauthEnabled)
	{
		Store_Feature::override('OAuthLogin', $oauthEnabled);

		$username = 'interspire:staff.user';

		// blowfish key = md5('test')
		$blowfishKey = '098f6bcd4621d373cade4e832627b4f6';
		// expected hash = md5('interspire:staff.user:BigCommerce:password');
		$expectedHash = '63bd60de026aaf56886559a83b9efd7d';
		// encrypted hash base64 encoded here so it's readable
		$encryptedHash = base64_decode("IMp5M98VSW1JSYxbOtNqpLECTXvR9WOPFFjbnSeiY2w=");

		$cacheKey = BC_WEBDAV_DIGEST_CACHE_PREFIX . $username;
		$this->authDigest->setCache($this->_getMockCache($cacheKey, $encryptedHash));
		$this->authDigest->setCrypter(new Interspire_Mcrypt($blowfishKey));

		$result = $this->authDigest->getDigestHash('BigCommerce', $username);

		$this->assertEquals($expectedHash, $result);
	}

	public function testGetDigestNonAuthenticatedStaffUser()
	{
		$result = $this->authDigest->getDigestHash('BigCommerce', 'interspire:invalid.user');

		$this->assertNull($result);
	}

	public function digestDataProvider()
	{
		return array(
			array('oauth' => false, 'username' => 'testuser'),
			array('oauth' => true, 'username' => 'test@bigcommerce.com'),
		);
	}

	/**
	 * @dataProvider digestDataProvider
	 */
	public function testGetDigestForEnabledUser($oauthEnabled, $username)
	{
		Store_Feature::override('OAuthLogin', $oauthEnabled);

		$this->authDigest->setUser($this->_getMockUser(1, $username));
		$result = $this->authDigest->getDigestHash('realm', $username);

		// md5('testuser:realm:test')
		$expectedHash = md5($username . ':realm:test');

		$this->assertEquals($expectedHash, $result);
	}

	/**
	 * @dataProvider digestDataProvider
	 */
	public function testGetDigestForWebdavTokenUser($oauthEnabled, $username)
	{
		// TODO: BIG-6068 update tests.
		Store_Feature::override('OAuthLogin', $oauthEnabled);

		$webdavPassword = 'abc123xyz';
		// md5('test@bigcommerce.com:realm:abc123xyz')
		$expectedHash = md5($username . ':realm:' . $webdavPassword);

		$this->authDigest->setUser($this->_getMockUser(1, $username, $webdavPassword));

		$result = $this->authDigest->getDigestHash('realm', $username);

		$this->assertEquals($expectedHash, $result);
	}

	/**
	 * @dataProvider digestDataProvider
	 */
	public function testGetDigestDisabledUser($oauthEnabled, $username)
	{
		Store_Feature::override('OAuthLogin', $oauthEnabled);

		$this->authDigest->setUser($this->_getMockUser(0));

		$result = $this->authDigest->getDigestHash('realm', $username);

		$this->assertNull($result);
	}

	/**
	 * @dataProvider digestDataProvider
	 */
	public function testGetDigestInvalidUser($oauthEnabled, $username)
	{
		Store_Feature::override('OAuthLogin', $oauthEnabled);

		$this->authDigest->setUser($this->_getMockUserFail($username));

		$result = $this->authDigest->getDigestHash('realm', $username);

		$this->assertNull($result);
	}
}
