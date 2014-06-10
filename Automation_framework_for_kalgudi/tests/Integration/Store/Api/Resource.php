<?php

class Integration_Store_Api_Resource extends Unit_Lib_Store_Api_Base
{
	public function testGetResourceClassName()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$this->assertEquals('Customers_Addresses', $resource->getResourceClassName());
	}

	public function testGetResourceSegmentName()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$this->assertEquals('addresses', $resource->getResourceSegmentName());
	}

	public function testGetOutputDataWrapper()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$wrapper = $resource->getOutputDataWrapper(array(1));

		$this->assertInstanceOf('Store_Api_OutputDataWrapper', $wrapper);
		$this->assertEquals('address', $wrapper->getSingularName());
		$this->assertEquals('addresses', $wrapper->getPluralName());
		$this->assertEquals(array(1), $wrapper->getData());
	}

	public function testGetSingularName()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$this->assertEquals('address', $resource->getSingularName());
	}

	public function testGetPluralName()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$this->assertEquals('addresses', $resource->getPluralName());
	}

	public function testGetRequestRoute()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();

		$params = array('foo' => 'bar');
		$dataType = 'json';
		$route = $resource->getRequestRoute($params, $dataType);

		$this->assertInstanceOf('Store_RequestRoute_StoreApi', $route);
		$this->assertEquals('Store_Api_Version_2_Resource_Customers_Addresses', $route->getController());
		$this->assertEquals($params, $route->getParameters());
		$this->assertEquals($dataType, $route->getDataType());
	}

	public function testGetFields()
	{
		$resource = new Store_Api_Version_2_Resource_Products();

		$fields = $resource->getFields();
		$fieldsReference = PHPUnit_Framework_Assert::readAttribute($resource, '_fields');
		$this->assertEquals($fieldsReference, $fields);

		$fields = $resource->getFields(array('date_created', 'availability'));
		$this->assertCount(2, $fields);
		$this->assertArrayHasKey("date_created", $fields);
		$this->assertArrayHasKey("availability", $fields);
	}

	public function testGetDisplayedFields()
	{
		$resource = new Store_Api_Version_2_Resource_Products();

		$reflection = new ReflectionClass($resource);
		$fields = $reflection->getProperty('_fields');
		$fields->setAccessible(true);
		$fields->setValue($resource, array(
			'apple' => array(),
			'banana' => array(),
			'pear' => array(),
			'tomato' => array(),
			'onion' => array(),
		));
		$fieldGroups = $reflection->getProperty('_fieldGroups');
		$fieldGroups->setAccessible(true);
		$fieldGroups->setValue($resource, array(
			'@fruits' => array('apple', 'banana', 'pear'),
			'@veggies' => array('tomato', 'onion'),
		));
		$displayAlwaysFields = $reflection->getProperty('_displayAlwaysFields');
		$displayAlwaysFields->setAccessible(true);
		$displayAlwaysFields->setValue($resource, array(
			'id'
		));

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', ""),
					array('exclude', '', "")
				)
			));
		$this->assertEqualsArrays(array('id', 'apple', 'banana', 'pear', 'tomato', 'onion'), $resource->getDisplayedFields($requestMock));

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', "@veggies"),
					array('exclude', '', "")
				)
			));
		$this->assertEqualsArrays(array('id', 'tomato', 'onion'), $resource->getDisplayedFields($requestMock));

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', "@all"),
					array('exclude', '', "banana,pear")
				)
			));
		$this->assertEqualsArrays(array('id', 'apple', 'tomato', 'onion'), $resource->getDisplayedFields($requestMock));

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', "tomato,@fruits"),
					array('exclude', '', "pear")
				)
			));
		$this->assertEqualsArrays(array('id', 'apple', 'banana', 'tomato'), $resource->getDisplayedFields($requestMock));

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', "foo"),
					array('exclude', '', "pear")
				)
			));
		$this->setExpectedException('Store_Api_Exception_Request_InvalidField', 'Invalid field(s): foo');
		$resource->getDisplayedFields($requestMock);

		$requestMock = $this->getMock('Interspire_Request');
		$requestMock->expects($this->any())
			->method('get')
			->will($this->returnValueMap(
				array(
					array('include', '', "@all"),
					array('exclude', '', "bar")
				)
			));
		$this->setExpectedException('Store_Api_Exception_Request_InvalidField', 'Invalid field(s): bar');
		$resource->getDisplayedFields($requestMock);
	}

	public function testDisplayedFieldsToSqlFields()
	{
		$resource = new Store_Api_Version_2_Resource_Products();

		$reflection = new ReflectionClass($resource);
		$fields = $reflection->getProperty('_fields');
		$fields->setAccessible(true);
		$fields->setValue($resource, array(
			'id' => array(
				'db_field' => array('fruits' => 'id')
			),
			'apple' => array(
				'db_field' => array('fruits' => 'apple')
			),
			'pear' => array(
				'db_field' => array('fruits' => 'pear')
			),
			'onion' => array(
				'db_field' => array('veggies' => 'onion')
			),
			'pear_and_onion' => array(
				'formatters_params' => array(
					'my_formatter' => array(
						'inject_db_fields' => array(
							'foo' => array('fruits' => 'pear'),
							'bar' => array('veggies' => 'onion')
						)
					)
				)
			)
		));
		$fieldGroups = $reflection->getProperty('_fieldGroups');
		$fieldGroups->setAccessible(true);
		$fieldGroups->setValue($resource, array(
			'@fruits' => array('apple', 'pear'),
			'@veggies' => array('tomato', 'onion'),
		));
		$displayAlwaysFields = $reflection->getProperty('_displayAlwaysFields');
		$displayAlwaysFields->setAccessible(true);
		$displayAlwaysFields->setValue($resource, array(
			'id'
		));

		$this->assertEqualsArrays(array("`fruits`.`id`", "`fruits`.`apple`", "`veggies`.`onion`"), $resource->displayedFieldsToSqlFields(array("apple", "onion")));
		$this->assertEqualsArrays(array("`fruits`.`id`"), $resource->displayedFieldsToSqlFields(array()));
		$this->assertEqualsArrays(array("`fruits`.`id`", "`fruits`.`pear`", "`veggies`.`onion`"), $resource->displayedFieldsToSqlFields(array("pear_and_onion")));
	}

	public function testIsRequestForEntity()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$request = new Interspire_Request();

		$this->assertFalse($resource->isRequestForEntity($request));

		$request->setUserParam('customers', 1);
		$this->assertTrue($resource->isRequestForEntity($request));
	}

	public function testGetParentResource()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Addresses();
		$this->assertInstanceOf('Store_Api_Version_2_Resource_Customers', $resource->getParentResource());

		$resource = new Store_Api_Version_2_Resource_Customers();
		$this->assertFalse($resource->getParentResource());
	}

	public function testOptionsAction()
	{
		$resource = new Store_Api_Version_2_Resource_Brands();
		$request = new Interspire_Request();
		$resource->optionsAction($request);
		$headers = $request->getResponse()->getHeaders();
		$allowedMethods = $request->getResponse()->getHeader('Allow');
		$this->assertEquals('GET, POST, DELETE, HEAD, OPTIONS', $allowedMethods);
	}

	public function testHeadAction()
	{
		// @todo fix this test
		$this->markTestSkipped();
		return;

		$resource = new Store_Api_Version_2_Resource_Customers();
		$request = new Interspire_Request();
		$resource->headAction($request);
		$this->assertEquals(Interspire_Response::STATUS_OK, $request->getResponse()->getStatus());
	}

	/**
	* @expectedException Store_Api_Exception_Request_InvalidHeader
	*/
	public function testInvalidIfModifiedSince()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$server = array(
			'HTTP_IF_MODIFIED_SINCE' => 'foo',
		);
		$request = new Interspire_Request(null, null, null, $server);
		$resource->getAction($request);
	}

	public function testValidIfModifiedSince()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$server = array(
			'HTTP_IF_MODIFIED_SINCE' => 'Fri, 19 Aug 2011 12:00:00 +0000',
		);
		$request = new Interspire_Request(null, null, null, $server);
		$resource->getAction($request);
		$this->assertEquals(Interspire_Response::STATUS_OK, $request->getResponse()->getStatus());
	}

	protected function assertEqualsArrays($expected, $actual) {
		$this->assertTrue(count($expected) == count(array_intersect($expected, $actual)));
	}
}
