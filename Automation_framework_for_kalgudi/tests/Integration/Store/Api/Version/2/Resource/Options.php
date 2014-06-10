<?php

class Unit_Lib_Store_Api_Version_2_Resource_Options extends Interspire_IntegrationTest
{
	private function _getResource ()
	{
		return new Store_Api_Version_2_Resource_Options();
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

	private $_dummyOptions = array();

	private function _createDummyOption ($data = array())
	{
		$json = array_merge(array(
			'name' => 'OPTION_' . mt_rand(1, PHP_INT_MAX),
			'display_name' => 'DISPLAY_' . mt_rand(1, PHP_INT_MAX),
			'type' => 'T',
		), $data);

		$json = json_encode($json);
		$request = $this->_getRequest($json);
		$result = $this->_getResource()->postAction($request)->getData(true);

		$this->_dummyOptions[] = $result['id'];
		return $result;
	}

	private function _deleteOption ($id)
	{
		$option = new Store_Attribute();
		$option->load($id);
		$this->assertTrue($option->delete(), "option delete failed");
	}

	public function tearDown ()
	{
		foreach ($this->_dummyOptions as $id) {
			$this->_deleteOption($id);
		}
	}

	public function testGetList()
	{
		$options = array();

		$option = $this->_createDummyOption();
		$options[$option['id']] = false;

		$option = $this->_createDummyOption();
		$options[$option['id']] = false;

		$list = $this->_getResource()->getAction(new Interspire_Request())->getData();

		$this->assertInternalType('array', $list);
		$this->assertFalse(empty($list));

		if (count($list) == Store_Api::ITEMS_PER_PAGE_DEFAULT) {
			// for now if the paging limit is reached (probably by running this test repeatedly on an installed store)
			// then we can't test the rest -- skip (this shouldn't happen on bamboo!)
			$this->markTestSkipped();
			return;
		}

		foreach ($list as $item) {
			$id = (int)$item['id'];
			if (isset($options[$id])) {
				$options[$id] = true;
			}
		}

		$this->assertFalse(in_array(false, $options), "one or more dummy options were not found in the list");
	}

	public function testGetEntity()
	{
		$option = $this->_createDummyOption();
		$optionId = $option['id'];

		$request = new Interspire_Request();
		$request->setUserParam('options', $optionId);

		$options = $this->_getResource()->getAction($request)->getData();
		$this->assertInternalType('array', $options);
		$this->assertSame(1, count($options));
		$this->assertEquals($optionId, $options[0]['id']);
	}

	public function testGetEntityDoesntExist()
	{
		$request = new Interspire_Request();
		$request->setUserParam('options', 999);

		$options = $this->_getResource()->getAction($request)->getData();
		$this->assertInternalType('array', $options);
		$this->assertSame(0, count($options));
	}

	public function testGetWithNameCondition()
	{
		// create an option
		$this->_createDummyOption();

		// now another with specific name
		$optionName = 'OPTION_' . mt_rand(0, PHP_INT_MAX);
		$option = $this->_createDummyOption(array('name' => $optionName));
		$optionId = $option['id'];

		$request = new Interspire_Request(array('name' => $optionName));
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$option = array_pop($list);
		$this->assertEquals($optionId, $option['id']);
		$this->assertEquals($optionName, $option['name']);
	}

	public function testGetWithDisplayNameCondition()
	{
		// create an option
		$this->_createDummyOption();

		// now another with specific name
		$displayName = 'DISPLAY_' . mt_rand(0, PHP_INT_MAX);
		$option = $this->_createDummyOption(array('display_name' => $displayName));
		$optionId = $option['id'];

		$request = new Interspire_Request(array('display_name' => $displayName));
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$option = array_pop($list);
		$this->assertEquals($optionId, $option['id']);
		$this->assertEquals($displayName, $option['display_name']);
	}

	public function testGetWithTypeCondition()
	{
		// create an option
		$this->_createDummyOption();

		// now another with specific type (and name so we can filter correctly)
		$name = 'OPTION_' . mt_rand(0, PHP_INT_MAX);
		$option = $this->_createDummyOption(array('name' => $name, 'type' => 'CS'));
		$optionId = $option['id'];

		$request = new Interspire_Request(array('name' => $name, 'type' => 'CS'));
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$option = array_pop($list);
		$this->assertEquals($optionId, $option['id']);
		$this->assertEquals('CS', $option['type']);
	}

	/**
	* @expectedException Store_Api_Exception_Resource_MethodNotFound
	*/
	public function testPostToEntityFails()
	{
		$request = new Interspire_Request();
		$request->setUserParam('options', 1);
		$options = $this->_getResource()->postAction($request);
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
		$request->setUserParam('options', 999);
		$data = $this->_getResource()->putAction($request)->getData();
		$this->assertSame(0, count($data));
	}

	public function testPostDuplicateNameFails()
	{
		$data = array('name' => 'OPTION_' . mt_rand(0, PHP_INT_MAX));
		$this->_createDummyOption($data);

		try {
			$this->_createDummyOption($data);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}

	public function testPutDuplicateNameFails()
	{
		$data1 = array('name' => 'OPTION_' . mt_rand(0, PHP_INT_MAX));
		$option1 = $this->_createDummyOption($data1);

		$data2 = array('name' => 'OPTION_' . mt_rand(0, PHP_INT_MAX));
		$option2 = $this->_createDummyOption($data2);

		// rename option 1 to option 2
		$json = json_encode($data2);
		$request = $this->_getRequest($json, 'put');
		$request->setUserParam('options', $option1['id']);

		try {
			$this->_getResource()->putAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}

	/**
	* @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
	*/
	public function testPostMissingNameFails()
	{
		$this->_createDummyOption(array('name' => ''));
	}

	/**
	* @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
	*/
	public function testPostMissingTypeFails()
	{
		$this->_createDummyOption(array('type' => ''));
	}

	public function testPostWithoutDisplayName()
	{
		$option = $this->_createDummyOption(array('display_name' => ''));
		$this->assertEquals($option['name'], $option['display_name']);
	}

	public function testPostTypeWithoutView()
	{
		$option = $this->_createDummyOption();
		$attribute = new Store_Attribute();
		$attribute->load($option['id']);
		$this->assertFalse($attribute->getType()->getView());
	}

	public function testPostTypeWithView()
	{
		$option = $this->_createDummyOption(array('type' => 'RB'));
		$attribute = new Store_Attribute();
		$attribute->load($option['id']);
		$this->assertInstanceOf('Store_Attribute_View_Radio', $attribute->getType()->getView());
	}

	public function testPutWithoutType()
	{
		$option = $this->_createDummyOption();

		// rename option 1 to option 2
		$data = array('name' => 'OPTION_' . mt_rand(0, PHP_INT_MAX));
		$json = json_encode($data);
		$request = $this->_getRequest($json, 'put');
		$request->setUserParam('options', $option['id']);

		$data = $this->_getResource()->putAction($request)->getData(true);

		$this->assertEquals($option['type'], $data['type']);
	}

	public function testPutDifferentType()
	{
		$option = $this->_createDummyOption();

		// rename option 1 to option 2
		$data = array('type' => 'S');
		$json = json_encode($data);
		$request = $this->_getRequest($json, 'put');
		$request->setUserParam('options', $option['id']);

		$data = $this->_getResource()->putAction($request)->getData(true);

		$this->assertEquals('S', $data['type']);
	}

	public function testDeleteList()
	{
		// can't implement currently due to sample data
		$this->markTestIncomplete();
	}

	public function testDeleteEntity()
	{
		$option = $this->_createDummyOption();
		$request = new Interspire_Request();
		$request->setUserParam('options', $option['id']);
		$this->assertNull($this->_getResource()->deleteAction($request));
	}
}
