<?php

class Unit_Users_Password extends Interspire_IntegrationTest
{
	/** @var ISC_ADMIN_USER */
	private $userManager;

	private $originalPCISettings;

	public function setUp()
	{
		parent::setUp();

		require_once BUILD_ROOT.'/admin/init.php';
		$this->userManager = getClass('ISC_ADMIN_USER');

		$this->originalPCISettings = array(
			'PCIPasswordMinLen' => Store_Config::get('PCIPasswordMinLen'),
			'PCIPasswordHistoryCount' => Store_Config::get('PCIPasswordHistoryCount'),
			'PCIPasswordExpiryTimeDay' => Store_Config::get('PCIPasswordExpiryTimeDay'),
			'PCILoginAttemptCount' => Store_Config::get('PCILoginAttemptCount'),
			'PCILoginLockoutTimeMin' => Store_Config::get('PCILoginLockoutTimeMin'),
			'PCILoginIdleTimeMin' => Store_Config::get('PCILoginIdleTimeMin'),
			'PCILoginInactiveTimeDay' => Store_Config::get('PCILoginInactiveTimeDay'),
		);

		// set some default pci settings
		Store_Config::override('PCIPasswordMinLen', 8);
		Store_Config::override('PCIPasswordHistoryCount', 4);
		Store_Config::override('PCIPasswordExpiryTimeDay', 90);
		Store_Config::override('PCILoginAttemptCount', 6);
		Store_Config::override('PCILoginLockoutTimeMin', 30);
		Store_Config::override('PCILoginIdleTimeMin', 15);
		Store_Config::override('PCILoginInactiveTimeDay', 90);
	}

	public function tearDown ()
	{
		parent::tearDown();

		/*
		 * Clear any created users.
		 */
		$query = '
			DELETE FROM
				[|PREFIX|]users
			WHERE
				pk_userid > 1';
		$GLOBALS['ISC_CLASS_DB']->Query($query);

		/*
		 * Clear the password histories
		 */
		$query = '
			DELETE FROM
				[|PREFIX|]user_password_histories
			WHERE
				user_id > 1';
		$GLOBALS['ISC_CLASS_DB']->Query($query);

		foreach ($this->originalPCISettings as $setting => $value) {
			Store_Config::override($setting, $value);
		}
	}

	private function createUser($name, $password)
	{
		$user = array(
			'username' => $name,
			'userpass' => $password,
		);

		$userid = $GLOBALS['ISC_CLASS_DB']->insertQuery('users', $user);
		return $userid;
	}


	private function updateUser($userid, $values)
	{
		$where = "pk_userid='".$GLOBALS['ISC_CLASS_DB']->Quote($userid)."'";
		$GLOBALS['ISC_CLASS_DB']->updateQuery('users', $values, $where);
	}

	/**
	 * Return the raw content of a user db row.
	 * @param string $userName
	 */
	private function getRawUserDataByUsername($userName)
	{
		$query = 'SELECT * FROM `[|PREFIX|]users` WHERE `username` = \''.$GLOBALS['ISC_CLASS_DB']->Quote($userName).'\'';
		$result = $GLOBALS['ISC_CLASS_DB']->Query($query);
		if(!$result) {
			return false;
		}
		return $GLOBALS['ISC_CLASS_DB']->Fetch($result);
	}

	public function testCryptographicStorageOfPassword()
	{

		$userHelper = new ISC_ADMIN_USER;

		$error = "";
		$permissions = array(101, 103);
		$suppliedData = array(
			"username" => substr(__METHOD__, 0, 40), // Usernames can be only 50 chars
			"userpass" => 'password1',
			"userfirstname" => 'First',
			"userlastname" => 'Last',
			"userstatus" => 1,
			"useremail" => __METHOD__.'@example.com',
			"token" => $userHelper->_GenerateUserToken(),
			"usertoken" => '',
			"userapi" => '',
			'userrole' => 'admin',
			"webdav_enabled" => 0,
		);

		/*
		 * Save the user into the database.
		 * Note: _CommitUser() called for both create and update
		 */
		if (!$userHelper->_CommitUser(0, $suppliedData, $permissions, $error)) {
			$this->fail('Could not create test user: '.$error);
		}

		/*
		 * Read the data straight from the db.
		 */
		$savedData = $this->getRawUserDataByUsername($suppliedData['username']);

		/*
		 * Check that the supplied password does not match the raw db data.
		 */
		$this->assertNotEquals($suppliedData['userpass'], $savedData['userpass'], 'Password not altered/encrypted on save');

		/*
		 * Now check that the encrypted value is set properly
		 */
		$expectedHash = md5($savedData['salt'] . sha1($savedData['salt'] . $suppliedData['userpass']));
		$this->assertEquals($savedData['userpass'], $expectedHash, "Database value '".$savedData['userpass']."' doesnt match what we expect '".$expectedHash."'");


	}

