<?php

class Unit_Lib_Store_Api_InputValidator_Email extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_Email('field', array());
		$email = 'bob@example.com';
		$validator->validate($email);
		$this->assertEquals('bob@example.com', $email);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
