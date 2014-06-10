<?php

class Unit_Lib_Store_Api_Version_2_Resource_Mobile_Customers extends Interspire_IntegrationTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		Interspire_DataFixtures::getInstance()->loadData('customers', 'orders-some-deleted');
	}

	public static function tearDownAfterClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('customers', 'orders-some-deleted');
	}

	public function testGetAction()
	{
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers();
		$request = new Interspire_Request();
		$request->setUserParam('customers', 1);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];

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
			'notes' => '',
			'address' => array(
				'company' => null,
				'street_1' => null,
				'street_2' => null,
				'city' => null,
				'state' => null,
				'zip' => null,
				'country' => null,
			),
			'gravatar' => 'http://www.gravatar.com/avatar/46d68054bf8ca20466fc9779270d5597?d=identicon&s=70',
			'order_count' => 0,
			'order_total' => 0.0,
			'order_total_formatted' => '$0.00',
		);
		unset($data['incomplete_orders']);
		$this->assertEquals($expected, $data);
	}

	public function testCountAll()
	{
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
		$request = new Interspire_Request();
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(30, $data['count']);
	}

	public function testCountSingle()
	{
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
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
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers();
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
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers();
		$get = array(
			'min_id' => 10,
			'max_id' => 12,
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();

		$this->assertArrayIsNotEmpty($data);

		$maxCustomer = current($data);
		$this->assertEquals(12, $maxCustomer['id']);

		$minCustomer = end($data);
		$this->assertEquals(10, $minCustomer['id']);
	}

	public function testMinDateCreated()
	{
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
		$get = array(
			'min_date_created' => 'Thu, 10 Apr 2008 12:00:00 +0000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(15, $data['count']);



		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
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
		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
		$get = array(
			'max_date_created' => 'Thu, 10 Apr 2008 12:00:00 +0000',
		);
		$request = new Interspire_Request($get);
		$wrapper = $resource->getAction($request);
		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(15, $data['count']);



		$resource = new Store_Api_Version_2_Resource_Mobile_Customers_Count();
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
}
