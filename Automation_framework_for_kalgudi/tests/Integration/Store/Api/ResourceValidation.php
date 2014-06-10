<?php

require_once dirname(__FILE__).'/Base.php';
require_once dirname(__FILE__).'/ValidatingResource.php';

class Unit_Lib_Store_Api_ResourceValidation extends Unit_Lib_Store_Api_Base
{

	public function testValidateJsonObjectArrayValid()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Json(json_decode('
		{
			"objects": [
				{"id": 1},
				{"id": 2}
			]
		}', true));
		$res = $resource->validateInput($input, 'POST');
		$this->assertTrue($res instanceof Store_Api_Input_Json);

	}

	public function testValidateJsonObjectArrayValidObjectAsCollection()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Json(json_decode('
		{
			"objects": {
				"one" : {"id": 1},
				"two" : {"id": 2}
			}
		}', true));
		$res = $resource->validateInput($input, 'POST');
		$this->assertTrue($res instanceof Store_Api_Input_Json);

	}

	public function testValidateJsonObjectArrayInvalidObjectArray()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Json(json_decode('
		{
			"objects": {
				"id": 1
			}
		}', true));

		try {
			$resource->validateInput($input, 'POST');
			$this->assertTrue(false, 'Expected Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('objects', $e->getField());
		}

	}

	public function testValidateXmlObjectArrayValid()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Xml(new SimpleXMLIterator('
		<input>
			<objects>
				<object>
					<id>1</id>
				</object>
				<object>
					<id>1</id>
				</object>
			</objects>
		</input>', true));
		$res = $resource->validateInput($input, 'POST');
		$this->assertTrue($res instanceof Store_Api_Input_Xml);

	}

	public function testValidateXmlObjectArrayValidSingleItem()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Xml(new SimpleXMLIterator('
		<input>
			<objects>
				<object>
					<id>1</id>
				</object>
			</objects>
		</input>', true));
		$res = $resource->validateInput($input, 'POST');
		$this->assertTrue($res instanceof Store_Api_Input_Xml);

	}

	public function testValidateXmlObjectArrayInvalidItems()
	{
		$resource = new Unit_Lib_Store_Api_ValidatingResource();
		$input = new Store_Api_Input_Xml(new SimpleXMLIterator('
		<input>
			<objects>
				<id>1</id>
			</objects>
		</input>', true));

		try {
			$resource->validateInput($input, 'POST');
			$this->assertTrue(false, 'Expected Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('objects', $e->getField());
		}

	}

}