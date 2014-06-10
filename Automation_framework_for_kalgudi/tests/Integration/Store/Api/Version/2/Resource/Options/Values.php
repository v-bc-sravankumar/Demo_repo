<?php

class Unit_Lib_Store_Api_Version_2_Resource_Options_Values extends Interspire_IntegrationTest
{
	private function _getResource ()
	{
		return new Store_Api_Version_2_Resource_Options_Values();
	}

	/**
	* @param mixed $json
	* @param mixed $method
	* @return Interspire_Request
	*/
	private function _getRequest($json = null, $method = 'post')
	{
		$server = array('CONTENT_TYPE' => 'application/json', 'REQUEST_METHOD' => $method);
		$request = new Interspire_Request(array(), array(), array(), $server, $json);
		return $request;
	}

	private function _createImportableImage ($filename = 'TextureImage.jpg')
	{
		// put the image we want to test with into product_images/import
		$source = dirname(__FILE__) . '/' . $filename;
		$import = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/import');
		if (!file_exists($import)) {
			$this->assertTrue(isc_mkdir($import), 'isc_mkdir failed');
		}

		$extension = Interspire_File::getExtensionFromFile($source, true);
		// ISC-5059 tempnam does not like asset paths, replace with uniqid
		$import .= ('/' . uniqid('api') .  $extension);
		$this->assertTrue(copy($source, $import), 'copy failed');

		return $import;
	}

	private $_dummyValues = array();

	private $_dummyOptions = array();

	private function _createDummyValue ($attribute = null, $data = array())
	{
		if ($attribute === null) {
			$attribute = $this->_createDummyOption();
		}

		$json = array_merge(array(
			'label' => 'VALUE_' . mt_rand(1, PHP_INT_MAX),
			'value' => 'CONTENT_' . mt_rand(1, PHP_INT_MAX),
		), $data);

		$json = json_encode($json);
		$request = $this->_getRequest($json);
		$request->setUserParam('options', $attribute->getId());
		$result = $this->_getResource()->postAction($request)->getData(true);

		$this->_dummyValues[] = $result['id'];
		return $result;
	}

	private function _createDummyOption($attributeType = null)
	{
		if ($attributeType === null) {
			$attributeType = new Store_Attribute_Type_Configurable_PickList_Set();
		}
		$attribute = new Store_Attribute();
		$attribute
			->setName('OPTION_' . mt_rand(1, PHP_INT_MAX))
			->setDisplayName('DISPLAY_' . mt_rand(1, PHP_INT_MAX))
			->setType($attributeType);

		$attribute->save();

		$this->_dummyOptions[] = $attribute->getId();
		return $attribute;
	}

	private function _deleteValue ($id)
	{
		$value = new Store_Attribute_Value();
		$value->load($id);
		$this->assertTrue($value->delete(), "option value delete failed");
	}

	private function _deleteOption ($id)
	{
		$option = new Store_Attribute();
		$option->load($id);
		$this->assertTrue($option->delete(), "option delete failed");
	}

	public function tearDown ()
	{
		foreach ($this->_dummyValues as $id) {
			$this->_deleteValue($id);
		}

		foreach ($this->_dummyOptions as $id) {
			$this->_deleteOption($id);
		}
	}

	public function testGetList()
	{
		$option = $this->_createDummyOption();

		$values = array();

		$value = $this->_createDummyValue($option);
		$values[$value['id']] = false;

		$value = $this->_createDummyValue($option);
		$values[$value['id']] = false;

		$request = new Interspire_Request();
		$request->setUserParam('options', $option->getId());
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertFalse(empty($list));

		if (count($list) == Store_Api::MAX_ITEMS_PER_PAGE) {
			// for now if the paging limit is reached (probably by running this test repeatedly on an installed store)
			// then we can't test the rest -- skip (this shouldn't happen on bamboo!)
			$this->markTestSkipped();
			return;
		}

		foreach ($list as $item) {
			$id = (int)$item['id'];
			if (isset($values[$id])) {
				$values[$id] = true;
			}
		}

		$this->assertFalse(in_array(false, $values), "one or more dummy values were not found in the list");
	}

