<?php

class Unit_Lib_Store_Api_Version_2_Resource_Customers extends Interspire_IntegrationTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		Interspire_DataFixtures::getInstance()->loadData('customers');
	}

	public static function tearDownAfterClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('customers');
	}

	public function testGetAction()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$request = new Interspire_Request();
		$request->setUserParam('customers', 1);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];

		$this->assertInstanceOf('Store_RequestRoute_StoreApi', $data['addresses']);
		$this->assertEquals(array('customers' => '1'), $data['addresses']->getParameters());
		$this->assertEquals('Store_Api_Version_2_Resource_Customers_Addresses', $data['addresses']->getController());

		unset($data['addresses']);

		$expected = array(
			'id' => 1,
			'company' => '',
			'first_name' => 'Denise',
			'last_name' => 'Koenig',
			'email' => 'Denise.M.Koenig@mailinator.com',
			'phone' => '734-506-8973',
			'date_created' => 'Wed, 09 Apr 2008 02:41:58 +0000',
			'date_modified' => 'Wed, 09 Apr 2008 02:41:58 +0000',
			'store_credit' => '0.0000',
			'registration_ip_address' => '',
			'customer_group_id' => 0,
			'notes' => null,
		);

		$this->assertEquals($expected, $data);
	}

	protected function getCustomerDataSet($index = 0)
	{
		$data = array(
			array(
				'_authentication' => array(
					'password' => 'Foo',
					'password_confirmation' => 'Foo',
				),
				'company' => 'My Company',
				'first_name' => 'John',
				'last_name' => 'Smith',
				'email' => 'john.smith@example.com',
				'phone' => '+61 2 812345789',
				'store_credit' => 23.45,
				'registration_ip_address' => '123.456.789.012',
				'customer_group_id' => 2,
				'notes' => 'These are my notes',
			),
			array(
				'_authentication' => array(
					'password' => 'Bar',
					'password_confirmation' => 'Bar',
				),
				'company' => 'Some Company',
				'first_name' => 'Bob',
				'last_name' => 'Bobster',
				'email' => 'bob@example.com',
				'phone' => '1800 BOB',
				'store_credit' => 98.21,
				'registration_ip_address' => '987.654.321.098',
				'customer_group_id' => 3,
				'notes' => 'Dont sell to Bob.',
			),
		);

		return $data[$index];
	}

	protected function createCustomer($data)
	{
		return DataModel_ApiFinder::createObject('Customers', $data);
	}

	protected function updateCustomer($data, $id)
	{
		return DataModel_ApiFinder::updateObject('Customers', $id, $data);
	}

	protected function deleteCustomer($id)
	{
		$customer = new Store_Customer();
		if (!$customer->load($id)) {
			$this->fail("Customer $id not found.");
		}

		$customer->delete();
	}

	public function testPostAction()
	{
		$data = $this->getCustomerDataSet();
		$result = $this->createCustomer($data);

		$expected = $data;
		unset($expected['_authentication']); // this is a post/put only field

		// ensure dates auto created
		$this->assertNotEmpty($result['date_created']);
		$this->assertNotEmpty($result['date_modified']);

		$id = $result['id'];
		unset($result['id']);
		unset($result['date_created']);
		unset($result['date_modified']);

		$this->assertInstanceOf('Store_RequestRoute_StoreApi', $result['addresses']);
		unset($result['addresses']);

		$this->assertEquals($expected, $result);

		$this->deleteCustomer($id);
	}

	public function testPutAction()
	{
		$data = $this->getCustomerDataSet();
		$result = $this->createCustomer($data);

		$id = $result['id'];

		$newData = $this->getCustomerDataSet(1);

		unset($newData['_authentication']);
		$updatedCustomer = $this->updateCustomer($newData, $id);

		unset($updatedCustomer['id']);
		unset($updatedCustomer['date_created']);
		unset($updatedCustomer['date_modified']);
		unset($updatedCustomer['addresses']);

		$this->assertEquals($newData, $updatedCustomer);

		$this->deleteCustomer($id);
	}

	public function testDeleteAction()
	{
		$data = $this->getCustomerDataSet();
		$result = $this->createCustomer($data);

		$id = $result['id'];

		DataModel_ApiFinder::deleteObject('Customers', $id);

		$this->assertEquals(0, Store_Customer::find($id)->count());
	}

	public function testCountAll()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$request = new Interspire_Request();
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(30, $data['count']);
	}

	public function testCountSingle()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$request = new Interspire_Request();
		$request->setUserParam('customers', 1);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(1, $data['count']);
	}

	public function testIfModifiedSince()
	{
		// check for modified customers
		$resource = new Store_Api_Version_2_Resource_Customers();
		$server = array(
			'HTTP_IF_MODIFIED_SINCE' => 'Thu, 10 Apr 2008 12:00:00 +0000',
		);
		$get = array(
			'limit' => 1,
		);
		$request = new Interspire_Request($get, null, null, $server);
		$resource->getAction($request);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		// check for no modified customers
		$server = array(
			'HTTP_IF_MODIFIED_SINCE' => 'Mon, 21 Mar 2011 12:00:00 +0000',
		);
		$request = new Interspire_Request($get, null, null, $server);
		$resource->getAction($request);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsEmpty($data);
		$this->assertEquals(Interspire_Response::STATUS_NOT_MODIFIED, $request->getResponse()->getStatus());
	}

	public function testMinMaxId()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$get = array(
			'min_id' => 10,
			'max_id' => 12,
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();

		$this->assertArrayIsNotEmpty($data);

		$minCustomer = current($data);
		$this->assertEquals(10, $minCustomer['id']);

		$maxCustomer = end($data);
		$this->assertEquals(12, $maxCustomer['id']);
	}

	public function testMinDateCreated()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$get = array(
			'min_date_created' => 'Thu, 10 Apr 2008 12:00:00 +0000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(15, $data['count']);



		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$get = array(
			'min_date_created' => 'Sun, 13 Mar 2011 18:06:40 +0000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(0, $data['count']);
	}

	public function testMaxDateCreated()
	{
		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$get = array(
			'max_date_created' => 'Thu, 10 Apr 2008 12:00:00 +0000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(15, $data['count']);



		$resource = new Store_Api_Version_2_Resource_Customers_Count();
		$get = array(
			'max_date_created' => 'Thu, 01 Jan 1970 10:00:00 +1000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(0, $data['count']);
	}

	public function testEntityExists()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$this->assertTrue($resource->entityExists(1));
		$this->assertFalse($resource->entityExists(50));
	}

	public function testFilterFirstName()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$get = array(
			'first_name' => 'Denise',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals('Denise', $data['first_name']);
	}

	public function testZeroEntityRequestIsEmpty ()
	{
		$resource = new Store_Api_Version_2_Resource_Customers();
		$request = new Interspire_Request();
		$request->setUserParam('customers', 0);

		$this->assertArrayIsEmpty($resource->getAction($request)->getData());
	}

	/**
	 * BIG-5865: Updating a customer with the same email results in an error
	 */
	public function testPutCustomerWithSameDataSucceeds()
	{
		$data = $this->getCustomerDataSet();
		$result = $this->createCustomer($data);

		$id = $result['id'];

		unset($data['id']);
		unset($data['date_created']);
		unset($data['date_modified']);
		unset($data['addresses']);

		// should not trigger an exception
		$this->updateCustomer($data, $id);

		$this->deleteCustomer($id);
	}

	/**
	 * BIG-5866: Updating a customer is updating the created time
	 */
	public function testPutCustomerDoesntUpdateCreatedTime()
	{
		$customer = new Store_Customer();
		$customer
			->setFirstName('Nobby')
			->setLastName('Doldrums')
			->setEmail('nobby@doldrumsinc.com')
			->setDateCreated(1234567890)
			->save();

		$id = $customer->getId();

		$data = array(
			'first_name' => 'Bob',
		);

		// should not trigger an exception
		$this->updateCustomer($data, $id);

		$customer->delete();
	}

	/**
	 * BIG-5866: Updating a customer isn't updating the modified time
	 */
	public function testPutCustomerUpdatesUpdatedTime()
	{
		$customer = new Store_Customer();
		$customer
			->setFirstName('Nobby')
			->setLastName('Doldrums')
			->setEmail('nobby@doldrumsinc.com')
			->setDateCreated(1234567890)
			->setDateModified(1234567890)
			->save();

		$id = $customer->getId();

		$data = array(
			'first_name' => 'Bob',
		);

		// should not trigger an exception
		$result = $this->updateCustomer($data, $id);

		$modifiedTime = Interspire_DateTime::parseRfc2822Date($result['date_modified'])->format('U');

		$this->assertGreaterThan($customer->getDateModified(), $modifiedTime);

		$this->deleteCustomer($id);
	}
}
