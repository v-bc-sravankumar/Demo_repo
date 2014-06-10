<?php

class Unit_Lib_Store_Api_InputValidator_Int extends Interspire_IntegrationTest
{
	public function testSmoke()
	{
		$validator = new Store_Api_InputValidator_Int('field', array());
		$value = 5;
		$validator->validate($value);
		$this->assertEquals(5, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$validator = new Store_Api_InputValidator_Int('field', array());
		$value = 'foo';
		$validator->validate($value);
	}

	public function testNegatives()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_Int('field', $config);
		$value = -1;
		$validator->validate($value);
		$this->assertEquals(-1, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$validator = new Store_Api_InputValidator_Int('field', array());
		$value = -1;
		$validator->validate($value);
	}

	public function testMin()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_Int('field', $config);
		$value = -2147483647;
		$validator->validate($value);
		$this->assertEquals(-2147483647, $value);

		$config = array(
			'min' => 10,
		);
		$validator = new Store_Api_InputValidator_Int('field', $config);
		$value = 10;
		$validator->validate($value);
		$this->assertEquals(10, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 9;
		$validator->validate($value);
	}

	public function testMax()
	{
		$validator = new Store_Api_InputValidator_Int('field', array());
		$value = 2147483647;
		$validator->validate($value);
		$this->assertEquals(2147483647, $value);

		$config = array(
			'max' => 10,
		);
		$validator = new Store_Api_InputValidator_Int('field', $config);
		$value = 10;
		$validator->validate($value);
		$this->assertEquals(10, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 11;
		$validator->validate($value);
	}
}