	public function testGetEntity()
	{
		$value = $this->_createDummyValue();
		$valueId = $value['id'];

		$request = new Interspire_Request();
		$request->setUserParam('values', $valueId);

		$values = $this->_getResource()->getAction($request)->getData();
		$this->assertInternalType('array', $values);
		$this->assertSame(1, count($values));
		$this->assertEquals($valueId, $values[0]['id']);
	}

	public function testGetForOption()
	{
		$values = array();

		$option = $this->_createDummyOption();
		$value = $this->_createDummyValue($option);
		$values[$value['id']] = false;
		$value = $this->_createDummyValue($option);
		$values[$value['id']] = false;

		$this->_createDummyValue();

		$request = new Interspire_Request();
		$request->setUserParam('options', $option->getId());
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertFalse(empty($list));
		$this->assertEquals(count($values), count($list));

		foreach ($list as $item) {
			$id = (int)$item['id'];
			if (isset($values[$id])) {
				$values[$id] = true;
			}
		}

		$this->assertFalse(in_array(false, $values), "one or more dummy values were not found in the list");
	}

	/**
	* @expectedException Store_Api_Exception_Resource_MethodNotFound
	*/
	public function testPostToEntityFails()
	{
		$request = new Interspire_Request();
		$request->setUserParam('values', 1);
		$options = $this->_getResource()->postAction($request);
	}

	/**
	* @expectedException Store_Api_Exception_Resource_MethodNotFound
	*/
	public function testPostWithoutOptionFails()
	{
		$request = new Interspire_Request();
		$options = $this->_getResource()->postAction($request);
	}

	/**
	* @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
	*/
	public function testPostMissingLabelFails()
	{
		$this->_createDummyValue(null, array('label' => ''));
	}

	/**
	* @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
	*/
	public function testPostMissingValueFails()
	{
		$this->_createDummyValue(null, array('value' => ''));
	}

