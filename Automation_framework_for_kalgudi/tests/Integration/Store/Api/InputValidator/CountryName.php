<?php

class Unit_Lib_Store_Api_InputValidator_CountryName extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_CountryName('field', array());
		$country = 'Australia';
		$validator->validate($country);
		$this->assertEquals('Australia', $country);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
