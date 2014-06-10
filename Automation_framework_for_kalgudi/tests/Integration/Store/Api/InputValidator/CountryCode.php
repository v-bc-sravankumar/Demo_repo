<?php

class Unit_Lib_Store_Api_InputValidator_CountryCode extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_CountryCode('field', array());
		$code = 'AU';
		$validator->validate($code);
		$this->assertEquals('AU', $code);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
