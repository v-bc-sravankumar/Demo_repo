<?php

class Integration_Lib_Store_Api_Version_2_Resource_Orders extends PHPUnit_Framework_TestCase
{
	private $_dummyOrders = array();

	protected $_dummyProducts = array();

	private $defaultRequestUri = null;

	private $originalConfig = array();

	private static $fixtures;

	public static function setUpBeforeClass()
	{
		self::$fixtures = $fixtures = Interspire_DataFixtures::getInstance();
		$fixtures->removeData('orders', 'order_products', 'order_addresses');
	}

	public function setUp()
	{
		$this->originalConfig = array(
			'ShopPath' => Store_Config::get('ShopPath'),
			'ShopPathSSL' => Store_Config::get('ShopPathSSL'),
			'AppPath' => Store_Config::get('AppPath'),
		);

		Store_Config::override('ShopPath', 'http://bigcommerce.local/');
		Store_Config::override('ShopPathSSL', 'https://bigcommerce.local/');
		Store_Config::override('AppPath', Store_Config::get('ShopPath'));

		$this->defaultRequestUri = $_SERVER['REQUEST_URI'];
	}

	public function tearDown()
	{
		foreach ($this->_dummyOrders as $order) {
			try {
				$this->_deleteOrder($order);
			} catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
				// a test already deleted this? ignore
			}
		}

		foreach ($this->_dummyProducts as $id) {
			$this->_deleteProduct($id);
		}

		foreach ($this->originalConfig as $setting => $value) {
			Store_Config::override($setting, $value);
		}