	/**
	* @expectedException Store_Api_Exception_Resource_Conflict
	*/
	public function testPostToOptionThatDoesntSupportValuesFails()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_Entry_Text());
		$this->_createDummyValue($option);
	}

	public function testPostDuplicateLabelFails()
	{
		$option = $this->_createDummyOption();

		$data = array('label' => 'LABEL_' . mt_rand(0, PHP_INT_MAX));
		$this->_createDummyValue($option, $data);

		try {
			$this->_createDummyValue($option, $data);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}

	public function testPostRegularValue()
	{
		$label = 'VALUE_' . mt_rand(0, PHP_INT_MAX);
		$sortOrder = 3;
		$newValue = $this->_createDummyValue(null, array('label' => $label, 'sort_order' => $sortOrder));
		$this->assertEquals($label, $newValue['label']);
		$this->assertEquals($label, $newValue['value']);
		$this->assertEquals($sortOrder, $newValue['sort_order']);
	}

	/**
	* @expectedException Store_Api_Exception_Request_InvalidField
	*/
	public function testPostProductValueDoesntExistFails()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Product());
		$this->_createDummyValue($option, array('value' => 999));
	}

	public function testPostProductValue()
	{
		$this->markTestIncomplete();
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Product());
		$value = $this->_createDummyValue($option, array('value' => 1));
		$this->assertEquals(1, $value['value']);
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostInvalidSwatchValue()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'foo'));
	}

	public function testPostSwatchValueOneColor()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'red'));
		$this->assertEquals('#FF0000', $value['value']);
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostSwatchValueOneColorHex()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => '#gggggg'));
	}

	public function testPostSwatchOneColorInvalidHexFails()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => '#ff'));
		$this->assertEquals('#FF0000', $value['value']);
	}

	public function testPostSwatchValueTwoColor()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'red|green'));
		$this->assertEquals('#FF0000|#008000', $value['value']);
	}

	public function testPostSwatchValueThreeColor()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'red|green|blue'));
		$this->assertEquals('#FF0000|#008000|#0000FF', $value['value']);
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostSwatchValueFourColorsFails()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'red|green|blue|black'));
	}

	public function testPostSwatchValueLocalTexture()
	{
		$import = $this->_createImportableImage();
		$destruct = new Interspire_File_DestructDelete($import);

		$imageFile = basename($import);

		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => $imageFile));

		$this->assertInternalType('array', $value);
		$this->assertFalse(empty($value['value']), "texture value is empty");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/attribute_value_images/' . $value['value']);
		$this->assertFileExists($imageFile, "texture file doesn't actually exist");
		$this->assertFileNotEquals($import, $imageFile, "texture value matches import file but shouldn't as it should be resized");
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostSwatchValueInvalidLocalTextureFails()
	{
		$import = $this->_createImportableImage('TextureInvalidImage.jpg');
		$destruct = new Interspire_File_DestructDelete($import);

		$imageFile = basename($import);

		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => $imageFile));
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostSwatchValueMissingLocalTextureFails()
	{
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => 'foo.jpg'));
	}

	public function testPostSwatchValueRemoteTexture()
	{
		$remoteImage = 'http://www.google.com/images/srpr/logo4w.png';
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => $remoteImage));

		$this->assertInternalType('array', $value);
		$this->assertFalse(empty($value['value']), "texture value is empty");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/attribute_value_images/' . $value['value']);
		$this->assertFileExists($imageFile, "texture file doesn't actually exist");
	}

	/**
	* @expectedException Store_Api_Exception_Resource_SaveError
	*/
	public function testPostSwatchValueMissingRemoteTextureFails()
	{
		$remoteImage = 'http://www.google.com/images/missing_image.jpg';
		$option = $this->_createDummyOption(new Store_Attribute_Type_Configurable_PickList_Swatch());
		$value = $this->_createDummyValue($option, array('value' => $remoteImage));
	}

	/**
	* @expectedException Store_Api_Exception_Resource_MethodNotFound
	*/
	public function testPutToListFails()
	{
		$request = new Interspire_Request();
		$options = $this->_getResource()->putAction($request);
	}

	public function testPutToNonExistantEntityFails()
	{
		$request = new Interspire_Request();
		$request->setUserParam('values', 999);
		$data = $this->_getResource()->putAction($request)->getData();
		$this->assertSame(0, count($data));
	}

	public function testPutDuplicateLabelFails()
	{
		$option = $this->_createDummyOption();

		$data1 = array('label' => 'LABEL_' . mt_rand(0, PHP_INT_MAX));
		$value1 = $this->_createDummyValue($option, $data1);

		$data2 = array('label' => 'LABEL_' . mt_rand(0, PHP_INT_MAX));
		$value2 = $this->_createDummyValue($option, $data2);

		// rename option 1 to option 2
		$json = json_encode($data2);
		$request = $this->_getRequest($json, 'put');
		$request->setUserParam('values', $value1['id']);

		try {
			$this->_getResource()->putAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}
	
	public function testPutSortOrder()
	{
	    $option = $this->_createDummyOption();
	    $newValue = $this->_createDummyValue($option);
	    $this->assertEquals(0, $newValue['sort_order']);
	    
	    $sortOrder = 4;
	    $data = json_encode(array('sort_order' => $sortOrder));
	    $request = $this->_getRequest($data, 'put');
	    $request->setUserParam('values', $newValue['id']);
	    
	    $updatedValue = $this->_getResource()->putAction($request)->getData(true);
	    $this->assertEquals($sortOrder, $updatedValue['sort_order']);
	}

	public function testDeleteList()
	{
		$this->markTestIncomplete();
	}

	public function testDeleteListForOption()
	{
		$option = $this->_createDummyOption();
		$this->_createDummyValue($option);
		$this->_createDummyValue($option);

		$this->assertFalse($option->getValues()->isEmpty());

		$request = new Interspire_Request();
		$request->setUserParam('options', $option->getId());
		$this->assertNull($this->_getResource()->deleteAction($request));

		$this->assertTrue($option->getValues()->isEmpty());
	}

	public function testDeleteEntity()
	{
		$value = $this->_createDummyValue();
		$request = new Interspire_Request();
		$request->setUserParam('values', $value['id']);
		$this->assertNull($this->_getResource()->deleteAction($request));
	}
}
