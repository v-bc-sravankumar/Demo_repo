<?php

class Unit_Lib_Store_Api_InputValidator_SmallInt extends Interspire_IntegrationTest
{
	public function testMin()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_Smallint('field', $config);
		$value = -32767;
		$validator->validate($value);
		$this->assertEquals(-32767, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = -32768;
		$validator->validate($value);
	}

	public function testMax()
	{
		$validator = new Store_Api_InputValidator_Smallint('field', array());
		$value = 32767;
		$validator->validate($value);
		$this->assertEquals(32767, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 32768;
		$validator->validate($value);
	}
}
