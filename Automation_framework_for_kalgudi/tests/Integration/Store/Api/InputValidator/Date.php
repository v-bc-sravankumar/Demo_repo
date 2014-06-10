<?php

class Unit_Lib_Store_Api_InputValidator_Date extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_Date('field', array());
		$date = date('r');
		$expected = $date;
		$validator->validate($date);
		$this->assertEquals($expected, $date);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$timestamp = time();
		$validator->validate($timestamp);
	}
}
