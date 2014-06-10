<?php

class Unit_Lib_Store_Api_InputValidator_CustomerPassword extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_CustomerPassword('field', array());
		$password = 'foo';
		$validator->validate($password);
		$this->assertEquals('foo', $password);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$empty = null;
		$validator->validate($empty);
	}
}
