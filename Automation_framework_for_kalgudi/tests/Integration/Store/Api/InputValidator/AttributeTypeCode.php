<?php

class Unit_Lib_Store_Api_InputValidator_AttributeTypeCode extends Interspire_IntegrationTest
{
	public function testValidator()
	{
		$validator = new Store_Api_InputValidator_AttributeTypeCode('field', array());

		$prefixes = Store_Attribute_Type::getImportExportPrefixesForTypes();
		$validCode = key($prefixes);
		$expected = $validCode;

		$validator->validate($validCode);
		$this->assertEquals($expected, $validCode);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$string = 'foobar';
		$validator->validate($string);
	}
}
