<?php

class Unit_Lib_Store_Api_InputValidator_Enum extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$config = array(
			'values' => array(1,2,3),
		);
		$validator = new Store_Api_InputValidator_Enum('field', $config);

		$value = 1;
		$validator->validate($value);
		$this->assertEquals(1, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