	public function testKnownEncryptionResult()
	{
		$expected = 'c1b9495299a3ed3ad5964682c7d369c8';
		$actual = \Security\Password::generateHash('password1', 'Mmm-salty');
		$this->assertEquals($expected, $actual, 'Salting and encrypting didnt result in what we expected');
	}

	public function testUpdatePasswordCalledDuringCommit()
	{

		$password = 'password'.time();

		$userHelperMock = $this->getMock('ISC_ADMIN_USER', array('updatePassword'));
		$userHelperMock
			->expects($this->once())
			->method('updatePassword')
			->with($this->anything(), $this->equalTo($password));

		$error = "";
		$permissions = array(101, 103);
		$suppliedData = array(
			"username" => __METHOD__,
			"userpass" => $password,
			"userfirstname" => 'First',
			"userlastname" => 'Last',
			"userstatus" => 1,
			"useremail" => __METHOD__.'@example.com',
			"token" => $userHelperMock->_GenerateUserToken(),
			"usertoken" => '',
			"userapi" => '',
			'userrole' => 'admin',
			"webdav_enabled" => 0,
		);

		$userHelperMock->_CommitUser(0, $suppliedData, $permissions, $error);

	}

	public function testValidatePassword()
	{
		// empty password
		$msg = '';
		$minLen = getConfig('PCIPasswordMinLen');
		$res = $this->userManager->validatePassword('', $msg);
		$expected = getLang('PasswordStrengthMeter_MsgTooShort', array(
			'minLen' => $minLen,
		));
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		// too short, 7 char
		$msg = '';
		$res = $this->userManager->validatePassword('aabbccd', $msg);
		$expected = getLang('PasswordStrengthMeter_MsgTooShort', array(
			'minLen' => $minLen,
		));
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		// no alpha
		$msg = '';
		$res = $this->userManager->validatePassword('12345678', $msg);
		$expected = getLang('PasswordStrengthMeter_MsgNoAlphaNum');
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		// no number
		$msg = '';
		$res = $this->userManager->validatePassword('abcdefghijk', $msg);
		$expected = getLang('PasswordStrengthMeter_MsgNoAlphaNum');
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		// weak, but ok
		$msg = '';
		$res = $this->userManager->validatePassword('12345678a', $msg);
		$this->assertTrue($res);
	}


	public function testDisablePasswordMinLen()
	{
		Store_Config::override('PCIPasswordMinLen', 0);
		$res = $this->userManager->validatePassword('nonum');
		$this->assertTrue($res);
	}


	public function testPasswordHistory()
	{
		$userid = $this->createUser('rong', md5('password'));

		$res = $this->userManager->updatePassword($userid, 'password1');
		$this->assertTrue($res);

		// password1 to password1, previously used
		$msg = '';
		$res = $this->userManager->updatePassword($userid, 'password1', $msg);
		$expected = getLang('PasswordPreviouslyUsed', array(
			'historyCount' => getConfig('PCIPasswordHistoryCount')
		));
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		// password1 to password2, ok
		$res = $this->userManager->updatePassword($userid, 'password2');
		$this->assertTrue($res);

		// password2 -> password3 -> password4 -> password1 -> error (1 is still in history)
		$res1 = $this->userManager->updatePassword($userid, 'password3');
		$res2 = $this->userManager->updatePassword($userid, 'password4');
		$res3 = $this->userManager->updatePassword($userid, 'password1');
		$this->assertTrue($res1);
		$this->assertTrue($res2);
		$this->assertFalse($res3);

		// password4 -> password5 -> password1 -> ok (1 is no longer in history)
		$res1 = $this->userManager->updatePassword($userid, 'password5');
		$res2 = $this->userManager->updatePassword($userid, 'password1');
		$this->assertTrue($res1);
		$this->assertTrue($res2);
	}


	public function testDisablePasswordHistory()
	{
		$userid = $this->createUser('rong', md5('password'));

		Store_Config::override('PCIPasswordHistoryCount', 0);
		$res1 = $this->userManager->updatePassword($userid, 'password1');
		$res2 = $this->userManager->updatePassword($userid, 'password1');
		$this->assertTrue($res1);
		$this->assertTrue($res2);
	}


