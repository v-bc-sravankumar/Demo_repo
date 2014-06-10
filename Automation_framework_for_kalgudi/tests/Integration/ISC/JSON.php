<?php

class ClassFor_testJSONEncodedSerializedPhpObject
{
	private $_data = array();

	public function __construct ($value)
	{
		$this->_data['value'] = $value;
	}

	public function getValue ()
	{
		return $this->_data['value'];
	}
}

class Unit_Core_JSON extends Interspire_IntegrationTest
{
	public function testJSONEncodedSerializedPhpObject ()
	{
		$random = rand();
		$object = new ClassFor_testJSONEncodedSerializedPhpObject($random);

		$json = array('object' => serialize($object));

		$encoded = isc_json_encode($json);

		GetLib('class.json');
		$decoded = ISC_JSON::decode($encoded);
		$unserialized = unserialize($decoded->object);

		$this->assertNotEquals(false, $unserialized);
		$this->assertEquals($object->getValue(), $unserialized->getValue());
	}
}

