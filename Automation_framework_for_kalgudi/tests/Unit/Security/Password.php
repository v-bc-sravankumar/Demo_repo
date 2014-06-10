<?php
class Unit_Security_Password extends PHPUnit_Framework_TestCase
{
	private $salt = '940JkYVJ67CP8Aa7BaHF';

	private $testPassword = 'test password';

	private $expectedHash = '49779f4adfe4bd3df82a665e7d61a02b';

	/** @var Security\Password */
	private $password;

	public function setUp()
	{
		$this->password = new Security\Password();
	}

	public function testHashedPassword()
	{
		$hashed = $this->password->generateHash($this->testPassword, $this->salt);

		$this->assertTrue($hashed === $this->expectedHash);
	}

	public function testHashedPasswordWithDifferentSalt()
	{
		$hashed = $this->password->generateHash($this->testPassword, 'incorrect salt');

		$this->assertFalse($hashed === $this->expectedHash);
	}

	public function testHashedPasswordWithDifferentPassword()
	{
		$hashed = $this->password->generateHash('another password', $this->salt);

		$this->assertFalse($hashed === $this->expectedHash);
	}

	public function testSaltHashPassword()
	{
		$params = $this->password->generateSaltHash($this->testPassword);
		$expectedHash = $this->password->generateHash($this->testPassword, $params['salt']);

		$this->assertTrue($params['hash'] === $expectedHash);
	}

	public function testHashPasswordReturnsExpected2yHashUsingPepper()
	{
		$secrets = new \Config\Secrets();

		$hash = $this->password->hashPassword($this->testPassword);

		$this->assertTrue(password_verify($this->testPassword . $secrets->get('password.pepper'), $hash));
	}

	public function testHashPasswordReturnsExpected2aHashUsingPepper()
	{
		$secrets = new \Config\Secrets();

		$hash = $this->password->hashPassword($this->testPassword, true);

		$this->assertTrue(password_verify($this->testPassword . $secrets->get('password.pepper'), $hash));
	}

	public function testHashPasswordReturns2yAlgorithmVersionByDefault()
	{
		$hash = $this->password->hashPassword($this->testPassword);

		$this->assertEquals('y', $hash[2]);
	}

	public function testHashPasswordReturns2aAlgorithmVersion()
	{
		$hash = $this->password->hashPassword($this->testPassword, true);

		$this->assertEquals('a', $hash[2]);
	}

	public function testGeneratePassword()
	{
		$numChars = 16;
		$generated = $this->password->generatePassword($numChars);

		$this->assertTrue(preg_match('/^[a-z0-9!@#$%&?]{'.$numChars.'}$/i', $generated) === 1);
	}
}