		// restore mangling of REQUEST_URI
		$_SERVER['REQUEST_URI'] = $this->defaultRequestUri;
	}

	private function _getSubResource(Store_RequestRoute_StoreApi $route)
	{
		// Create an instance of the controller for this request
		$controller = $route->getController();

		$_SERVER['REQUEST_URI'] = Store_Config::get("ShopPathSSL") . $route->getStoreRelativeUrl();

		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
			'REQUEST_URI' => Store_Config::get("ShopPathSSL") . $route->getStoreRelativeUrl(),
		));

		foreach ($route->getParameters() as $key => $value) {
			// always put parameters store in the route into the request object as user parameters
			$request->setUserParam($key, $value);
		}

		/** @var $controller Store_Api_Resource */
		if (!class_exists($controller) ||
			($controller = new $controller()) instanceof Store_Api_Resource == false) {
			// If we can't instantiate this as an instance or subclass instance of Store_Api_Resource ...
			throw new Store_Api_Exception_Resource_ResourceNotFound();
		}

		$resp = $controller->getAction($request)->getData(true);

		return $resp;
	}

	private function _getResource ()
	{
		return new Store_Api_Version_2_Resource_Orders();
	}

	private function _createDummyOrder ($data = array())
	{
		$data = array_merge(array(
			'ordtoken' => GenerateOrderToken(),
		), $data);

		$orderId = Store::getStoreDb()->InsertQuery('orders', $data);
		$this->assertTrue(isId($orderId), "dummy order insert failed: " . store::getStoreDb()->GetErrorMsg());

		$orderId = (int)$orderId;
		$this->_dummyOrders[] = $orderId;

		return Store::getStoreDb()->FetchRow("SELECT * FROM [|PREFIX|]orders WHERE orderid = " . $orderId);
	}

	private function _getOrder ($id, $server = array())
	{
		$request = new Interspire_Request(array(), array(), array(), $server);
		$request->setUserParam('orders', $id);
		return $this->_getResource()->getAction($request)->getData(true);
	}

	private function _updateOrder ($id, $data = array(), $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$body = Interspire_Json::encode($data);

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $body);
		$request->setUserParam('orders', (int)$id);

		$resource->putAction($request);
		return $this->_getOrder($id);
	}

	private function _deleteOrder ($id, $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$id = (int)$id;
		if (!$id) {
			throw new Exception;
		}

		$request = new Interspire_Request();
		$request->setUserParam('orders', $id);
		return $resource->deleteAction($request);
	}

	private function _postOrder($payload)
	{
		$resource = $this->_getResource();

		$body = Interspire_Json::encode($payload);

		$_SERVER['REQUEST_URI'] = Store_Config::get("ShopPathSSL") . '/api/v2/orders.json';
		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
			'REQUEST_URI' => $_SERVER['REQUEST_URI'],
		), $body);
		$resp = $resource->postAction($request);

		return $resp;

	}

	private function _putOrder($payload)
	{
		$resource = $this->_getResource();

		$id = $payload['id'];
		unset($payload['id']);

		$body = Interspire_Json::encode($payload);

		$_SERVER['REQUEST_URI'] = Store_Config::get("ShopPathSSL") . '/api/v2/orders/' . $id . '.json';
		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
			'REQUEST_URI' => $_SERVER['REQUEST_URI'],
		), $body);
		$request->setUserParam('orders', $id);

		$resp = $resource->putAction($request);

		return $resp;
	}


	public function testSoftDelete ()
	{
		$order = $this->_createDummyOrder();

		$updated = $this->_updateOrder($order['orderid'], array(
			'is_deleted' => true,
		));

		$this->assertTrue($updated['is_deleted']);
	}

	public function testUndelete ()
	{
		$order = $this->_createDummyOrder(array(
			'deleted' => 1,
		));

		$updated = $this->_updateOrder($order['orderid'], array(
			'is_deleted' => false,
		));

		$this->assertFalse($updated['is_deleted']);
	}

	public function testHardDelete ()
	{
		$order = $this->_createDummyOrder();
		$this->_deleteOrder($order['orderid']);
		$this->assertEmpty($this->_getOrder($order['orderid']));
	}

	/**
	 * Dummy address to be used by orders
	 *
	 * @return array
	 */
	private function _getDummyAddress()
	{
		return array (
			'first_name' => 'Test',
			'last_name' => 'Engineering',
			'company' => 'BigCommerce',
			'street_1' => 'Level 6, 1-3 Smail St',
			'street_2' => '',
			'city' => 'Ultimo',
			'state' => 'New South Wales',
			'zip' => '2007',
			'country' => 'Australia',
			'country_iso2' => 'AU',
			'phone' => '90066009',
			'email' => 'test@bigcommerce.com',
		);
	}


	/**
	 * Create a random product
	 *
	 * @param array $data
	 * @return array
	 */
	private function _createDummyProduct ($data = array())
	{
		$data = array_merge(array(
			'prodname' => 'PRODUCT_' . mt_rand(1, PHP_INT_MAX),
			'prodcatids' => '',
			'proddateadded' => time(),
			'prodlastmodified' => time(),
		), $data);

		$productId = Store::getStoreDb()->InsertQuery('products', $data);
		$this->assertTrue(isId($productId), "dummy product insert failed: " . Store::getStoreDb()->GetErrorMsg());

		$productId = (int)$productId;
		$this->_dummyProducts[] = $productId;

		return \Store\Product::find($productId, false);
	}

	private function _deleteProduct($id)
	{
		$id = (int)$id;
		if (!$id) {
			throw new Exception;
		}

		$key = array_search($id, $this->_dummyProducts);
		if ($key !== false) {
			unset($this->_dummyProducts[$key]);
		}

		$request = new Interspire_Request();
		$request->setUserParam('products', $id);
		$resource = new \Store_Api_Version_2_Resource_Products;
		return $resource->deleteAction($request);
	}

	/**
	 * @link https://jira.bigcommerce.com/browse/BIG-5764
	 */
	public function testEmailNotificationUponPutStatusIdChange()
	{
		if (Store_Feature::isEnabled('Resque')) {
			$this->markTestSkipped('This test only runs when the internal taskmanager is being used');
		}

		$email = 'test@example.com';
		$customer = new Store_Customer();
		$customer->setEmail($email);
		$customer->setFirstName('John');
		$customer->setLastName('Doe');
		$customer->save();

		$order = $this->_createDummyOrder(array('ordcustid' => $customer->getId()));
		$orderId = $order['orderid'];

		// Remove the initial email notification task created upon order creation
		self::$fixtures->db->DeleteQuery('tasks', "WHERE queue = 'email'");

		// Get the list of statuses that should trigger a notification
		$orderStatusNotifications = explode(',', Store_Config::get('OrderStatusNotifications'));
		$this->assertNotEmpty($orderStatusNotifications);

		$this->_putOrder(array('id' => $orderId, "status_id" => array_shift($orderStatusNotifications)));

		// Fetch the "order status change" email from the DB
		$resultJson = self::$fixtures->db->FetchOne("SELECT data FROM [|PREFIX|]tasks WHERE queue = 'email'");
		$result = json_decode($resultJson, true);

		$emailObj = new \Email\Api($result['email']);
		$emailRecipients = $emailObj->getRecipients();

		$this->assertEquals($email, $emailRecipients[0]['address']);

		// cleanup
		$this->_deleteOrder($orderId);
		$customer->delete();
		self::$fixtures->db->DeleteQuery('tasks', "WHERE queue = 'email'");
	}

	/**
	 * Check that the status field is read-only on put
	 */
	public function testPutStatusFieldReadOnly()
	{

		$input = array(
			'status' => 'Completed',
		);

		$this->setExpectedException('Store_Api_Exception_Request_FieldNotWritable');
		$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'put');

	}

	/**
	 * Test a simple post
	 */
	public function testPostSimple()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);

	}

	/**
	 * Test post with invalid customer
	 */
	public function testPostInvalidCustomer()
	{
		$input = array(
			'customer_id' => 9999,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This test should be throwing Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('customer_id', $e->getField());
		}

	}

	/**
	 * Test post and check that order source is external
	 */
	public function testPostOrderSource()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals('external', $data['order_source']);
	}

	/**
	 * Test post and set the external source
	 */
	public function testPostExternalSource()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals('POS', $data['external_source']);
	}

	/**
	 * Test post with multiple products
	 */
	public function testPostMultipleProducts()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$products = $this->_getSubResource($data['products']);

		$productIds = array_map(function($product) {
			return $product['product_id'];
		}, $products);

		$this->assertContains(27, $productIds);
		$this->assertContains(25, $productIds);

	}

	/**
	 * Test post purchase a product when it has inventory tracking on
	 */
	public function testPostProductQuantityOnInventoryTracking()
	{
		$db = Store::getStoreDb();

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 2,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(2, (int) $product['inventory_level']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 1,
				),
			),
		);
		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 25');

		$this->assertNotEmpty($data['id']);

		$productAfter = \Store\Product::find(25, false);
		$this->assertEquals(1, (int) $productAfter['inventory_level']);

	}

	/**
	 * Test post a product when it has inventory tracking on with negative quantity
	 */
	public function testPostProductWithNegativeQuantityOnInventoryTracking()
	{
		$db = Store::getStoreDb();

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 2,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(2, (int) $product['inventory_level']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => -1,
				),
			),
		);
		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 25');

		$this->assertNotEmpty($data['id']);

		$productAfter = \Store\Product::find(25, false);
		$this->assertEquals(3, (int) $productAfter['inventory_level']);

	}

	/**
	 * Test purchasing a product with options
	 */
	public function testPostProductWithOptions()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 19,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
					),
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$products = $this->_getSubResource($data['products']);

		$this->assertEquals(1, count($products));

		$product = $products[0];

		$this->assertEquals(6, $product['product_id']);

		$productOptions = array_map(function($orderOptions) {
			return array('id' => $orderOptions['product_option_id'], 'value' => $orderOptions['value']);
		},$product['product_options']->getData());

		$this->assertEquals($input['products'][0]['product_options'], $productOptions);

	}

	/**
	 * Test post an order with invalid options
	 */
	public function testPostProductWithInvalidOptions()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 19,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
						array (
							'id' => 29,
							'value' => 19,
						),
					),
				),
			),
		);
		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_ProductOptionErrors $e) {
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_ProductOptionErrors::TYPE_INVALID_PRODUCT_OPTION, $error['type']);
		}
	}

	/**
	 * Test post an order with products having an invalid option value
	 */
	public function testPostProductWithInvalidOptionValue()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 23,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
					),
				),
			),
		);
		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_ProductOptionErrors $e) {
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_ProductOptionErrors::TYPE_INVALID_PRODUCT_OPTION_VALUE, $error['type']);
		}
	}

	/**
	 * Test post a payload with options when the product does not have options attached to it
	 */
	public function testPostProductWithoutOptionsGivenOptions()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 25,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 29,
							'value' => 19,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
					),
				),
			),
		);
		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_ProductOptionErrors $e) {
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_ProductOptionErrors::TYPE_PRODUCT_OPTIONS_NOT_SUPPORTED, $error['type']);
		}
	}

	/**
	 * Test post failure when a product has mandatory options and no options are supplied on the payload
	 */
	public function testPostProductWithMandatoryOptionFail()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
				),
			),
		);
		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_ProductOptionErrors $e) {
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_ProductOptionErrors::TYPE_MANDATORY_PRODUCT_OPTIONS, $error['type']);
		}
	}

	/**
	 * Test purchasing when a product is set to have inventory tracking on SKU
	 */
	public function testPostProductWithQuantityOnSKUInventoryTracking()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 2,
		), 'productid = 5');

		$sku = 'MB-1';
		/* @var $combination Store_Product_Attribute_Combination */
		$combination = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$combination->setStockLevel(2);
		$combination->save();

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 5,
					'quantity' => 1,
					'product_options' => array (
						array (
							'id' => 15,
							'value' => 17,
						),
						array (
							'id' => 16,
							'value' => 28,
						),
					),
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 5');

		/* @var $combinationAfter Store_Product_Attribute_Combination */
		$combinationAfter = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$this->assertEquals(1, $combinationAfter->getStockLevel());

	}

	/**
	 * Test post on a product with quantity out of stock
	 */
	public function testPostProductWithQuantityOutOfStock()
	{

		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 1,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(1, (int) $product['inventory_level']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 2,
				),
			),
		);

		try {

			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_InvalidQuantity $e) {
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = 25');
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_OUT_OF_STOCK, $error['type']);

		}

	}

	/**
	 * Test post product when the sku inventory is out of stock
	 */
	public function testPostProductWithSkuWithQuantityOutOfStock()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 2,
		), 'productid = 5');

		$sku = 'MB-1';
		/* @var $combination Store_Product_Attribute_Combination */
		$combination = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$combination->setStockLevel(2);
		$combination->save();

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 5,
					'quantity' => 3,
					'product_options' => array (
						array (
							'id' => 15,
							'value' => 17,
						),
						array (
							'id' => 16,
							'value' => 28,
						),
					),
				),
			),
		);

		try {
			$res = $this->_postOrder($input);
			$res->getData(true);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch(Store_Api_Exception_Request_Orders_InvalidQuantity $e) {
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = 5');
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_OUT_OF_STOCK, $error['type']);

		}

	}

	/**
	 * Test post with product having minimum purchase quantity, purchase on its minimum quantity
	 */
	public function testPostProductWithMinimumPurchaseQuantity()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodminqty' => 3,
		), 'productid = 25');

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$products = $this->_getSubResource($data['products']);
		$this->assertNotEmpty($products[0]);
		$this->assertEquals(3, $products[0]['quantity']);

		$db->UpdateQuery('products', array(
			'prodminqty' => 0,
		), 'productid = 25');
	}

	/**
	 * Test post product when the minimum purchase quantity requirement is not met
	 */
	public function testPostProductWithMinimumPurchaseQuantityFailure()
	{

		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 10,
			'prodminqty' => 3,
			'prodmaxqty' => 0,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(10, (int) $product['inventory_level']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 2,
				),
			),
		);

		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_InvalidQuantity $e) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
				'prodminqty' => 0,
			), 'productid = 25');

			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_INSUFFICIENT_QUANTITY, $error['type']);

		}
	}

	/**
	 * Test product with maximum purchase quantity, purchase on its max quantity
	 */
	public function testPostProductWithMaximumPurchaseQuantity()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodminqty' => 0,
			'prodmaxqty' => 3,
		), 'productid = 25');

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$products = $this->_getSubResource($data['products']);
		$this->assertNotEmpty($products[0]);
		$this->assertEquals(3, $products[0]['quantity']);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
			'prodmaxqty' => 0,
		), 'productid = 25');

	}

	/**
	 * Test post product with maximum purchase quantity failure
	 */
	public function testPostProductWithMaximumPurchaseQuantityFailure()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 10,
			'prodminqty' => 0,
			'prodmaxqty' => 3,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(10, (int) $product['inventory_level']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 5,
				),
			),
		);

		try {
			$this->_postOrder($input);
			$this->assertTrue(false, 'This test supposed to throw an exception.');
		} catch (Store_Api_Exception_Request_Orders_InvalidQuantity $e) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
				'prodmaxqty' => 0,
			), 'productid = 25');

			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_EXCEEDS_MAX_QUANTITY, $error['type']);

		}
	}

	/**
	 * Test post with missing billing address
	 */
	public function testPostMissingBillingAddress()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_RequiredFieldNotSupplied.');
		} catch (Store_Api_Exception_Request_RequiredFieldNotSupplied $e) {
			$this->assertEquals('billing_address', $e->getField());
		}

	}

	/**
	 * Test use the billing address for shipping address
	 */
	public function testPostUseBillingDetailsForShippingAddress()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$shippingAddress = $shippingAddresses[0];

		$diff = array_diff_assoc($input['billing_address'], $shippingAddress);
		$this->assertEmpty($diff);

	}

	/**
	 * Test the shipping address
	 */
	public function testPostShippingAddress()
	{
		$shippingAddressInput = $this->_getDummyAddress();
		$shippingAddressInput['street_1'] = 'Test Avenue';
		$shippingAddressInput['street_2'] = 'End of the rainbow';

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
			'shipping_addresses' => array($shippingAddressInput),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$shippingAddress = $shippingAddresses[0];

		$diff = array_diff_assoc($shippingAddressInput, $shippingAddress);
		$this->assertEmpty($diff);

	}

	/**
	 * Test use the billing address for shipping address will missing country information
	 */
	public function testPostUseBillingDetailsForShippingAddressWithMissingCountry()
	{
		$address = $this->_getDummyAddress();

		unset($address['country']);
		unset($address['country_iso2']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $address,
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This should throw a Store_Api_Exception_Request_InvalidField for billing_address.country');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('billing_address.country', $e->getField());
		}
	}

	/**
	 * Test use the billing address for shipping address will invalid country info
	 */
	public function testPostUseBillingDetailsForShippingAddressWithMissingCountryInvalidCountry()
	{
		$address = $this->_getDummyAddress();

		unset($address['country_iso2']);
		$address['country'] = 'NotRight';

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $address,
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This should throw a Store_Api_Exception_Request_InvalidField for billing_address.country');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('country', $e->getField());
		}
	}

	/**
	 * Test use the billing address for shipping address with missing country iso info
	 */
	public function testPostUseBillingDetailsForShippingAddressWithMissingCountryIso()
	{
		$address = $this->_getDummyAddress();

		unset($address['country_iso2']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $address,
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$shippingAddress = $shippingAddresses[0];

		$diff = array_diff_assoc($this->_getDummyAddress(), $shippingAddress);
		$this->assertEmpty($diff);
	}

	/**
	 * Test use the billing address for shipping address with some unknown state should still work
	 */
	public function testPostUseBillingDetailsForShippingAddressWithUnknownState()
	{
		$address = $this->_getDummyAddress();

		$address['state'] = "Some New State";

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $address,
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$shippingAddress = $shippingAddresses[0];

		$diff = array_diff_assoc($address, $shippingAddress);
		$this->assertEmpty($diff);
	}


	/**
	 * Test post order totals for simple order
	 */
	public function testPostTotalsForSimpleOrder()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		$product27 = \Store\Product::find(27, false);
		$product25 = \Store\Product::find(25, false);

		$expectedTotal = $product25['price']*3 + $product27['price']*2;
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($expectedTotal, $data['total_inc_tax']);
		$this->assertEquals($expectedTotal, $data['total_ex_tax']);

	}

	/**
	 * Test post order totals for simple order with sale products
	 */
	public function testPostTotalsForSimpleOrderWithSaleProducts()
	{
		$gateway = new Store_Product_Gateway();
		$product25Sale = $gateway->get(25);
		$product25Sale['prodretailprice'] = $product25Sale['prodprice'];
		$product25Sale['prodsaleprice'] = 1.99;
		$gateway->edit($product25Sale);

		$product25 = \Store\Product::find(25, false);
		$this->assertEquals(1.99, $product25['calculated_price']);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		$product27 = \Store\Product::find(27, false);
		$product25 = \Store\Product::find(25, false);

		$expectedTotal = $product25['sale_price']*3 + $product27['price']*2;
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($expectedTotal, $data['total_inc_tax']);
		$this->assertEquals($expectedTotal, $data['total_ex_tax']);

		// restore everything
		$product25Sale = $gateway->get(25);
		$product25Sale['prodretailprice'] = 0;
		$product25Sale['prodsaleprice'] = 0;
		$gateway->edit($product25Sale);

	}

	/**
	 * Test post with supplied tax prices
	 */
	public function testPostWithSuppliedTaxPrices()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
					'price_inc_tax' => 10.45,
					'price_ex_tax' => 8.21,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
					'price_inc_tax' => 100.50,
					'price_ex_tax' => 84.450,
				),
			),
		);

		$expectedIncTaxTotal = array_reduce($input['products'], function($result, $product) {
			return $result + ($product['price_inc_tax'] * $product['quantity']);
		}, 0);

		$expectedExTaxTotal = array_reduce($input['products'], function($result, $product) {
			return $result + ($product['price_ex_tax'] * $product['quantity']);
		}, 0);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);

		// total inc and ex tax are always the inc_tax value
		$this->assertEquals($expectedIncTaxTotal, $data['total_inc_tax']);
		$this->assertEquals($expectedExTaxTotal, $data['total_ex_tax']);

	}

	/**
	 * Test post with negative price ex tax prices
	 */
	public function testPostWithNegativeExTaxPrice()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
					'price_ex_tax' => -10,
					'price_inc_tax' => 20,
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('price_ex_tax', $e->getField());
		}

	}

	/**
	 * Test post with negative price inc tax prices
	 */
	public function testPostWithNegativeIncTaxPrice()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
					'price_ex_tax' => 10,
					'price_inc_tax' => -20,
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('price_inc_tax', $e->getField());
		}

	}

	/**
	 * Test post with negative (decimal) price ex tax prices
	 */
	public function testPostWithDecimalNegativeExTaxPrice()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
					'price_ex_tax' => -10.01,
					'price_inc_tax' => 20,
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('price_ex_tax', $e->getField());
		}

	}

	/**
	 * Test post with negative (decimal) price inc tax prices
	 */
	public function testPostWithDecimalNegativeIncTaxPrice()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
					'price_ex_tax' => 10,
					'price_inc_tax' => -20.002,
				),
			),
		);

		try {
			$this->_getResource()->validateInput(new Store_Api_Input_Json($input), 'post');
			$this->assertTrue(false, 'This test should throw a Store_Api_Exception_Request_InvalidField.');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('price_inc_tax', $e->getField());
		}

	}

	/**
	 * Test post totals for product with product option rules
	 */
	public function testPostTotalsForProductOptionRules()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 19,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
					),
				),
			),
		);

		$rules = Store_Product_Attribute_Rule::findRulesForProductValues(
			6,
			array(
				22 => 19,
				23 => 27,
				24 => 30,
			)
		);

		$product = \Store\Product::find(6, false);
		$price = $product['price'];
		/* @var $rule Store_Product_Type_Rule */
		foreach ($rules as $rule) {
			$priceAdjuster = $rule->getPriceAdjuster();
			if ($priceAdjuster) {
				$price = $priceAdjuster->adjustValue($price);
			}
		}
		$price *= $input['products']['0']['quantity'];

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($price, $data['total_inc_tax']);
		$this->assertEquals($price, $data['total_ex_tax']);

	}

	/**
	 * Test post with supplied subtotal
	 */
	public function testPostWithSuppliedSubtotal()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'subtotal_ex_tax' => 49.99,
			'subtotal_inc_tax' => 55.75,
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);

		// total inc and ex tax are always the inc_tax value
		$this->assertEquals($input['subtotal_inc_tax'], $data['total_inc_tax']);
		$this->assertEquals($input['subtotal_ex_tax'], $data['total_ex_tax']);
		$this->assertEquals($input['subtotal_inc_tax'] - $input['subtotal_ex_tax'], $data['subtotal_tax']);

	}

	/**
	 * Test post with supplied subtotal with missing inc tax
	 */
	public function testPostWithSuppliedSubtotalMissingIncTax()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'subtotal_ex_tax' => 49.99,
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This test should produce Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('subtotal_inc_tax', $e->getField());
		}
	}

	/**
	 * Test post with supplied subtotal with missing ex tax
	 */
	public function testPostWithSuppliedSubtotalMissingExTax()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'subtotal_inc_tax' => 49.99,
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This test should produce Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('subtotal_ex_tax', $e->getField());
		}
	}

	/**
	 * Test post with supplied subtotal with shipping handling wrapping
	 */
	public function testPostWithSuppliedSubtotalWithShippingHandlingWrapping()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'shipping_cost_ex_tax' => 1,
			'shipping_cost_inc_tax' => 3,
			'handling_cost_ex_tax' => 5,
			'handling_cost_inc_tax' => 7,
			'wrapping_cost_ex_tax' => 11,
			'wrapping_cost_inc_tax' => 13,
			'subtotal_ex_tax' => 49.99,
			'subtotal_inc_tax' => 55.75,
		);

		$res = $this->_postOrder($input);

		$expectedTotalExTax = $input['subtotal_ex_tax'] + $input['shipping_cost_ex_tax'] + $input['handling_cost_ex_tax'] + $input['wrapping_cost_ex_tax'];
		$expectedTotalIncTax = $input['subtotal_inc_tax'] + $input['shipping_cost_inc_tax'] + $input['handling_cost_inc_tax'] + $input['wrapping_cost_inc_tax'];

		$data = $res->getData(true);

		// total inc and ex tax are always the inc_tax value
		$this->assertEquals($expectedTotalIncTax, $data['total_inc_tax']);
		$this->assertEquals($expectedTotalExTax, $data['total_ex_tax']);

	}

	/**
	 * Test post with supplied total
	 */
	public function testPostWithSuppliedTotal()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'shipping_cost_ex_tax' => 1,
			'shipping_cost_inc_tax' => 3,
			'handling_cost_ex_tax' => 5,
			'handling_cost_inc_tax' => 7,
			'wrapping_cost_ex_tax' => 11,
			'wrapping_cost_inc_tax' => 13,
			'subtotal_ex_tax' => 49.99,
			'subtotal_inc_tax' => 55.75,
			'total_ex_tax' => 90,
			'total_inc_tax' => 100,
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($input['total_inc_tax'], $data['total_inc_tax']);
		$this->assertEquals($input['total_ex_tax'], $data['total_ex_tax']);
		$this->assertEquals($input['total_inc_tax'] - $input['total_ex_tax'], $data['total_tax']);

	}

	/**
	 * Test post with supplied total with missing inc tax
	 */
	public function testPostWithSuppliedTotalMissingIncTax()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'total_ex_tax' => 49.99,
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This test should produce Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('total_inc_tax', $e->getField());
		}
	}

	/**
	 * Test post with supplied total with missing ex tax
	 */
	public function testPostWithSuppliedTotalMissingExTax()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
			'total_inc_tax' => 49.99,
		);

		try {
			$res = $this->_postOrder($input);
			$this->assertTrue(false, 'This test should produce Store_Api_Exception_Request_InvalidField exception.');
		} catch(Store_Api_Exception_Request_InvalidField $e) {
			$this->assertEquals('total_ex_tax', $e->getField());
		}
	}

	/**
	 * Test post with supplied discount on multiple line items
	 */
	public function testPostWithSuppliedDiscountTaxInclusiveDisplayMultipleProducts()
	{

		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_INCLUSIVE);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'discount_amount' => 100,
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
					'price_ex_tax' => 30,
					'price_inc_tax' => 30,
				),
				array(
					'product_id' => 25,
					'quantity' => 1,
					'price_ex_tax' => 80,
					'price_inc_tax' => 85,
				),
			),
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($data['subtotal_inc_tax']-$input['discount_amount'], $data['total_inc_tax']);
		$this->assertEquals($data['subtotal_inc_tax']-$input['discount_amount'], $data['total_ex_tax']);

		$productsData = $this->_getSubResource($data['products']);

		$totalLineDiscount = 0;
		foreach ($productsData as $productData) {
			$appliedDiscounts = $productData['applied_discounts']->getData(true);
			$this->assertNotEmpty($appliedDiscounts);

			$appliedDiscount = $appliedDiscounts[0];

			$this->assertEquals('manual-discount', $appliedDiscount['id']);
			$totalLineDiscount += $appliedDiscount['amount'];

		}

		$this->assertEquals(100, $totalLineDiscount);

		Store_Config::override('taxDefaultTaxDisplayCart', Store_Config::getOriginal('taxDefaultTaxDisplayCart'));

	}

	/**
	 * Test post with supplied discount
	 */
	public function testPostWithSuppliedDiscountTaxInclusiveDisplay()
	{

		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_INCLUSIVE);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'discount_amount' => 10,
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
					'price_ex_tax' => 100,
					'price_inc_tax' => 110,
				),
			),
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($data['subtotal_inc_tax']-$input['discount_amount'], $data['total_inc_tax']);
		$this->assertEquals($data['subtotal_inc_tax']-$input['discount_amount'], $data['total_ex_tax']);

		$productsData = $this->_getSubResource($data['products']);
		$productData = $productsData[0];

		$this->assertNotEmpty($productData['applied_discounts']);
		$this->assertEquals(1, count($productData['applied_discounts']));

		$appliedDiscounts = $productData['applied_discounts']->getData(true);
		$appliedDiscount = $appliedDiscounts[0];

		$this->assertEquals('manual-discount', $appliedDiscount['id']);
		$this->assertEquals(10, $appliedDiscount['amount']);

		Store_Config::override('taxDefaultTaxDisplayCart', Store_Config::getOriginal('taxDefaultTaxDisplayCart'));

	}

	/**
	 * Test post with supplied discount
	 */
	public function testPostWithSuppliedDiscountTaxExclusiveDisplay()
	{

		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_EXCLUSIVE);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'discount_amount' => 10,
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
					'price_ex_tax' => 100,
					'price_inc_tax' => 110,
				),
			),
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($data['subtotal_ex_tax']-$input['discount_amount'], $data['total_inc_tax']);
		$this->assertEquals($data['subtotal_ex_tax']-$input['discount_amount'], $data['total_ex_tax']);

		$productsData = $this->_getSubResource($data['products']);
		$productData = $productsData[0];

		$this->assertNotEmpty($productData['applied_discounts']);
		$this->assertEquals(1, count($productData['applied_discounts']));

		$appliedDiscounts = $productData['applied_discounts']->getData(true);
		$appliedDiscount = $appliedDiscounts[0];

		$this->assertEquals('manual-discount', $appliedDiscount['id']);
		$this->assertEquals(10, $appliedDiscount['amount']);

		Store_Config::override('taxDefaultTaxDisplayCart', Store_Config::getOriginal('taxDefaultTaxDisplayCart'));

	}

	/**
	 * Test post with supplied discount
	 */
	public function testPostWithSuppliedDiscountTaxExclusiveDisplayWithTotalOverride()
	{

		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_EXCLUSIVE);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'discount_amount' => 10,
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
					'price_ex_tax' => 100,
					'price_inc_tax' => 110,
				),
			),
			'total_ex_tax' => 100,
			'total_inc_tax' => 100,
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals($input['discount_amount'], $data['discount_amount']);
		$this->assertEquals($input['total_ex_tax'], $data['total_ex_tax']);
		$this->assertEquals($input['total_inc_tax'], $data['total_inc_tax']);
		$this->assertEquals($input['total_inc_tax'] - $input['total_ex_tax'], $data['total_tax']);

		$productsData = $this->_getSubResource($data['products']);
		$productData = $productsData[0];

		$this->assertNotEmpty($productData['applied_discounts']);
		$this->assertEquals(1, count($productData['applied_discounts']));

		$appliedDiscounts = $productData['applied_discounts']->getData(true);
		$appliedDiscount = $appliedDiscounts[0];

		$this->assertEquals('manual-discount', $appliedDiscount['id']);
		$this->assertEquals(10, $appliedDiscount['amount']);

		Store_Config::override('taxDefaultTaxDisplayCart', Store_Config::getOriginal('taxDefaultTaxDisplayCart'));

	}

	/**
	 * Test post with supplied status ID
	 */
	public function testPostWithStatusId()
	{

		Store_Config::override('taxDefaultTaxDisplayCart', TAX_PRICES_DISPLAY_EXCLUSIVE);

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'discount_amount' => 10,
			'status_id' => 1,
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
				),
			),
		);

		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertEquals(1, $data['status_id']);
		$this->assertEquals(GetOrderStatusById(1), $data['status']);

	}

	/**
	 * Missing date should produce todays date in the return payload
	 */
	public function testPostWithMissingDateCreated()
	{
		$input = array(
			'customer_id' => 0,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$this->assertTrue(strpos($data['date_created'], date('D, d M Y H:')) === 0);

	}

	/**
	 * Test post with custom payment method
	 */
	public function testPostWithPaymentMethod()
	{
		$input = array(
			'customer_id' => 0,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
			'payment_method' => 'cash',
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$this->assertEquals($input['payment_method'], $data['payment_method']);

	}

	/**
	 * Test post with missing payment method. Should default to manual
	 */
	public function testPostWithMissingPaymentMethod()
	{
		$input = array(
			'customer_id' => 0,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$this->assertEquals(\Orders\Order::PAYMENT_METHOD_MANUAL, $data['payment_method']);

	}

	/**
	 * Test a simple post with varying status ID
	 */
	public function testPostStatusId()
	{

		$statuses = array(
			\Orders\Order::STATUS_AWAITING_FULFILLMENT,
			\Orders\Order::STATUS_INCOMPLETE,
			\Orders\Order::STATUS_PENDING,
			\Orders\Order::STATUS_SHIPPED,
			\Orders\Order::STATUS_PARTIALLY_SHIPPED,
			\Orders\Order::STATUS_REFUNDED,
			\Orders\Order::STATUS_CANCELLED,
			\Orders\Order::STATUS_DECLINED,
			\Orders\Order::STATUS_AWAITING_PAYMENT,
			\Orders\Order::STATUS_AWAITING_PICKUP,
			\Orders\Order::STATUS_AWAITING_SHIPMENT,
			\Orders\Order::STATUS_COMPLETED,
			\Orders\Order::STATUS_AWAITING_FULFILLMENT,
			\Orders\Order::STATUS_HELD_REVIEW,
		);

		foreach ($statuses as $statusId) {
			$input = array(
				'customer_id' => 0,
				'status_id' => $statusId,
				'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
				'billing_address' => $this->_getDummyAddress(),
				'products' => array (
					array (
						'product_id' => 27,
						'quantity' => 2,
					),
				),
			);
			$res = $this->_postOrder($input);

			$data = $res->getData(true);
			$this->assertNotEmpty($data['id']);
			$this->assertEquals($statusId, $data['status_id'], "Failed to set status to [".GetOrderStatusById($statusId)."]");
			$this->assertEquals(GetOrderStatusById($statusId), $data['status']);
		}

	}

	public function testPostSetsCurrencyFields()
	{
		$input = array(
			'customer_id' => 0,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);

		$defaultCurrency = Store_Currency::getDefault();
		$currencyId = $defaultCurrency['currencyid'];
		$currencyCode = $defaultCurrency['currencycode'];
		$currencyExchangeRate = $defaultCurrency['currencyexchangerate'];

		$this->assertNotEmpty($data['id']);

		$this->assertEquals($currencyId, $data['currency_id']);
		$this->assertEquals($currencyCode, $data['currency_code']);
		$this->assertEquals($currencyExchangeRate, $data['currency_exchange_rate']);

		$this->assertEquals($currencyId, $data['default_currency_id']);
		$this->assertEquals($currencyCode, $data['default_currency_code']);
	}

	/**
	 * Do a post to an entity
	 */
	public function testPostToEntity()
	{
		$order = $this->_createDummyOrder();

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
		), Interspire_Json::encode($input));
		$request->setUserParam('orders', $order['orderid']);

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$this->_getResource()->postAction($request);

	}

	/**
	 * Test Digital order has no shipping addresses
	 */
	public function testPostDigitalOrderHasNoShippingAddresses()
	{
		$product = $this->_createDummyProduct(array(
			'prodtype' => 2,
		));

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => $product['id'],
					'quantity' => 2,
				),
			),
			'order_is_digital' => true,
		);

		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
		), Interspire_Json::encode($input));

		$data = $this->_getResource()->postAction($request)->getData(true);

		$this->assertNotEmpty($data['id']);

		$addresses = $this->_getSubResource($data['shipping_addresses']);

		$this->assertEmpty($addresses);

	}

	/**
	 * Test error when creating a digital order and product is phyical
	 */
	public function testPostDigitalOrderWithPhysicalProductInvalid()
	{

		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
			'order_is_digital' => true,
		);

		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
		), Interspire_Json::encode($input));

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$this->_getResource()->postAction($request)->getData(true);

	}

	public function testPostWithEbayOrderId()
	{
		$input = array(
			'customer_id' => 0,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
			'ebay_order_id' => '12345-6789',
		);
		$res = $this->_postOrder($input);

		$data = $res->getData(true);
		$this->assertNotEmpty($data['id']);
		$this->assertEquals($input['ebay_order_id'], $data['ebay_order_id']);
	}

	/**
	 * Simple puts
	 */
	public function testPutSimple()
	{

		$order = $this->_createDummyOrder();

		$input = array(
			'id' => $order['orderid'],
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'the_cloud',
		);

		$data = $this->_putOrder($input)->getData(true);

		foreach ($input as $field => $value) {

			if ($field == 'billing_address') {
				foreach ($value as $billingField => $billingFieldValue) {
					$this->assertEquals($billingFieldValue, $data['billing_address'][$billingField]);
				}
			} else {
				$this->assertEquals($value, $data[$field]);
			}
		}

	}

	/**
	 * Changing an order product to a different product
	 */
	public function testPutChangeProduct()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'product_id' => 25,
				),
			),
		);
		$res = $this->_putOrder($input)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		// check that the product ID has changed
		$this->assertEquals(25, $product['product_id']);

		// Check that the totals has changed
		$this->assertNotEquals($order['total_ex_tax'], $res['total_ex_tax']);
		$this->assertNotEquals($order['total_inc_tax'], $res['total_inc_tax']);

	}

	/**
	 * Test add a new product via PUT
	 */
	public function testPutAddProduct()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'product_id' => 25,
					'quantity' => 1,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$productIds = array_map(function($product) {
			return $product['product_id'];
		}, $products);

		$this->assertContains(27, $productIds);
		$this->assertContains(25, $productIds);

		// Check that the totals has changed
		$this->assertNotEquals($order['total_ex_tax'], $res['total_ex_tax']);
		$this->assertNotEquals($order['total_inc_tax'], $res['total_inc_tax']);
	}

	/**
	 * Test change the product price and recalculate totals
	 */
	public function testPutChangeProductPrices()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$productPriceExTax = 4321;
		$productPriceIncTax = $productPriceExTax * 1.1;

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'price_inc_tax' => $productPriceIncTax,
					'price_ex_tax' => $productPriceExTax,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);

		// Check that the totals has changed
		$this->assertNotEquals($productPriceExTax*2, $res['total_ex_tax']);
		$this->assertNotEquals($productPriceIncTax*2, $res['total_inc_tax']);
	}

	/**
	 * Change the product prices but override the subtotal
	 */
	public function testPutChangeProductPricesOverrideSubtotal()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$productPriceExTax = 4321;
		$productPriceIncTax = $productPriceExTax * 1.1;

		$subtotalExTax = 100;
		$subtotalIncTax = 110;

		$input = array(
			'id' => $order['id'],
			'subtotal_inc_tax' => $subtotalIncTax,
			'subtotal_ex_tax' => $subtotalExTax,
			'products' => array(
				array(
					'id' => $product['id'],
					'price_inc_tax' => $productPriceIncTax,
					'price_ex_tax' => $productPriceExTax,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);

		// Check that the totals has changed
		$this->assertNotEquals($subtotalExTax, $res['total_ex_tax']);
		$this->assertNotEquals($subtotalIncTax, $res['total_inc_tax']);
	}

	/**
	 * Change the product prices but override the subtotal and total
	 */
	public function testPutChangeProductPricesOverrideSubtotalAndTotal()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$productPriceExTax = 4321;
		$productPriceIncTax = $productPriceExTax * 1.1;

		$subtotalExTax = 100;
		$subtotalIncTax = 1.1 * $subtotalExTax;
		$totalExTax = 120;
		$totalIncTax = 1.1 * $totalExTax;

		$input = array(
			'id' => $order['id'],
			'subtotal_inc_tax' => $subtotalIncTax,
			'subtotal_ex_tax' => $subtotalExTax,
			'total_ex_tax' => $totalExTax,
			'total_inc_tax' => $totalIncTax,
			'products' => array(
				array(
					'id' => $product['id'],
					'price_inc_tax' => $productPriceIncTax,
					'price_ex_tax' => $productPriceExTax,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);

		// Check that the subtotals has changed
		$this->assertEquals($subtotalExTax, $res['subtotal_ex_tax']);
		$this->assertEquals($subtotalIncTax, $res['subtotal_inc_tax']);

		$this->assertEquals($totalExTax, $res['total_ex_tax']);
		$this->assertEquals($totalIncTax, $res['total_inc_tax']);
	}

	/**
	 * Change the product quantity
	 */
	public function testPutChangeProductQuantity()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'quantity' => 5,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$this->assertEquals(5, $product['quantity']);

		// Check that the totals has changed
		$this->assertEquals((5/2)*$order['total_ex_tax'], $res['total_ex_tax']);
		$this->assertEquals((5/2)*$order['total_inc_tax'], $res['total_inc_tax']);
	}

	/**
	 * Test changing the product quantity with the item inventory tracked
	 */
	public function testPutChangeProductQuantityInventoryTracked()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 5,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'quantity' => 5,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);
		$products = $this->_getSubResource($res['products']);
		$product = current($products);

		$this->assertEquals(5, $product['quantity']);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 25');

	}

	/**
	 * Test put change product when the quantity is out of stock
	 */
	public function testPutChangeProductQuantityOutOfStock()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 5,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array(
					'product_id' => 25,
					'quantity' => 3,
				),
			),
		);

		try {

			$order = $this->_postOrder($orderInput)->getData(true);
			$products = $this->_getSubResource($order['products']);
			$product = current($products);

			$input = array(
				'id' => $order['id'],
				'products' => array(
					array(
						'id' => $product['id'],
						'quantity' => 6,
					),
				),
			);

			$res = $this->_putOrder($input)->getData(true);
			$this->assertTrue(false, 'This test supposed to throw an exception.');


		} catch (Store_Api_Exception_Request_Orders_InvalidQuantity $e) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = 25');
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_OUT_OF_STOCK, $error['type']);

		}
	}

	/**
	 * Test change the option value
	 */
	public function testPutChangeOptionsValues()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 6,
					'quantity' => 2,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 19,
						),
						array (
							'id' => 23,
							'value' => 27,
						),
						array (
							'id' => 24,
							'value' => 30,
						),
					),
				),
			),
		);

		$order = $this->_postOrder($orderInput)->getData(true);
		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'quantity' => 6,
					'product_options' => array (
						array (
							'id' => 22,
							'value' => 17,
						),
						array (
							'id' => 23,
							'value' => 26,
						),
						array (
							'id' => 24,
							'value' => 29,
						),
					),
				),
			),
		);

		$data = $this->_putOrder($input)->getData(true);

		$products = $this->_getSubResource($data['products']);

		$this->assertEquals(1, count($products));

		$product = $products[0];

		$this->assertEquals(6, $product['product_id']);

		$productOptions = array_map(function($orderOptions) {
			return array('id' => $orderOptions['product_option_id'], 'value' => $orderOptions['value']);
		},$product['product_options']->getData());

		$this->assertEquals($input['products'][0]['product_options'], $productOptions);

		// Check that the totals has changed
		$this->assertNotEquals($order['total_ex_tax'], $res['total_ex_tax']);
		$this->assertNotEquals($order['total_inc_tax'], $res['total_inc_tax']);

	}

	/**
	 * Test product inventory change on SKU tracking
	 */
	public function testPutChangeProductQuantityOnSKUInventoryTracking()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 2,
		), 'productid = 5');

		$sku = 'MB-1';
		/* @var $combination Store_Product_Attribute_Combination */
		$combination = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$combination->setStockLevel(5);
		$combination->save();

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 5,
					'quantity' => 1,
					'product_options' => array (
						array (
							'id' => 15,
							'value' => 17,
						),
						array (
							'id' => 16,
							'value' => 28,
						),
					),
				),
			),
		);

		$order = $this->_postOrder($orderInput)->getData(true);

		$this->assertNotEmpty($order['id']);

		/* @var $combinationAfter Store_Product_Attribute_Combination */
		$combinationAfter = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$this->assertEquals(4, $combinationAfter->getStockLevel());

		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'quantity' => 3,
				),
			),
		);

		$res = $this->_putOrder($input)->getData(true);

		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 5');

		/* @var $combinationAfter Store_Product_Attribute_Combination */
		$combinationAfter = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$this->assertEquals(2, $combinationAfter->getStockLevel());
	}

	/**
	 * Test product inventory change on SKU tracking while out of stock
	 */
	public function testPutChangeProductQuantityOnSKUInventoryTrackingOutOfStock()
	{
		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 2,
		), 'productid = 5');

		$sku = 'MB-1';
		/* @var $combination Store_Product_Attribute_Combination */
		$combination = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$combination->setStockLevel(2);
		$combination->save();

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'external_source' => 'POS',
			'products' => array (
				array (
					'product_id' => 5,
					'quantity' => 1,
					'product_options' => array (
						array (
							'id' => 15,
							'value' => 17,
						),
						array (
							'id' => 16,
							'value' => 28,
						),
					),
				),
			),
		);

		$order = $this->_postOrder($orderInput)->getData(true);

		$this->assertNotEmpty($order['id']);

		/* @var $combinationAfter Store_Product_Attribute_Combination */
		$combinationAfter = Store_Product_Attribute_Combination::find("sku = '".Store::getStoreDb()->Quote($sku)."'")->first();
		$this->assertEquals(1, $combinationAfter->getStockLevel());

		$products = $this->_getSubResource($order['products']);
		$product = current($products);

		$input = array(
			'id' => $order['id'],
			'products' => array(
				array(
					'id' => $product['id'],
					'quantity' => 3,
				),
			),
		);

		try {

			$res = $this->_putOrder($input)->getData(true);
			$this->assertTrue(false, 'This test supposed to throw an exception.');

		} catch (Store_Api_Exception_Request_Orders_InvalidQuantity $e) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = 5');
			$errors = $e->getErrors();
			$error = $errors[0];
			$this->assertEquals(Store_Api_Exception_Request_Orders_InvalidQuantity::TYPE_OUT_OF_STOCK, $error['type']);

		}

	}

	/**
	 * Change the billing address
	 */
	public function testPutChangeBillingAddress()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$input = array(
			'id' => $order['id'],
			'billing_address' => array(
				'first_name' => 'Trisha',
				'last_name' => 'McLaughlin',
				'company' => 'Acme Pty Ltd',
				'street_1' => '566 Sussex St',
				'street_2' => '',
				'city' => 'Austin',
				'state' => 'Texas',
				'zip' => '78757',
				'country' => 'United States',
				'country_iso2' => 'US',
				'phone' => '',
				'email' => 'elsie@example.com',
			),
		);

		$data = $this->_putOrder($input)->getData(true);

		foreach ($input['billing_address'] as $billingField => $billingFieldValue) {
			$this->assertEquals($billingFieldValue, $data['billing_address'][$billingField]);
		}

	}

	/**
	 * Test put change the shipping address with invalid ID
	 */
	public function testPutChangeShippingAddressInvalidId()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$shippingAddresses = $this->_getSubResource($order['shipping_addresses']);
		$shippingAddress = current($shippingAddresses);

		$input = array(
			'id' => $order['id'],
			'shipping_addresses' => array(
				array(
					'id' => 999999,
					'first_name' => 'Trisha',
					'last_name' => 'McLaughlin',
					'company' => 'Acme Pty Ltd',
					'street_1' => '566 Sussex St',
					'street_2' => '',
					'city' => 'Austin',
					'state' => 'Texas',
					'zip' => '78757',
					'country' => 'United States',
					'country_iso2' => 'US',
					'phone' => '',
					'email' => 'elsie@example.com',
				),
			),
		);

		$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		$this->_putOrder($input)->getData(true);

	}

	/**
	 * Test put change the shipping address
	 */
	public function testPutChangeShippingAddress()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$shippingAddresses = $this->_getSubResource($order['shipping_addresses']);
		$shippingAddress = current($shippingAddresses);

		$input = array(
			'id' => $order['id'],
			'shipping_addresses' => array(
				array(
					'id' => $shippingAddress['id'],
					'first_name' => 'Trisha',
					'last_name' => 'McLaughlin',
					'company' => 'Acme Pty Ltd',
					'street_1' => '566 Sussex St',
					'street_2' => '',
					'city' => 'Austin',
					'state' => 'Texas',
					'zip' => '78757',
					'country' => 'United States',
					'country_iso2' => 'US',
					'phone' => '',
					'email' => 'elsie@example.com',
				),
			),
		);

		$data = $this->_putOrder($input)->getData(true);

		foreach ($input['billing_address'] as $billingField => $billingFieldValue) {
			$this->assertEquals($billingFieldValue, $data['billing_address'][$billingField]);
		}

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);

		$this->assertEquals(1, count($shippingAddresses));

		$shippingAddress = current($shippingAddresses);

		foreach ($input['shipping_addresses'][0] as $shippingField=> $shippingFieldValue) {
			$this->assertEquals($shippingFieldValue, $shippingAddress[$shippingField]);
		}

	}

	/**
	 * Test put add a new shipping address
	 */
	public function testPutAddShippingAddress()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$shippingAddresses = $this->_getSubResource($order['shipping_addresses']);
		$shippingAddress = current($shippingAddresses);

		$input = array(
			'id' => $order['id'],
			'shipping_addresses' => array(
				array(
					'first_name' => 'Trisha',
					'last_name' => 'McLaughlin',
					'company' => 'Acme Pty Ltd',
					'street_1' => '566 Sussex St',
					'street_2' => '',
					'city' => 'Austin',
					'state' => 'Texas',
					'zip' => '78757',
					'country' => 'United States',
					'country_iso2' => 'US',
					'phone' => '',
					'email' => 'elsie@example.com',
				),
			),
		);

		$data = $this->_putOrder($input)->getData(true);

		foreach ($input['billing_address'] as $billingField => $billingFieldValue) {
			$this->assertEquals($billingFieldValue, $data['billing_address'][$billingField]);
		}

		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);

		$this->assertEquals(2, count($shippingAddresses));

		$newShippingAddress = array();
		foreach ($shippingAddresses as $sp) {
			if ($sp['id'] != $shippingAddress['id']) {
				$newShippingAddress = $sp;
				break;
			}
		}

		$this->assertNotEmpty($newShippingAddress);

		foreach ($input['shipping_addresses'][0] as $shippingField => $shippingFieldValue) {
			$this->assertEquals($shippingFieldValue, $newShippingAddress[$shippingField]);
		}
	}

	public function testPutUnmodifiedTotals()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$originalSubtotalExTax = $order['subtotal_ex_tax'];
		$originalSubtotalIncTax = $order['subtotal_inc_tax'];
		$originalSubtotalTax = $order['subtotal_tax'];
		$originalTotalExTax = $order['total_ex_tax'];
		$originalTotalIncTax = $order['total_inc_tax'];
		$originalTotalTax = $order['total_tax'];

		$input = array(
			'id' => $order['id'],
			'status_id' => 6,
		);

		$data = $this->_putOrder($input)->getData(true);

		$this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
		$this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
		$this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
		$this->assertEquals($originalTotalExTax, $data['total_ex_tax']);
		$this->assertEquals($originalTotalIncTax, $data['total_inc_tax']);
		$this->assertEquals($originalTotalTax, $data['total_tax']);

	}

	public function testPutWithDiscountAmount()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
					'price_inc_tax' => 10,
					'price_ex_tax' => 10,
				),
				array(
					'product_id' => 25,
					'quantity' => 1,
					'price_inc_tax' => 10,
					'price_ex_tax' => 10,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$originalSubtotalExTax = $order['subtotal_ex_tax'];
		$originalSubtotalIncTax = $order['subtotal_inc_tax'];
		$originalSubtotalTax = $order['subtotal_tax'];
		$originalTotalExTax = $order['total_ex_tax'];
		$originalTotalIncTax = $order['total_inc_tax'];
		$originalTotalTax = $order['total_tax'];

		$input = array(
			'id' => $order['id'],
			'discount_amount' => 10.5,
		);

		$data = $this->_putOrder($input)->getData(true);

		$this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
		$this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
		$this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
		$this->assertEquals($originalTotalExTax-10.5, $data['total_ex_tax']);
		$this->assertEquals($originalTotalIncTax-10.5, $data['total_inc_tax']);
		$this->assertEquals($originalTotalTax, $data['total_tax']);

	}

	public function testPutModifyShippingCost()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$originalSubtotalExTax = $order['subtotal_ex_tax'];
		$originalSubtotalIncTax = $order['subtotal_inc_tax'];
		$originalSubtotalTax = $order['subtotal_tax'];
		$originalTotalExTax = $order['total_ex_tax'];
		$originalTotalIncTax = $order['total_inc_tax'];
		$originalTotalTax = $order['total_tax'];

		$input = array(
			'id' => $order['id'],
			'shipping_cost_ex_tax' => 5,
			'shipping_cost_inc_tax' => 10,
		);

		$data = $this->_putOrder($input)->getData(true);

		$this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
		$this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
		$this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
		$this->assertEquals($originalTotalExTax+5, $data['total_ex_tax']);
		$this->assertEquals($originalTotalIncTax+10, $data['total_inc_tax']);
		$this->assertEquals($originalTotalTax+5, $data['total_tax']);
	}
	
	/**
	 * BIG-6518: Modify shipping costs to 0 recalculates totals/taxes properly.
	 */
	public function testPutModifyShippingCostToZero()
	{
	    $orderInput = array(
	            'customer_id' => 0,
	            'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
	            'billing_address' => $this->_getDummyAddress(),
	            'products' => array (
	                    array (
	                            'product_id' => 27,
	                            'quantity' => 2,
	                    ),
	            ),
	            'shipping_cost_ex_tax' => 50,
	            'shipping_cost_inc_tax' => 70,
	    );
	    $order = $this->_postOrder($orderInput)->getData(true);
	    $originalSubtotalExTax = $order['subtotal_ex_tax'];
	    $originalSubtotalIncTax = $order['subtotal_inc_tax'];
	    $originalSubtotalTax = $order['subtotal_tax'];
	    $originalTotalExTax = $order['total_ex_tax'];
	    $originalTotalIncTax = $order['total_inc_tax'];
	    $originalTotalTax = $order['total_tax'];
	
	    $input = array(
	            'id' => $order['id'],
	            'shipping_cost_ex_tax' => 0,
	            'shipping_cost_inc_tax' => 0,
	    );
	
	    $data = $this->_putOrder($input)->getData(true);
	
	    $this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
	    $this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
	    $this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
	    $this->assertEquals($originalTotalExTax - 50, $data['total_ex_tax']);
	    $this->assertEquals($originalTotalIncTax - 70, $data['total_inc_tax']);
	    $this->assertEquals($originalTotalTax - 20, $data['total_tax']);
	}

	public function testPutModifyHandlingCost()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$originalSubtotalExTax = $order['subtotal_ex_tax'];
		$originalSubtotalIncTax = $order['subtotal_inc_tax'];
		$originalSubtotalTax = $order['subtotal_tax'];
		$originalTotalExTax = $order['total_ex_tax'];
		$originalTotalIncTax = $order['total_inc_tax'];
		$originalTotalTax = $order['total_tax'];

		$input = array(
			'id' => $order['id'],
			'handling_cost_ex_tax' => 5,
			'handling_cost_inc_tax' => 10,
		);

		$data = $this->_putOrder($input)->getData(true);

		$this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
		$this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
		$this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
		$this->assertEquals($originalTotalExTax+5, $data['total_ex_tax']);
		$this->assertEquals($originalTotalIncTax+10, $data['total_inc_tax']);
		$this->assertEquals($originalTotalTax+5, $data['total_tax']);

	}

	public function testPutModifyWrappingCost()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);
		$originalSubtotalExTax = $order['subtotal_ex_tax'];
		$originalSubtotalIncTax = $order['subtotal_inc_tax'];
		$originalSubtotalTax = $order['subtotal_tax'];
		$originalTotalExTax = $order['total_ex_tax'];
		$originalTotalIncTax = $order['total_inc_tax'];
		$originalTotalTax = $order['total_tax'];

		$input = array(
			'id' => $order['id'],
			'wrapping_cost_ex_tax' => 5,
			'wrapping_cost_inc_tax' => 10,
		);

		$data = $this->_putOrder($input)->getData(true);

		$this->assertEquals($originalSubtotalExTax, $data['subtotal_ex_tax']);
		$this->assertEquals($originalSubtotalIncTax, $data['subtotal_inc_tax']);
		$this->assertEquals($originalSubtotalTax, $data['subtotal_tax']);
		$this->assertEquals($originalTotalExTax+5, $data['total_ex_tax']);
		$this->assertEquals($originalTotalIncTax+10, $data['total_inc_tax']);
		$this->assertEquals($originalTotalTax+5, $data['total_tax']);

	}

	/**
	 * Test put flag product as deleted
	 */
	public function testPutIsDeleted()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$input = array(
			'is_deleted' => true,
		);

		// validate
		$this->_getResource()->validateInput(new \Store_Api_Input_Json($input), 'PUT');
		$input['id'] = $order['id'];
		$data = $this->_putOrder($input)->getData(true);
		$this->assertNotEmpty($data['is_deleted']);

	}

	/**
	 * Test put flag product as deleted and inventory is restored
	 */
	public function testPutIsDeletedOnQuantityTrackedItemRestoredWhenSet()
	{

		Store_Config::override('UpdateInventoryOnOrderDelete', true);

		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 5,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 25,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$product = \Store\Product::find(25, false);
		$this->assertEquals(3, (int) $product['inventory_level']);

		$input = array(
			'id' => $order['id'],
			'is_deleted' => true,
		);

		$data = $this->_putOrder($input)->getData(true);
		$this->assertNotEmpty($data['is_deleted']);
		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		// restore inv tracking
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 25');

		Store_Config::override('UpdateInventoryOnOrderDelete', Store_Config::getOriginal('UpdateInventoryOnOrderDelete'));

	}

	/**
	 * Test put flag product as not deleted after being set as deleted and inventory is reduced
	 */
	public function testPutIsDeletedOnQuantityTrackedItemRestoredWhenUnSet()
	{

		Store_Config::override('UpdateInventoryOnOrderDelete', true);

		$db = Store::getStoreDb();
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 1,
			'prodcurrentinv' => 5,
		), 'productid = 25');

		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 25,
					'quantity' => 2,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$product = \Store\Product::find(25, false);
		$this->assertEquals(3, (int) $product['inventory_level']);

		// delete
		$input = array(
			'id' => $order['id'],
			'is_deleted' => true,
		);

		$data = $this->_putOrder($input)->getData(true);
		$this->assertNotEmpty($data['is_deleted']);
		$product = \Store\Product::find(25, false);
		$this->assertEquals(5, (int) $product['inventory_level']);

		// undelete
		$input = array(
			'id' => $order['id'],
			'is_deleted' => false,
		);

		$data = $this->_putOrder($input)->getData(true);
		$this->assertFalse($data['is_deleted']);
		$product = \Store\Product::find(25, false);
		$this->assertEquals(3, (int) $product['inventory_level']);

		// restore inv tracking
		$db->UpdateQuery('products', array(
			'prodinvtrack' => 0,
		), 'productid = 25');

		Store_Config::override('UpdateInventoryOnOrderDelete', Store_Config::getOriginal('UpdateInventoryOnOrderDelete'));
	}

	private function assertShippingDetails($input, $orderId, $addressId)
	{
		$order = Orders\Order::findById($orderId);
		$address = Orders\Address::findByOrderIdAndId($orderId, $addressId);
		$shipping = $address->getShipping();

		$this->assertEquals($input['base_shipping_cost'], $shipping->getBaseCost());
		$this->assertEquals($input['shipping_cost_ex_tax'], $shipping->getCostExTax());
		$this->assertEquals($input['shipping_cost_inc_tax'], $shipping->getCostIncTax());
		$this->assertEquals($input['shipping_cost_inc_tax'] - $input['shipping_cost_ex_tax'], $shipping->getTax());
		$this->assertEquals($order->getShippingCostTaxClassId(), $shipping->getTaxClassId());
		$this->assertEquals($input['base_handling_cost'], $shipping->getBaseHandlingCost());
		$this->assertEquals($input['handling_cost_ex_tax'], $shipping->getHandlingCostExTax());
		$this->assertEquals($input['handling_cost_inc_tax'], $shipping->getHandlingCostIncTax());
		$this->assertEquals($input['handling_cost_inc_tax'] - $input['handling_cost_ex_tax'], $shipping->getHandlingCostTax());
		$this->assertEquals($order->getHandlingCostTaxClassId(), $shipping->getHandlingCostTaxClassId());
	}

	/**
	 * Tests that the order_shipping table's shipping and handling details are filled in with the order details.
	 */
	public function testPostOrderWithShippingAndHandlingCostsSetsOrderShippingDetails()
	{
		$input = array(
			'customer_id' => 0,
			'base_shipping_cost' => 9,
			'shipping_cost_ex_tax' => 10,
			'shipping_cost_inc_tax' => 11,
			'base_handling_cost' => 12,
			'handling_cost_ex_tax' => 13,
			'handling_cost_inc_tax' => 14,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array(
				array(
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$res = $this->_postOrder($input);
		$data = $res->getData(true);

		$orderId = $data['id'];
		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$addressId = $shippingAddresses[0]['id'];

		$this->assertShippingDetails($input, $orderId, $addressId);

		$this->_deleteOrder($orderId);
	}

	public function testPutOrderChangeShippingAndHandlingUpdatesOrderShippingDetails()
	{
		$input = array(
			'customer_id' => 0,
			'base_shipping_cost' => 9,
			'shipping_cost_ex_tax' => 10,
			'shipping_cost_inc_tax' => 11,
			'base_handling_cost' => 12,
			'handling_cost_ex_tax' => 13,
			'handling_cost_inc_tax' => 14,
			'billing_address' => $this->_getDummyAddress(),
			'products' => array(
				array(
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$data = $this->_postOrder($input)->getData(true);

		$input = array(
			'id' => $data['id'],
			'base_shipping_cost' => 15,
			'shipping_cost_ex_tax' => 16,
			'shipping_cost_inc_tax' => 17,
			'base_handling_cost' => 18,
			'handling_cost_ex_tax' => 19,
			'handling_cost_inc_tax' => 20,
		);

		$data = $this->_putOrder($input)->getData(true);

		$orderId = $data['id'];
		$shippingAddresses = $this->_getSubResource($data['shipping_addresses']);
		$addressId = $shippingAddresses[0]['id'];

		$this->assertShippingDetails($input, $orderId, $addressId);

		$this->_deleteOrder($orderId);
	}

	/**
	 * @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
	 */
	public function testPostOrderWithNoProductsFails()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (),
		);
		$this->_postOrder($input);
	}

	public function testPutOrderWithEmptyProducts()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			),
		);

		$order = $this->_postOrder($orderInput)->getData(true);

		$input = array(
			'id' => $order['id'],
			'products' => array(),
		);

		$this->_putOrder($input)->getData(true);

		$products = $this->_getSubResource($order['products']);

		$this->assertNotEmpty($products);
		$this->assertEquals(1, count($products));
		$this->assertEquals(27, $products[0]['product_id']);
	}

	public function testPostOrderWithShippingMethod()
	{
		$methodName = 'Test Enabled Shipping Method';
		$shippingAddress = $this->_getDummyAddress();
		$shippingAddress['shipping_method'] = $methodName;

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
				),
			),
			'shipping_addresses' => array($shippingAddress),
		);

		$order = $this->_postOrder($orderInput)->getData(true);

		$shippingAddresses = $this->_getSubResource($order['shipping_addresses']);
		$this->assertCount(1, $shippingAddresses);
		$this->assertEquals($methodName, $shippingAddresses[0]['shipping_method']);
	}

	public function testPutOrderWithShippingMethod()
	{
		$initialMethodName = 'Test Initial Shipping Method';
		$updatedMethodName = 'Test Updated Shipping Method';

		$shippingAddress = $this->_getDummyAddress();
		$shippingAddress['shipping_method'] = $initialMethodName;

		$create = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
				),
			),
			'shipping_addresses' => array($shippingAddress),
		);

		$initialOrder = $this->_postOrder($create)->getData(true);

		$initialShippingAddresses = $this->_getSubResource($initialOrder['shipping_addresses']);
		$this->assertCount(1, $initialShippingAddresses);
		$this->assertEquals($initialMethodName, $initialShippingAddresses[0]['shipping_method']);

		$shippingAddressId = $initialShippingAddresses[0]['id'];

		$update = array(
			'id' => $initialOrder['id'],
			'shipping_addresses' => array(
				array('id' => $shippingAddressId, 'shipping_method' => $updatedMethodName)
			),
		);

		$updatedOrder = $this->_putOrder($update)->getData(true);
		$updatedShippingAddresses = $this->_getSubResource($updatedOrder['shipping_addresses']);
		$this->assertCount(1, $updatedShippingAddresses);
		$this->assertEquals($updatedMethodName, $updatedShippingAddresses[0]['shipping_method']);
	}

	public function skuAndBinPickingProvider()
	{
		return array(
			//    $sku,    $bin,    $price
			array('SKU-1', 'BIN-1', 10),
			array('SKU-2',  null,   10),
			array(null,    'BIN-2', 10),
			array(null,     null,   10),
			array('SKU-1', 'BIN-1', null),
			array('SKU-2',  null,   null),
			array(null,    'BIN-2', null),
			array(null,     null,   null),
		);
	}

	/**
	 * @dataProvider skuAndBinPickingProvider
	 */
	public function testPostOrderForProductsWithSkuAndBinPicking($sku, $bin, $price)
	{
		$product = $this->_createDummyProduct(array(
			'prodcode' => $sku,
			'bin_picking_number' => $bin,
		));

		$productId = $product['id'];

		$orderProduct = array (
			'product_id' => $productId,
			'quantity' => 1,
		);

		// Test when price is explicitly set
		if (!is_null($price)) {
			$orderProduct['price_ex_tax'] = $price;
			$orderProduct['price_inc_tax'] = $price;
		}

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array ($orderProduct),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$products = $this->_getSubResource($order['products']);
		$this->assertCount(1, $products);
		$this->assertEquals($productId, $products[0]['product_id']);
		$this->assertEquals($sku, $products[0]['sku']);
		$this->assertEquals($bin, $products[0]['bin_picking_number']);
	}

	public function testPostOrderWithCustomProduct()
	{
		$name = 'Custom Product';
		$sku = 'CUSTOM-SKU';
		$priceExcTax = 10;
		$priceIncTax = 20;
		$quantity = 2;

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array(
				array(
					'name' => $name,
					'quantity' => $quantity,
					'price_ex_tax' => $priceExcTax,
					'price_inc_tax' => $priceIncTax,
					'sku' => $sku,
				),
			),
		);
		$order = $this->_postOrder($orderInput)->getData(true);

		$products = $this->_getSubResource($order['products']);

		$this->assertEquals($priceIncTax * $quantity, $order['total_inc_tax']);
		$this->assertEquals($priceExcTax * $quantity, $order['total_ex_tax']);
		$this->assertEquals($priceIncTax * $quantity - $priceExcTax * $quantity, $order['total_tax']);
		$this->assertCount(1, $products);
		$this->assertEquals($name, $products[0]['name']);
		$this->assertEquals($sku, $products[0]['sku']);
	}

	public function incExcTaxProvider()
	{
		return array(
			array(10, null),
			array(null, 20),
			array(null, null),
		);
	}

	/**
	 * @dataProvider incExcTaxProvider
	 */
	public function testPostOrderWithCustomProductFailsWithoutIncTax($incTax, $excTax)
	{
		$product = array(
			'name' => 'Custom Product',
			'quantity' => 1,
		);

		if (!is_null($incTax)) {
			$product['price_inc_tax'] = $incTax;
		}

		if (!is_null($excTax)) {
			$product['price_ex_tax'] = $excTax;
		}

		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array($product),
		);
		try {
			$this->_postOrder($orderInput)->getData(true);
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertContains('products.price_ex_tax', $e->getField());
			$this->assertContains('products.price_inc_tax', $e->getField());
			return;
		}
		$this->fail('Expected Store_Api_Exception_Request_InvalidField');
	}

	public function testPostOrderWithCustomProductFailsWithoutName()
	{
		$orderInput = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array(
				array(
					'quantity' => 1,
					'price_ex_tax' => 10,
					'price_inc_tax' => 20,
					'sku' => 'CUSTOM-SKU',
				),
			),
		);
		try {
			$this->_postOrder($orderInput)->getData(true);
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertContains('products.product_id', $e->getField());
			$this->assertContains('products.name', $e->getField());
			return;
		}
		$this->fail('Expected Store_Api_Exception_Request_InvalidField');
	}
	
	public function testPostBasePrice()
	{
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 1,
				),
			),
		);
		$res = $this->_postOrder($input);
		$order = $res->getData(true);
		$products = $this->_getSubResource($order['products']);

		foreach ($products as $key => $product) {
			$orderBasePrice = $product['base_price'];
			$rowData = Store::getStoreDb()->FetchRow("SELECT prodprice FROM products WHERE productid = ".$product['product_id']);
			$productBasePrice = $rowData['prodprice'];
			$this->assertEquals($productBasePrice, $orderBasePrice);
		}
	}

}
