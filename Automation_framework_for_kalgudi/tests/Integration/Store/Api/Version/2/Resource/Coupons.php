<?php

class Unit_Lib_Store_Api_Version_2_Resource_Coupons extends Interspire_IntegrationTest
{
	private $dummyCoupons = array();

	private static $types = array(
		'per_item_discount',
    	'percentage_discount',
    	'per_total_discount',
    	'shipping_discount',
    	'free_shipping',
	);

	/**
	 * @return Store_Api_Version_2_Resource_Coupons
	 */
	private function _getResource()
	{
		return new Store_Api_Version_2_Resource_Coupons();
	}


    /**
     * @param array $data
     *
     * @throws Exception
     * @return Store_Coupon
     */
	private function _createDummyCoupon($data = null)
	{
		$coupon = new Store_Coupon();
		foreach ($data as $k => $v) {
			$method = 'set'.$k;
			$coupon->$method($v);
		}
		if (!$coupon->save()) {
			throw new Exception("Unable to create coupon.");
		}
		$this->dummyCoupons[] = $coupon;
		return $coupon;
	}

	public function tearDown()
	{
		foreach ($this->dummyCoupons as $coupon) {
			$coupon->delete();
		}
		$this->dummyCoupons = array();
	}

	/**
	 * Test normal listing
	 */
	public function testList()
	{
		// create a whole bunch of coupons
		for ($i = 0; $i < 10; $i++) {
			$this->_createDummyCoupon(array(
				'name' => uniqid(),
				'type' => rand(0, 4),
				'amount' => rand(1, 10),
				'code' => uniqid(),
			));
		}

		$coupons = Store_Coupon::find();

		$expectedCouponIds = array();
		foreach ($coupons as $coupon) {
			$expectedCouponIds[] = $coupon->getId();
		}
		sort($expectedCouponIds);

		$res = $this->_getResource()->getAction(new Interspire_Request())->getData();
		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);
		sort($actualCouponIds);

