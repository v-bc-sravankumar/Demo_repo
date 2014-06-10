<?php

class Unit_Security_CsrfTokenTest extends PHPUnit_Framework_TestCase
{

	public function testGenerateToken()
	{
		$token = Security\Csrf::generateToken();
		$this->assertTrue(!empty($token) && strlen($token) > 64);

		$new = Security\Csrf::generateToken();
		$this->assertFalse($token == $new);
	}

	public function testValidateCorrectToken()
	{
		$token1 = Security\Csrf::generateToken();
		$this->assertTrue(Security\Csrf::isValidToken($token1));

		$token2 = Security\Csrf::generateToken();
		$this->assertTrue(Security\Csrf::isValidToken($token2));
	}

	public function testValidateWrongToken()
	{
		$token = Security\Csrf::generateToken();
		session_id('_');
		$this->assertFalse(Security\Csrf::isValidToken($token));
	}

}
