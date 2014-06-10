<?php

class Unit_Lib_Store_Api_InputValidator_OrderStatus extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_OrderStatus('field', array());

		$value = ORDER_STATUS_INCOMPLETE;
		$validator->validate($value);
		$this->assertEquals(ORDER_STATUS_INCOMPLETE, $value);

		$value = ORDER_STATUS_COMPLETED;
		$validator->validate($value);
		$this->assertEquals(ORDER_STATUS_COMPLETED, $value);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
