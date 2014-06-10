<?php

class Unit_Lib_Store_Api_InputValidator_Boolean extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_Boolean('field', array());
		$true = true;
		$validator->validate($true);
		$this->assertEquals(true, $true);

		$validate = 'true';
		$validator->validate($validate);
		$this->assertEquals('true', $validate);

		$validate = 'false';
		$validator->validate($validate);
		$this->assertEquals('false', $validate);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foo';
		$validator->validate($string);
	}
}