	public function testVerifyPassword()
	{
		$password = md5('password');
		$userid = $this->createUser('rong', $password);
		$autoSalt = substr(md5(uniqid()), 0, 15);
		$hash = Security\Password::generateHash($password, $autoSalt);

		$user = array(
			'salt'      => $autoSalt,
			'userpass'  => $hash,
		);
		$this->updateUser($userid, $user);

		// test pre 6.0 verification (with auto salt)
		$res = $this->userManager->verifyPassword($userid, 'password');
		$this->assertTrue($res);
		$res = $this->userManager->verifyPassword($userid, 'wrong');
		$this->assertFalse($res);

		// test post 6.0 with salt
		$msg = '';
		$this->userManager->updatePassword($userid, 'password1');
		$res = $this->userManager->verifyPassword($userid, 'wrong', $msg);
		$expected = getLang('BadLogin');
		$this->assertFalse($res);
		$this->assertEquals($expected, $msg);

		$res = $this->userManager->verifyPassword($userid, 'password1', $msg);
		$this->assertTrue($res);
	}


	public function testLockout()
	{
		$sendMail = false;

		$userid = $this->createUser('rong', md5('password'));
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		// 5 failed login attempts, still good
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertEquals(0, $user['attempt_lockout']);

		// 6 failed login attempts, locked out
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertNotEquals(0, $user['attempt_lockout']);

		// reset by userid, not locked out anymore
		$res = $this->userManager->resetFailedLoginAttempt($userid);
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertTrue($res);
		$this->assertEquals(0, $user['attempt_lockout']);

		// lock again
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertNotEquals(0, $user['attempt_lockout']);

		// make sure getUserByField is working ok, in none strict mode
		$testUser = $this->userManager->getUserByField('pk_userid', '1abc', '*');
		$this->assertNull($testUser);

		// reset by wrong token
		$token = md5($user['attempt_lockout'].$userid);
		$res = $this->userManager->resetFailedLoginAttempt($token.'wrongtokensuffix');
		$this->assertFalse($res);

		// reset by correct token
		$res = $this->userManager->resetFailedLoginAttempt($token);
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertTrue($res);
		$this->assertEquals(0, $user['attempt_lockout']);
	}


	public function testDisableLockout()
	{
		Store_Config::override('PCILoginAttemptCount', 0);
		$userid = $this->createUser('rong', md5('password'));
		$sendMail = false;
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);
		$this->userManager->addFailedLoginAttempt($userid, $sendMail);

		// 6 failed login attempts, no lock out
		$user = $this->userManager->getUserByField('pk_userid', $userid, '*');
		$this->assertEquals(0, $user['attempt_lockout']);
		$this->assertEquals(0, $user['attempt_counter']);
	}


	public function testPasswordExpiry()
	{
		$userid = $this->createUser('rong', md5('password'));
		$this->userManager->updatePassword($userid, 'password1');
		$this->updateUser($userid, array('updated' => strtotime('100 days ago')));

		// already expired for 10 days
		$expiry = $this->userManager->getPasswordExpiry($userid);
		$this->assertTrue(time() > $expiry);

		// not expired, just updated
		$this->userManager->updatePassword($userid, 'password2');
		$expiry = $this->userManager->getPasswordExpiry($userid);
		$this->assertTrue(time() < $expiry);
	}


	public function testDisablePasswordExpiry()
	{
		$userid = $this->createUser('rong', md5('password'));
		$this->userManager->updatePassword($userid, 'password1');

		// 0 is pre 6.0, 1 is not expired
		Store_Config::override('PCIPasswordExpiryTimeDay', 0);
		$expiry = $this->userManager->getPasswordExpiry($userid);
		$this->assertEquals(1, $expiry);
	}


	public function testInactiveUsers()
	{
		$userid = $this->createUser('inactive', md5('password'));
		$inactiveUsers = $this->userManager->getInactiveUsers();
		$this->assertTrue(count($inactiveUsers) == 0);

		// not logged in for 100 days -> inactive
		$this->updateUser($userid, array('last_login' => strtotime('100 days ago')));
		$inactiveUsers = $this->userManager->getInactiveUsers();
		$this->assertTrue(count($inactiveUsers) == 1);
	}


	public function testDisableInactiveUsers()
	{
		// not logged in for 100 days -> still active
		Store_Config::override('PCILoginInactiveTimeDay', 0);
		$userid = $this->createUser('inactive', md5('password'));
		$this->updateUser($userid, array('last_login' => strtotime('100 days ago')));
		$inactiveUsers = $this->userManager->getInactiveUsers();
		$this->assertTrue(count($inactiveUsers) == 0);
	}
}
