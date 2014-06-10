<?php

class Unit_Lib_Store_Api_InputValidator_TinyInt extends Interspire_IntegrationTest
{
	public function testMin()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_Tinyint('field', $config);
		$value = -127;
		$validator->validate($value);
		$this->assertEquals(-127, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = -128;
		$validator->validate($value);
	}

	public function testMax()
	{
		$validator = new Store_Api_InputValidator_Tinyint('field', array());
		$value = 127;
		$validator->validate($value);
		$this->assertEquals(127, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 128;
		$validator->validate($value);
	}
}
