<?php

class Unit_Lib_Store_Api_InputValidator_String extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$config = array(
			'length' => 6,
		);
		$validator = new Store_Api_InputValidator_String('field', $config);

		$value = 'foobar';
		$validator->validate($value);
		$this->assertEquals('foobar', $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar!';
		$validator->validate($string);
	}
}
