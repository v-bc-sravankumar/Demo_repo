<?php

class Unit_Lib_Store_Api_InputValidator_DecimalValidator extends Interspire_IntegrationTest
{
	public function testSmoke()
	{
		$validator = new Store_Api_InputValidator_DecimalValidator('field', array());
		$value = 5.678;
		$validator->validate($value);
		$this->assertEquals(5.678, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$validator = new Store_Api_InputValidator_DecimalValidator('field', array());
		$value = 'foo';
		$validator->validate($value);
	}

	public function testNegatives()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_DecimalValidator('field', $config);
		$value = -1.1314;
		$validator->validate($value);
		$this->assertEquals(-1.1314, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$validator = new Store_Api_InputValidator_DecimalValidator('field', array());
		$value = -1.12;
		$validator->validate($value);
	}

	public function testMin()
	{
		$config = array(
			'negative' => true,
		);
		$validator = new Store_Api_InputValidator_DecimalValidator('field', $config);
		$value = -1.8e308;
		$validator->validate($value);
		$this->assertEquals(-1.8e308, $value);

		$config = array(
			'min' => 10.123,
		);
		$validator = new Store_Api_InputValidator_DecimalValidator('field', $config);
		$value = 10.123;
		$validator->validate($value);
		$this->assertEquals(10.123, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 9.123;
		$validator->validate($value);
	}

	public function testMax()
	{
		$validator = new Store_Api_InputValidator_DecimalValidator('field', array());
		$value = 1.8e308;
		$validator->validate($value);
		$this->assertEquals(1.8e308, $value);

		$config = array(
			'max' => 10.123,
		);
		$validator = new Store_Api_InputValidator_DecimalValidator('field', $config);
		$value = 10.123;
		$validator->validate($value);
		$this->assertEquals(10.123, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$value = 11.123;
		$validator->validate($value);
	}
}