		$this->assertEquals($expectedCouponIds, $actualCouponIds);

	}

	/**
	 * Test listing with name filter
	 */
	public function testListWithNameFilter()
	{
		// create a whole bunch of coupons
		for ($i = 0; $i < 10; $i++) {
			$this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
		}

		// create a whole bunch of coupons with a specific name
		for ($i = 0; $i < 5; $i++) {
			$this->_createDummyCoupon(array(
					'name' => uniqid() . " test " . uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
		}

		$coupons = Store_Coupon::find("`couponname` like '%" . Store::getStoreDb()->Quote('test') . "%'");

		$expectedCouponIds = array();
		foreach ($coupons as $coupon) {
			$expectedCouponIds[] = $coupon->getId();
		}
		sort($expectedCouponIds);

		$res = $this->_getResource()->getAction(new Interspire_Request(array('name' => 'test')))->getData();
		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);
		sort($actualCouponIds);

		$this->assertEquals($expectedCouponIds, $actualCouponIds);
	}

	/**
	 * Listing with minimum query
	 */
	public function testListWithMinIdFilter()
	{
		// create a whole bunch of coupons
		$randomId = 0;
		for ($i = 0; $i < 10; $i++) {
			$coupon = $this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
			if ($i == 5) {
				$randomId = $coupon->getId();
			}
		}

		$coupons = Store_Coupon::find('`couponid` >= '.$randomId);

		$expectedCouponIds = array();
		foreach ($coupons as $coupon) {
			$expectedCouponIds[] = $coupon->getId();
		}
		sort($expectedCouponIds);

		$res = $this->_getResource()->getAction(new Interspire_Request(array('min_id' => $randomId)))->getData();
		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);
		sort($actualCouponIds);

		$this->assertEquals($expectedCouponIds, $actualCouponIds);
	}

	/**
	 * Listing with max query
	 */
	public function testListWithMaxIdFilter()
	{
		// create a whole bunch of coupons
		$randomId = 0;
		for ($i = 0; $i < 10; $i++) {
			$coupon = $this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
			if ($i == 5) {
				$randomId = $coupon->getId();
			}
		}

		$coupons = Store_Coupon::find('`couponid` <= '.$randomId);

		$expectedCouponIds = array();
		foreach ($coupons as $coupon) {
			$expectedCouponIds[] = $coupon->getId();
		}
		sort($expectedCouponIds);

		$res = $this->_getResource()->getAction(new Interspire_Request(array('max_id' => $randomId)))->getData();
		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);
		sort($actualCouponIds);

		$this->assertEquals($expectedCouponIds, $actualCouponIds);
	}

	/**
	 * Listing with code filter
	 */
	public function testListWithCodeFilter()
	{
		// create a whole bunch of coupons
		for ($i = 0; $i < 10; $i++) {
			$this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
		}

		// create a whole bunch of coupons with a specific code
		for ($i = 0; $i < 5; $i++) {
			$this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => rand(0, 4),
					'amount' => rand(1, 10),
					'code' => uniqid() . " test " . uniqid(),
			));
		}

		$coupons = Store_Coupon::find("`couponcode` like '%" . Store::getStoreDb()->Quote('test') . "%'");

		$expectedCouponIds = array();
		foreach ($coupons as $coupon) {
			$expectedCouponIds[] = $coupon->getId();
		}
		sort($expectedCouponIds);

		$res = $this->_getResource()->getAction(new Interspire_Request(array('code' => 'test')))->getData();

		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);
		sort($actualCouponIds);

		$this->assertEquals($expectedCouponIds, $actualCouponIds);
	}

	/**
	 * Test listing with type filter
	 */
	public function testListWithTypeFilter()
	{
		// create a whole bunch of coupons
		for ($i = 0; $i < 50; $i++) {
			$this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => $i % 5,
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));
		}

		for ($type = 0; $type < 5; $type++) {
			$coupons = Store_Coupon::find("`coupontype` = " . $type);

			$expectedCouponIds = array();
			foreach ($coupons as $coupon) {
				$expectedCouponIds[] = $coupon->getId();
			}
			sort($expectedCouponIds);

			$res = $this->_getResource()->getAction(new Interspire_Request(array('type' => self::$types[$type])))->getData();

			$actualCouponIds = array_map(function($coupon) {
				return $coupon['id'];
			}, $res);
			sort($actualCouponIds);

			$this->assertEmpty(array_diff($expectedCouponIds, $actualCouponIds));
		}
	}

	/**
	 * Test a get request with given id
	 * @return unknown
	 */
	public function testGetWithId()
	{
		$coupon = $this->_createDummyCoupon(array(
					'name' => uniqid(),
					'type' => 1,
					'amount' => rand(1, 10),
					'code' => uniqid(),
			));

		$request = new Interspire_Request();
		$request->setUserParam('coupons',  $coupon->getId());
		$res = $this->_getResource()->getAction($request)->getData();

		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);

		$this->assertEquals(1, count($actualCouponIds));
		$this->assertEquals($coupon->getId(), $actualCouponIds[0]);

	}

	/**
	 * Test get with invalid id
	 */
	public function testGetWithInvalidId()
	{
		$request = new Interspire_Request();
		$request->setUserParam('coupons',  0);
		$res = $this->_getResource()->getAction($request)->getData();
		$actualCouponIds = array_map(function($coupon) {
			return $coupon['id'];
		}, $res);

		$this->assertTrue(count($res) == 0);
	}

	/**
	 * Test creation via POST
	 */
	public function testCreate()
	{
		$payload = array(
			'name' => 'test coupon '.uniqid(),
			'type' => 'per_item_discount',
			'amount' => floatval(5.0000),
			'code' => uniqid(),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(1, 2),
			),
		);
		$json = json_encode($payload);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);

		$coupon = $this->_getResource()->postAction($request)->getData(true);

		$this->assertNotEmpty($coupon);

		foreach ($payload as $key => $value) {
			$this->assertTrue(array_key_exists($key, $coupon));
			if (is_scalar($value)) {
				$this->assertEquals($value, $coupon[$key]);
			} else {
				$this->assertEmpty(array_diff($value, $coupon[$key]));
			}
		}

	}

	/**
	 * Test creation via POST
	 */
	public function testCreateForCouponTypes()
	{
		foreach (self::$types as $type) {
			$payload = array(
				'name' => 'test coupon '.uniqid(),
				'type' => $type,
				'amount' => floatval(5.0000),
				'code' => uniqid(),
				'applies_to' => array(
					'entity' => 'categories',
					'ids' => array(1, 2),
				),
			);
			$json = json_encode($payload);

			$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);

			$coupon = $this->_getResource()->postAction($request)->getData(true);

			$this->assertNotEmpty($coupon);

			foreach ($payload as $key => $value) {
				$this->assertTrue(array_key_exists($key, $coupon));
				if (is_scalar($value)) {
					$this->assertEquals($value, $coupon[$key]);
				} else {
					$this->assertEmpty(array_diff($value, $coupon[$key]));
				}
			}
		}
	}

	/**
	 * Test creation via POST
	 */
	public function testCreateWithDuplicateName()
	{
		$payload = array(
			'name' => 'test coupon duplicate',
			'type' => 'per_item_discount',
			'amount' => floatval(5.0000),
			'code' => uniqid(),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(1, 2),
			),
		);
		$json = json_encode($payload);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$coupon = $this->_getResource()->postAction($request)->getData(true);

		$this->assertNotEmpty($coupon);

		foreach ($payload as $key => $value) {
			$this->assertTrue(array_key_exists($key, $coupon));
			if (is_scalar($value)) {
				$this->assertEquals($value, $coupon[$key]);
			} else {
				$this->assertEmpty(array_diff($value, $coupon[$key]));
			}
		}

		$payload['code'] = uniqid();
		$json = json_encode($payload);

		try {
			$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
			$coupon = $this->_getResource()->postAction($request)->getData(true);
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$details = $e->getDetails();
			$this->assertEquals($payload['name'], $details['duplicate_coupon']);
		}

	}

	/**
	 * Test create with invalid country restriction
	 *
	 * @expectedException Store_Api_Exception_Request_InvalidField
	 */
	public function testCreateWithInvalidCountryRestriction()
	{
		$payload = array(
				'name' => 'test coupon invalid country',
				'type' => 'per_item_discount',
				'amount' => floatval(5.0000),
				'code' => uniqid(),
				'applies_to' => array(
						'entity' => 'categories',
						'ids' => array(1, 2),
				),
				'restricted_to' => array(
					'countries' => array('XXX'),
				),
		);
		$json = json_encode($payload);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);

		$coupon = $this->_getResource()->postAction($request)->getData(true);

	}

	/**
	 * Test create with invalid state restriction
	 *
	 * @expectedException Store_Api_Exception_Request_InvalidField
	 */
	public function testCreateWithInvalidStateRestriction()
	{
		$payload = array(
				'name' => 'test coupon invalid state',
				'type' => 'per_item_discount',
				'amount' => floatval(5.0000),
				'code' => uniqid(),
				'applies_to' => array(
						'entity' => 'categories',
						'ids' => array(1, 2),
				),
				'restricted_to' => array(
						'states' => array(
							'AU' => array('XXX'),
						),
				),
		);
		$json = json_encode($payload);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);

		$coupon = $this->_getResource()->postAction($request)->getData(true);

	}

	/**
	 * Test coupon update
	 */
	public function testUpdate()
	{
		$coupon = $this->_createDummyCoupon(array(
				'name' => uniqid(),
				'type' => 1,
				'amount' => rand(1, 10),
				'code' => uniqid(),
		));
		$id = $coupon->getId();

		$payload = array(
				'name' => 'test coupon update',
				'type' => 'per_item_discount',
				'amount' => floatval(5.0000),
				'code' => uniqid(),
				'applies_to' => array(
						'entity' => 'categories',
						'ids' => array(1, 2),
				),
				'min_purchase' => 5.50,
				'expires' => date('r', time()),
				'max_uses' => 16,
				'max_uses_per_customer' => 99,
				'restricted_to' => array(
						'states' => array(
								'AU' => array('New South Wales'),
						),
				),
		);
		$json = json_encode($payload);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('coupons', $id);
		$coupon = $this->_getResource()->putAction($request)->getData(true);

		$this->assertNotEmpty($coupon);

		foreach ($payload as $key => $value) {
			$this->assertTrue(array_key_exists($key, $coupon));
			if (is_scalar($value)) {
				$this->assertEquals($value, $coupon[$key]);
			} else {
				$this->assertEmpty(array_diff($value, $coupon[$key]));
			}
		}

		$couponModel = Store_Coupon::find($id)->first();
		// BIG-4030
		$this->assertTrue(
			$couponModel->isLocationRestricted(),
			"Coupon model should have 'location restricted' flag set to true"
		);
	}

	/**
	 * Test coupon update
	 */
	public function testUpdateDuplicateName()
	{
		$coupon = $this->_createDummyCoupon(array(
			'name' => 'new coupon duplicate',
			'type' => 1,
			'amount' => rand(1, 10),
			'code' => uniqid(),
		));

		$coupon = $this->_createDummyCoupon(array(
			'name' => uniqid(),
			'type' => 1,
			'amount' => rand(1, 10),
			'code' => uniqid(),
		));
		$id = $coupon->getId();

		$payload = array(
			'name' => 'new coupon duplicate',
			'type' => 'per_item_discount',
			'amount' => floatval(5.0000),
			'code' => uniqid(),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(1, 2),
			),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
		);
		$json = json_encode($payload);

		try {
			$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
			$request->setUserParam('coupons', $id);
			$this->_getResource()->putAction($request)->getData(true);
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$details = $e->getDetails();
			$this->assertEquals($payload['name'], $details['duplicate_coupon']);
		}
	}

	/**
	 * Test coupon update setting its name to its original value
	 */
	public function testUpdateDuplicateNameOnSelf()
	{

		$coupon = $this->_createDummyCoupon(array(
			'name' => 'new coupon duplicate',
			'type' => 1,
			'amount' => rand(1, 10),
			'code' => uniqid(),
		));
		$id = $coupon->getId();

		$payload = array(
			'name' => 'new coupon duplicate',
			'type' => 'per_item_discount',
			'amount' => floatval(5.0000),
			'code' => uniqid(),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(1, 2),
			),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
		);
		$json = json_encode($payload);

		try {
			$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
			$request->setUserParam('coupons', $id);
			$this->_getResource()->putAction($request)->getData(true);
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$details = $e->getDetails();
			$this->assertEquals($payload['name'], $details['duplicate_coupon']);
		}
	}

	/**
	 * Test Delete coupon
	 */
	public function testDelete()
	{
		$coupon = $this->_createDummyCoupon(array(
				'name' => uniqid(),
				'type' => 1,
				'amount' => rand(1, 10),
				'code' => uniqid(),
		));
		$id = $coupon->getId();

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('coupons', $id);
		$this->_getResource()->deleteAction($request);

		$coupons = Store_Coupon::find($id);

		$this->assertEquals(0, $coupons->count());

	}

	public function testPostXmlDecimalAmount()
	{
		$xml = new SimpleXMLElement('<coupon/>');
		$content = array(
			'name' => 'test coupon update',
			'type' => 'per_item_discount',
			'amount' => '50.00',
			'code' => uniqid(),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(array('value' => 1), array('value' => 2)),
			),
		);

		Interspire_Xml::addArrayToXML($xml, $content);
		$payload = $xml->asXML();

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Xml(new SimpleXMLIterator($payload)), 'post');
			$this->assertTrue(true);
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertTrue(false, 'This test should not throw a Store_Api_Exception_Request_InvalidField.');
		}
	}

	public function testPostXmlDecimalAmountWithInt()
	{
		$xml = new SimpleXMLElement('<coupon/>');
		$content = array(
			'name' => 'test coupon update',
			'type' => 'per_item_discount',
			'amount' => '50',
			'code' => uniqid(),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
			'applies_to' => array(
				'entity' => 'categories',
				'ids' => array(array('value' => 1), array('value' => 2)),
			),
		);

		Interspire_Xml::addArrayToXML($xml, $content);
		$payload = $xml->asXML();

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Xml(new SimpleXMLIterator($payload)), 'post');
			$this->assertTrue(true);
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertTrue(false, 'This test should not throw a Store_Api_Exception_Request_InvalidField.');
		}
	}

	public function testPostXmlDecimalAmountInvalidWithHex()
	{
		$xml = new SimpleXMLElement('<coupon/>');
		$content = array(
			'name' => 'test coupon update',
			'type' => 'per_item_discount',
			'amount' => '0x539',
			'code' => uniqid(),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
		);

		Interspire_Xml::addArrayToXML($xml, $content);
		$payload = $xml->asXML();

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Xml(new SimpleXMLIterator($payload)), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertTrue(true);
		}
	}

	public function testPostXmlDecimalAmountInvalidWithBinary()
	{
		$xml = new SimpleXMLElement('<coupon/>');
		$content = array(
			'name' => 'test coupon update',
			'type' => 'per_item_discount',
			'amount' => '0b10100111001',
			'code' => uniqid(),
			'min_purchase' => 5.50,
			'expires' => date('r', time()),
			'max_uses' => 16,
			'max_uses_per_customer' => 99,
			'restricted_to' => array(
				'states' => array(
					'AU' => array('New South Wales'),
				),
			),
		);

		Interspire_Xml::addArrayToXML($xml, $content);
		$payload = $xml->asXML();

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Xml(new SimpleXMLIterator($payload)), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertTrue(true);
		}
	}

	public function testPostValidateMissingAppliesTo()
	{
		$payload = array(
			'name' => 'test coupon invalid state',
			'type' => 'per_item_discount',
			'amount' => floatval(5.0000),
			'code' => uniqid(),
			'restricted_to' => array(
				'states' => array(
					'AU' => array('XXX'),
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($payload), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertTrue(true);
		}
	}

}
