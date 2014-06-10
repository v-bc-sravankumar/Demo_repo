<?php

class Integration_Lib_Store_Api_Version_2_Resource_Customers_Addresses extends Interspire_IntegrationTest
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		Interspire_DataFixtures::getInstance()->loadData('customers', 'shipping_addresses');
	}

	public static function tearDownAfterClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('customers', 'shipping_addresses');
	}

	public function testGetAction()
	{
		$request = new Interspire_Request();
		$request->setUserParam('addresses', 1);
		$wrapper = $this->_getResource()->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];

		$expected = array(
			'id' => 1,
			'customer_id' => 1,
			'first_name' => 'John',
			'last_name' => 'Smith',
			'company' => 'ACME',
			'street_1' => '4941 Charles Street',
			'street_2' => '',
			'city' => 'Southfield',
			'state' => 'MI',
			'zip' => 48075,
			'country' => 'United States',
			'phone' => '734-506-8973',
			'country_iso2' => 'US',
            'address_type' => 'residential',
		);

		$this->assertEquals($expected, $data);
	}

    public function testPostAction()
    {
        $customer = $this->_createDummyCustomer();
        $json = json_encode($this->getPayload());

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('customers', $customer->getId());
        $address = $this->_getResource()->postAction($request)->getData(true);
        $this->assertNotEmpty($address);

        foreach ($this->getExpected() as $key => $value) {
            $this->assertTrue(array_key_exists($key, $address));
            if (is_scalar($value)) {
                $this->assertEquals($value, $address[$key]);
            } else {
                $this->assertEmpty(array_diff($value, $address[$key]));
            }
        }

        // BIG-7056: Test United States gets countryid and MI gets stateid
        $record = Store_Customer_Address::find($address['id'])->first();
        $this->assertNotEmpty($record->getCountryId());
        $this->assertNotEmpty($record->getStateId());
        
        $record->delete();
        $customer->delete();
    }

    private function getPayload()
    {
        return array(
            'first_name' => 'John',
            'last_name' => 'Smith',
            'company' => 'ACME',
            'street_1' => '4941 Charles Street',
            'city' => 'Southfield',
            'state' => 'MI',
            'zip' => 48075,
            'country' => 'United States',
            'phone' => '734-506-8973',
        );
    }

    private function getExpected()
    {
        return array(
            'first_name' => 'John',
            'last_name' => 'Smith',
            'company' => 'ACME',
            'street_1' => '4941 Charles Street',
            'city' => 'Southfield',
            'state' => 'Michigan',
            'zip' => 48075,
            'country' => 'United States',
            'phone' => '734-506-8973',
        );
    }

    public function testPostActionNoCustomerId()
    {
        try {
            $json = json_encode(array());
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
            $this->_getResource()->postAction($request)->getData(true);
        } catch (Store_Api_Exception_Request_InvalidField $e) {
            $this->assertEquals('customer_id', $e->getField());
            $this->assertEquals('Customer Id must be specified', $e->getMessage());
        }
    }

    public function testPostActionMissingFields()
    {
        $customer = $this->_createDummyCustomer();

        try {
            $payload = $this->getPayload();
            unset($payload['first_name']);

            $json = json_encode($payload);
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
            $request->setUserParam('customers', $customer->getId());

            $this->_getResource()->postAction($request)->getData(true);
        } catch (Store_Api_Exception_Request_InvalidField $e) {
            $this->assertEquals("The required field 'first_name' was not supplied.", $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }

        $customer->delete();
    }

    public function testPutActionFail()
    {
        // payload to change it
        $payload = array(
            'company' => 'Bigcommerce',
            'street_1' => '1-3 Smail St',
            'street_2' => '',
            'city' => 'Sydney',
            'state' => 'NSW',
            'zip' => 2065,
            'country' => 'Australia'
        );
        $json = json_encode($payload);

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('addresses', 999999);
        $request->setUserParam('customers', 999999);
        try {
            $this->_getResource()->putAction($request)->getData(true);
        } catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
            $details = $e->getDetails();
            $this->assertEquals('Either AddressId or CustomerId is incorrect.', $details['reason']);
        }
    }

    public function testPutActionSuccess()
    {
        // setup test entry
        $rval = $this->_createDummyAddress();

        /** @var $customer Store_Customer */
        $customer = $rval[0];
          /** @var $customerAddress Store_Customer_Address */
        $customerAddress = $rval[1];

        // payload to change it
        $payload = array(
            'company' => 'Bigcommerce',
            'street_1' => '1-3 Smail St',
            'street_2' => '',
            'city' => 'Sydney',
            'state' => 'NSW',
            'zip' => 2065,
            'country' => 'Australia'
        );
        $json = json_encode($payload);

        $expected = array(
            'company' => 'Bigcommerce',
            'street_1' => '1-3 Smail St',
            'street_2' => '',
            'city' => 'Sydney',
            'state' => 'New South Wales',
            'zip' => 2065,
            'country' => 'Australia'
        );

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('addresses', $customerAddress->getId());
        $request->setUserParam('customers', $customerAddress->getCustomerId());
        $address = $this->_getResource()->putAction($request)->getData(true);
        $this->assertNotEmpty($address);

        foreach ($expected as $key => $value) {
            $this->assertTrue(array_key_exists($key, $address));
            if (is_scalar($value)) {
                $this->assertEquals($value, $address[$key]);
            } else {
                $this->assertEmpty(array_diff($value, $address[$key]));
            }
        }

        // BIG-7056: Test Australia gets countryid and NSW becomes 'NSW'
        $record = Store_Customer_Address::find($address['id'])->first();
        $this->assertNotEmpty($record->getCountryId());
        $this->assertNotEmpty($record->getState());

        $customerAddress->delete();
        $customer->delete();
    }

    public function testDeleteActionFail()
    {
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
        $request->setUserParam('addresses', 999999);
        $request->setUserParam('customers', 999999);
        try {
            $this->_getResource()->deleteAction($request);
        } catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
            $details = $e->getDetails();
            $this->assertEquals('Either AddressId or CustomerId is incorrect.', $details['reason']);
        }
    }

    public function testDeleteActionSuccess()
    {
        $rval = $this->_createDummyAddress();

        /** @var $customer Store_Customer */
        $customer = $rval[0];
        /** @var $customerAddress Store_Customer_Address */
        $customerAddress = $rval[1];

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
        $request->setUserParam('addresses', $customerAddress->getId());
        $request->setUserParam('customers', $customerAddress->getCustomerId());
        $this->_getResource()->deleteAction($request);

        $this->assertEquals(0, Store_Customer_Address::find($customerAddress->getId())->count());

        $customer->delete();
    }

    public function testDeleteAll()
    {
        $rval = $this->_createDummyAddress();

        /** @var $customer Store_Customer */
        $customer = $rval[0];
        /** @var $customerAddress Store_Customer_Address */
        $customerAddress = $rval[1];

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
        $request->setUserParam('customers', $customerAddress->getCustomerId());
        $this->_getResource()->deleteAction($request);

        $this->assertEquals(0, Store_Customer_Address::findByCustomerId($customerAddress->getCustomerId())->count());

        $customer->delete();
    }

	public function testCountAll()
	{
		$request = new Interspire_Request();
		$wrapper = $this->_getCountResource()->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(30, $data['count']);
	}

	public function testCountSingle()
	{
		$request = new Interspire_Request();
		$request->setUserParam('addresses', 1);
		$wrapper = $this->_getCountResource()->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(1, $data['count']);
	}

	public function testFilterByCustomer()
	{
		$request = new Interspire_Request();
		$request->setUserParam('customers', 1);
		$wrapper = $this->_getCountResource()->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$data = $data[0];
		$this->assertEquals(1, $data['count']);
	}

    /**
     * @return Store_Api_Version_2_Resource_Customers_Addresses
     */
    private function _getResource()
    {
        return new Store_Api_Version_2_Resource_Customers_Addresses();
    }

    private function _getCountResource()
    {
        return new Store_Api_Version_2_Resource_Customers_Addresses_Count();
    }

    private function _createDummyCustomer()
    {
        $customer = new Store_Customer();
        $customer->setCompany('ACME');
        $customer->setFirstName('john');
        $customer->setLastName('smith');
        $customer->setEmail('john.smith@acme.com');
        $customer->save();

        return $customer;
    }

    private function _createDummyAddress()
    {
        $customer = $this->_createDummyCustomer();

        // setup test entry
        $customerAddress = new Store_Customer_Address();

        $customerAddress->setFirstName('John');
        $customerAddress->setLastName('Smith');
        $customerAddress->setCompany('ACME');
        $customerAddress->setAddressLine1('4942 Charles Street');
        $customerAddress->setCity('Southfield');
        $customerAddress->setState('MI');
        $customerAddress->setCountry('United States');
        $customerAddress->setZip(48075);
        $customerAddress->setCustomerId($customer->getId());
        $customerAddress->save();

        return array($customer, $customerAddress);
    }
}
