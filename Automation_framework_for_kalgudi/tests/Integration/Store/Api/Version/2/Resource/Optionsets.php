<?php

class Unit_Lib_Store_Api_Version_2_Resource_Optionsets extends Interspire_IntegrationTest
{
	private $_optionSets = array();

	private function _getResource ()
	{
		return new Store_Api_Version_2_Resource_Optionsets();
	}

	private function _getCountResource ()
	{
		return new Store_Api_Version_2_Resource_Optionsets_Count();
	}

	private function _generateName ($prefix = 'OPTIONSET_')
	{
		return $prefix . mt_rand(0, PHP_INT_MAX);
	}

	private function _createOptionSet ($data = array(), $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$body = Interspire_Json::encode(array_merge(array(
			'name' => $this->_generateName(),
		), $data));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $body);

		$result = $resource->postAction($request)->getData(true);

		$this->_optionSets[] = $result['id'];
		return $result;
	}

	private function _updateOptionSet ($id, $json, $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$json = Interspire_Json::encode($json);

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('optionsets', (int)$id);

		return $resource->putAction($request)->getData(true);
	}

	private function _deleteOptionSet ($id, $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$request = new Interspire_Request();
		$request->setUserParam('optionsets', (int)$id);
		$resource->deleteAction($request);
	}

	public function tearDown ()
	{
		foreach ($this->_optionSets as $id) {
			$this->_deleteOptionSet($id);
		}
	}

	public function testPostDuplicateNameConflict ()
	{
		$name = $this->_generateName();

		$this->_createOptionSet(array('name' => $name));

		try {
			$this->_createOptionSet(array('name' => $name));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$this->assertNotEquals('', $e->getDetail('conflict_reason'), 'conflict has no reason');
		}
	}

	public function testPutDuplicateNameConflict ()
	{
		$set_a = $this->_createOptionSet();
		$set_b = $this->_createOptionSet();

		try {
			$this->_updateOptionSet($set_b['id'], array('name' => $set_a['name']));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$this->assertNotEquals('', $e->getDetail('conflict_reason'), 'conflict has no reason');
		}
	}

	public function testPostEntity ()
	{
		$body = Interspire_Json::encode(array(
			'name' => $this->_generateName(),
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $body);
		$request->setUserParam('optionsets', PHP_INT_MAX);

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$this->_getResource()->postAction($request);
	}

	public function testPutList ()
	{
		$body = Interspire_Json::encode(array(
			'name' => $this->_generateName(),
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $body);

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$this->_getResource()->putAction($request);
	}

	public function testGetMissingEntity ()
	{
		$request = new Interspire_Request();
		$request->setUserParam('optionsets', PHP_INT_MAX);

		$result = $this->_getResource()->getAction($request)->getData();
		$this->assertSame(array(), $result);
	}

	public function testPutMissingEntity ()
	{
		$body = Interspire_Json::encode(array(
			'name' => $this->_generateName(),
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $body);
		$request->setUserParam('optionsets', PHP_INT_MAX);

		$result = $this->_getResource()->putAction($request)->getData();
		$this->assertSame(array(), $result);
	}

	public function testCount ()
	{
		$request = new Interspire_Request();

		$count_a = $this->_getCountResource()->getAction($request)->getData(true);
		$this->assertInternalType('int', $count_a['count'], "initial count failure");

		$this->_createOptionSet();
		$count_b = $this->_getCountResource()->getAction($request)->getData(true);
		$this->assertSame($count_a['count'] + 1, $count_b['count'], "count mismatch");
	}
	
	public function testGetNameFilter ()
	{
	    $foo = $this->_createOptionSet();
	    $bar = $this->_createOptionSet();
	    $request = new Interspire_Request(array('name' => $foo['name']),
                                          null, null,
                                          array('REQUEST_METHOD' => 'get',
                                                'CONTENT_TYPE' => 'application/json'));
	    $this->assertEquals(array($foo), $this->_getResource()->getAction($request)->getData());
	}
	
}
