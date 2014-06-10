<?php

/**
* these tests can't currently use fixtures because products data exists in sample data and would be relied upon by
* product-based tests until fixtures can be properly introduced
*
* when fixtures are utilised properly change the tests here so they don't have to manually insert / remove dummy
* products
*/

class Unit_Lib_Store_Api_Version_2_Resource_Products extends Interspire_IntegrationTest
{
	private $_images = array();

	private $_dummyProducts = array();

	private function _createImportableImage ($filename = 'ProductsImage.jpg')
	{
		// put the image we want to test with into product_images/import
		$source = dirname(__FILE__) . '/' . $filename;
		$import = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/import');
		if (!file_exists($import)) {
			$this->assertTrue(isc_mkdir($import), 'isc_mkdir failed');
		}
		// ISC-5059 tempnam does not like asset paths, replace with uniqid
		$import .= ('/' . uniqid('api'));
		$this->assertTrue(copy($source, $import), 'copy failed');

		$this->_images[] = $import;
		return $import;
	}

	private function _getResource ($mobile = false)
	{
		if (!$mobile)
			return new Store_Api_Version_2_Resource_Products();
		return new Store_Api_Version_2_Resource_Mobile_Products();
	}

	private function _getCountResource ()
	{
		return new Store_Api_Version_2_Resource_Products_Count();
	}

	private function _generateName ()
	{
		return 'PRODUCT_' . mt_rand(1, PHP_INT_MAX);
	}

	private function _createAPIDummyProduct ($data = array(), $resource = null)
	{
        if ($resource === null) {
			$resource = $this->_getResource();
		}

		$data = array_merge(array(
			'name' => $this->_generateName(),
			'categories' => array(1, 2),
			'price' => 10,
			'type' => 'physical',
			'availability' => 'available',
			'weight' => 1,
		), $data);

        $json = Interspire_Json::encode($data);

		$postRequest = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/json', 'REQUEST_METHOD' => 'POST'), $json);

		$postResult = $resource->postAction($postRequest)->getData(true);
		$this->assertSame(201, $postRequest->getResponse()->getStatus());

		$productId = (int)$postResult['id'];
		$this->_dummyProducts[] = $productId;
		return $this->_getProduct($productId);
	}

	private function _createDBDummyProduct ($data = array(), $resource = null)
	{
		$data = array_merge(array(
			'prodname' => $this->_generateName(),
			'prodcatids' => '',
			'proddateadded' => time(),
			'prodlastmodified' => time(),
		), $data);

		$productId = Store::getStoreDb()->InsertQuery('products', $data);
		$this->assertTrue(isId($productId), "dummy product insert failed: " . Store::getStoreDb()->GetErrorMsg());

		$productId = (int)$productId;
		$this->_dummyProducts[] = $productId;

		return Store::getStoreDb()->FetchRow("SELECT * FROM [|PREFIX|]products WHERE productid = " . $productId);
	}

	private function _updateProduct ($id, $json, $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$json = Interspire_Json::encode($json);

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', (int)$id);

		$resource->putAction($request);
		return $this->_getProduct($id);
	}

	private function _getProduct ($id, $server = array())
	{
		$request = new Interspire_Request(array(), array(), array(), $server);
		$request->setUserParam('products', $id);
		return $this->_getResource()->getAction($request)->getData(true);
	}

	private function _deleteProduct ($id)
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
		return $this->_getResource()->deleteAction($request);
	}

	private function _getAnyProductTypeId ()
	{
		$type = Store_Product_Type::find()->limit(1)->first();
		if (!$type) {
			return false;
		}

		return $type->getId();
	}

	private function _getAnyCategoryId ($except = array())
	{
		$where = "";
		if ($except) {
			$where = "categoryid NOT IN (" . implode(",", array_filter($except, 'isId')) . ")";
		}

		if ($where) {
			$where = " WHERE " . $where;
		}

		$query = "
			SELECT
				categoryid
			FROM
				[|PREFIX|]categories
			" . $where . "
			LIMIT
				1
		";

		$result = Store::getStoreDb()->FetchOne($query);
		if ($result === false) {
			return false;
		}

		return (int)$result;
	}

	public function tearDown ()
	{
		foreach ($this->_images as $image) {
			unlink($image);
		}

		foreach ($this->_dummyProducts as $id) {
				$this->_deleteProduct($id);
		}
	}
	public function testGetFormattedCurrency ()
	{
		$product = $this->_createAPIDummyProduct(array('price' => '155.50'));
		$interspireRequest = new Interspire_Request();

		$interspireRequest->setUserParam('products', $product['id']);
		$list = $this->_getResource(true)->getAction($interspireRequest)->getData();

		$product = $list[0];

		$this->assertEquals($product['price_formatted'], '$155.50');
	}

	public function testGetAttributes ()
	{
		// this tests whether the attributes key is present when retrieving a single product
		$product = $this->_createAPIDummyProduct();

		$interspireRequest = new Interspire_Request();
		$list = $this->_getResource(true)->getAction($interspireRequest->setUserParam('products', $product['id']))->getData();

		$product = $list[0];

		$this->assertTrue(in_array('attributes', array_keys($product)));
	}

	public function testGetSortedList ()
	{
		// this test will only ensure that the results are not in
		// the same order that ORDER BY id ASC would produce
		$products = array();

		for ($i = 0; $i < 5; $i++) {
			$product = $this->_createAPIDummyProduct();
			$products[$product['id']] = false;
		}


		$list = $this->_getResource(true)->getAction(
			new Interspire_Request(array('sort' => 'name,desc'))
		)->getData();

		$productNames = array();

		foreach ($list as $item)
			$productNames[$item['id']] = $item['name'];

		$orderIds = $orderIds_sorted = array_keys($productNames);
		sort($orderIds_sorted);
		$orderIds = implode('', $orderIds);
		$orderIds_sorted = implode('', $orderIds_sorted);

		$this->assertFalse(empty($list));
		$this->assertNotEquals($orderIds, $orderIds_sorted);
	}

	public function testGetList ()
	{
		// can't accurately test this without fixtures support (which would allow us to setup a specific data set) but
		// we can at least do *something* for now

		// setup some products to find
		$products = array();

		$product = $this->_createAPIDummyProduct();
		$products[$product['id']] = false;

		$product = $this->_createAPIDummyProduct();
		$products[$product['id']] = false;

		// grab a list from the api
		$list = $this->_getResource()->getAction(new Interspire_Request())->getData();

		// smoke test it
		$this->assertInternalType('array', $list);
		$this->assertFalse(empty($list));

		if (count($list) == Store_Api::ITEMS_PER_PAGE_DEFAULT) {
			// for now if the paging limit is reached (probably by running this test repeatedly on an installed store)
			// then we can't test the rest -- skip (this shouldn't happen on bamboo!)
			$this->markTestSkipped();
			return;
		}

		// run through the list and locate the dummy products
		foreach ($list as $item) {
			$id = (int)$item['id'];
			if (isset($products[$id])) {
				$products[$id] = true;
			}
		}

		// if any element remains false then something has failed with getlist
		$this->assertFalse(in_array(false, $products), "one or more dummy products were not found in the list");
	}

	public function testDeleteList ()
	{
		// can't test this until we get proper fixture support without destroying sample data data used by other tests
		$this->markTestSkipped();
	}

	public function testGetEntity ()
	{
		$product = $this->_createAPIDummyProduct();

		$result = $this->_getProduct($product['id']);
		$this->assertSame((int)$product['id'], $result['id']);
	}

	public function testGetEntityIfModifiedSinceModified ()
	{
		$product = $this->_createAPIDummyProduct();
		$modified = time() - 3600;

		$result = $this->_getProduct($product['id'], array(
			'HTTP_IF_MODIFIED_SINCE' => date('r', $modified),
		));

		$this->assertNotEquals(array(), $result, "get with if-modified-since should return entity data but isn't");
	}

	public function testGetEntityIfModifiedSinceNotModified ()
	{
		$product = $this->_createAPIDummyProduct();
		$modified = time() + 3600;

		$result = $this->_getProduct($product['id'], array(
			'HTTP_IF_MODIFIED_SINCE' => date('r', $modified),
		));

		$this->assertSame(array(), $result, "get with if-modified-since should not return entity data but is");
	}

	public function testGetMissingEntity ()
	{
		$this->assertArrayIsEmpty($this->_getProduct(PHP_INT_MAX));
	}

	public function testPutMissingEntity ()
	{
		$this->setExpectedException('Store_Api_Exception_Resource_ResourceNotFound');
		$this->_updateProduct(PHP_INT_MAX, array('name' => $this->_generateName()));
	}

	public function testPutStringNullName ()
	{
		// I should be able to pass a string which says "null" without it being treated as a literal NULL
		$product = $this->_createAPIDummyProduct();
		$this->_updateProduct($product['id'], array(
			'name' => 'null',
		));
	}

	public function testPutUniqueNameConflict ()
	{
		$product_a = $this->_createAPIDummyProduct();
		$product_b = $this->_createAPIDummyProduct();

		try {
			$this->_updateProduct($product_b['id'], array('name' => $product_a['name']));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$this->assertNotEquals('', $e->getDetail('conflict_reason'), 'conflict has no reason');
		}
	}

	public function testPutUniqueSkuConflict ()
	{
		$product_a = $this->_createAPIDummyProduct(array('sku' => $this->_generateName()));
		$product_b = $this->_createAPIDummyProduct(array('sku' => $this->_generateName()));

		try {
			$this->_updateProduct($product_b['id'], array('sku' => $product_a['sku']));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$this->assertNotEquals('', $e->getDetail('conflict_reason'), 'conflict has no reason');
		}
	}

	public function testPutSkuInvTrackWithoutOptionSetInvalid ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$this->_updateProduct($product['id'], array('inventory_tracking' => 'sku'));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertSame('inventory_tracking', $e->getField());
			$this->assertNotEquals('', $e->getDetail('invalid_reason'), 'invalid field has no reason');
		}
	}

	public function testPostWithOptionSetIdCreatesProductAttributes()
	{
		$typeId = $this->_getAnyProductTypeId();
		if (!$typeId) {
			$this->markTestSkipped("no product type id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct(array(
			'inventory_tracking' => 'sku',
			'option_set_id' => $typeId,
		));

		$productType = new Store_Product_Type();
		$productType->setId($typeId);
		$expectedAttributeCount = $productType->getProductTypeAttributes()->count();

		$productAttributeCount = Store_Product_Attribute::find('product_id = ' . (int)$product['id'])->count();

		$this->assertEquals($expectedAttributeCount, $productAttributeCount);
	}

	public function testPutWithOptionSetIdCreatesProductAttributes()
	{
		$typeId = $this->_getAnyProductTypeId();
		if (!$typeId) {
			$this->markTestSkipped("no product type id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct(array(
			'inventory_tracking' => 'none',
			'option_set_id' => null,
		));

		$updateValues = array(
			'inventory_tracking' => 'sku',
			'option_set_id' => $typeId,
		);
		$this->_updateProduct($product['id'], $updateValues);

		$productType = new Store_Product_Type();
		$productType->setId($typeId);
		$expectedAttributeCount = $productType->getProductTypeAttributes()->count();

		$productAttributeCount = Store_Product_Attribute::find('product_id = ' . (int)$product['id'])->count();

		$this->assertEquals($expectedAttributeCount, $productAttributeCount);
	}

	public function testPutRemoveOptionSet ()
	{
		$typeId = $this->_getAnyProductTypeId();
		if (!$typeId) {
			$this->markTestSkipped("no product type id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct(array(
			'option_set_id' => $typeId,
		));

		$updated = $this->_updateProduct($product['id'], array(
			'option_set_id' => null,
		));

		$this->assertNull($updated['option_set_id']);
	}

	public function testPutRemoveOptionSetWithSkuInventoryTrackingConflict ()
	{
		$typeId = $this->_getAnyProductTypeId();
		if (!$typeId) {
			$this->markTestSkipped("no product type id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct(array(
			'inventory_tracking' => 'sku',
			'option_set_id' => $typeId,
		));

		try {
			$this->_updateProduct($product['id'], array(
				'option_set_id' => null,
			));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $e) {
			$this->assertNotEquals('', $e->getDetail('conflict_reason'), "conflict has no reason");
		}
	}

	public function testPutPurchasable ()
	{
		$product = $this->_createDBDummyProduct(array(
			'prodallowpurchases' => 0,
			'prodhideprice' => 1,
			'prodcallforpricinglabel' => 'foo',
		));

		$updated = $this->_updateProduct($product['productid'], array(
			'availability' => 'available',
		));

		$this->assertSame('available', $updated['availability'], "availability mismatch");
		$this->assertSame(false, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('', $updated['preorder_release_date'], "preorder_release_date mismatch");
	}

	public function testPutNotPurchasable ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'disabled',
		));

		$this->assertSame('disabled', $updated['availability'], "availability mismatch");
		$this->assertSame(false, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('', $updated['preorder_release_date'], "preorder_release_date mismatch");
	}

	public function testPutNotPurchasablePriceHidden ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'disabled',
			'is_price_hidden' => true,
		));

		$this->assertSame('disabled', $updated['availability'], "availability mismatch");
		$this->assertSame(true, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('', $updated['preorder_release_date'], "preorder_release_date mismatch");
	}

	public function testPutNotPurchasableCallPricing ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'disabled',
			'is_price_hidden' => true,
			'price_hidden_label' => 'foo',
		));

		$this->assertSame('disabled', $updated['availability'], "availability mismatch");
		$this->assertSame(true, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('foo', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('', $updated['preorder_release_date'], "preorder_release_date mismatch");
	}

	public function testPutPreorder ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'preorder',
		));

		$this->assertSame('preorder', $updated['availability'], "availability mismatch");
		$this->assertSame(false, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('', $updated['preorder_release_date'], "preorder_release_date mismatch");
	}

	public function testPutPreorderValidReleaseDate ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'preorder',
			'preorder_release_date' => 'Tue, 19 Jan 2038 03:14:07 +1100',
		));

		$this->assertSame('preorder', $updated['availability'], "availability mismatch");
		$this->assertSame(false, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('Mon, 18 Jan 2038 16:14:07 +0000', $updated['preorder_release_date'], "preorder_release_date mismatch");
		$this->assertSame(false, $updated['is_preorder_only'], "is_preorder_only mismatch");
	}

	public function testPutPreorderRemoveOnDate ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'availability' => 'preorder',
			'preorder_release_date' => 'Tue, 19 Jan 2038 03:14:07 +1100',
			'is_preorder_only' => true,
		));

		$this->assertSame('preorder', $updated['availability'], "availability mismatch");
		$this->assertSame(false, $updated['is_price_hidden'], "is_price_hidden mismatch");
		$this->assertSame('', $updated['price_hidden_label'], "price_hidden_label mismatch");
		$this->assertSame('Mon, 18 Jan 2038 16:14:07 +0000', $updated['preorder_release_date'], "preorder_release_date mismatch");
		$this->assertSame(true, $updated['is_preorder_only'], "is_preorder_only mismatch");
	}

	public function testPutEventDateRangeRequiresStart ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'event_date_type' => 'range',
				'event_date_start' => 'Thu, 25 Aug 2011 10:58:00 +1100',
			));
			$this->setExpectedException('Store_Api_Exception_Request_RequiredFieldNotSupplied');
		} catch (Store_Api_Exception_Request_RequiredFieldNotSupplied $e) {
			$this->assertSame('event_date_end', $e->getField(), "required field mismatch");
		}
	}

	public function testPutEventDateRangeRequiresEnd ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'event_date_type' => 'range',
				'event_date_end' => 'Tue, 19 Jan 2038 03:14:07 +1100',
			));
			$this->setExpectedException('Store_Api_Exception_Request_RequiredFieldNotSupplied');
		} catch (Store_Api_Exception_Request_RequiredFieldNotSupplied $e) {
			$this->assertSame('event_date_start', $e->getField(), "required field mismatch");
		}
	}

	public function testPutEventDateRangeEndBeforeStart ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'event_date_type' => 'range',
				'event_date_start' => 'Tue, 19 Jan 2038 03:14:07 +1100',
				'event_date_end' => 'Thu, 25 Aug 2011 10:58:00 +1100',
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertSame('event_date_start', $e->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $e->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testPutValidEventDateRange ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'event_date_type' => 'range',
			'event_date_start' => 'Thu, 25 Aug 2011 10:58:00 +1100',
			'event_date_end' => 'Tue, 19 Jan 2038 03:14:07 +1100',
		));

		$this->assertSame('range', $updated['event_date_type'], "event_date_type mismatch");
		$this->assertSame('Wed, 24 Aug 2011 23:58:00 +0000', $updated['event_date_start'], "event_date_start mismatch");
		$this->assertSame('Mon, 18 Jan 2038 16:14:07 +0000', $updated['event_date_end'], "event_date_end mismatch");
	}

	public function testPutEventDateAfterRequiresStart ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'event_date_type' => 'after',
			));
			$this->setExpectedException('Store_Api_Exception_Request_RequiredFieldNotSupplied');
		} catch (Store_Api_Exception_Request_RequiredFieldNotSupplied $e) {
			$this->assertSame('event_date_start', $e->getField(), "required field mismatch");
		}
	}

	public function testPutValidEventDateAfter ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'event_date_type' => 'after',
			'event_date_start' => 'Thu, 25 Aug 2011 10:58:00 +1100',
		));

		$this->assertSame('after', $updated['event_date_type'], "event_date_type mismatch");
		$this->assertSame('Wed, 24 Aug 2011 23:58:00 +0000', $updated['event_date_start'], "event_date_start mismatch");
	}

	public function testPutEventDateBeforeRequiresEnd ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'event_date_type' => 'before',
			));
			$this->setExpectedException('Store_Api_Exception_Request_RequiredFieldNotSupplied');
		} catch (Store_Api_Exception_Request_RequiredFieldNotSupplied $e) {
			$this->assertSame('event_date_end', $e->getField(), "required field mismatch");
		}
	}

	public function testPutValidEventDateBefore ()
	{
		$product = $this->_createAPIDummyProduct();

		$updated = $this->_updateProduct($product['id'], array(
			'event_date_type' => 'before',
			'event_date_end' => 'Tue, 19 Jan 2038 03:14:07 +1100',
		));

		$this->assertSame('before', $updated['event_date_type'], "event_date_type mismatch");
		$this->assertSame('Mon, 18 Jan 2038 16:14:07 +0000', $updated['event_date_end'], "event_date_end mismatch");
	}

	public function testDeleteEntity ()
	{
		$product = $this->_createAPIDummyProduct();

		$this->assertNull($this->_deleteProduct($product['id']));

		$this->assertArrayIsEmpty($this->_getProduct($product['id']));
	}

	public function testDeleteMissingEntity ()
	{
		$this->setExpectedException('Store_Api_Exception_Resource_ResourceNotFound');
		$this->_deleteProduct(PHP_INT_MAX);
	}

	public function testPutValidCategory ()
	{
		$categoryId = $this->_getAnyCategoryId();
		if (!$categoryId) {
			$this->markTestSkipped("no category id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct();
		$categories = array($categoryId);

		$updated = $this->_updateProduct($product['id'], array(
			'categories' => $categories,
		));

		$this->assertSame($categories, $updated['categories'], "categories list mismatch");
	}

	public function testPutEmptyCategory ()
	{
		$product = $this->_createAPIDummyProduct();
		try {
			$updated = $this->_updateProduct($product['id'], array(
				'categories' => array(),
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertSame('categories', $e->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $e->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testPutInvalidCategory ()
	{
		$product = $this->_createAPIDummyProduct();

		try {
			$updated = $this->_updateProduct($product['id'], array(
				'categories' => array(PHP_INT_MAX),
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertSame('categories', $e->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $e->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testPutDuplicateCategory ()
	{
		$categoryId = $this->_getAnyCategoryId();
		if (!$categoryId) {
			$this->markTestSkipped("no category id found to test with");
			return false;
		}

		$product = $this->_createAPIDummyProduct();
		$categories = array(
			$categoryId,
			$categoryId,
		);

		try {
			$this->_updateProduct($product['id'], array(
				'categories' => $categories,
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $e) {
			$this->assertSame('categories', $e->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $e->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	/**
	 * Regression test for BIG-1860 - ensure categories is set during product creation (prodcatids field was previously empty).
	 */
	public function testCreateProductSetsCategories()
	{
		$product = $this->_createAPIDummyProduct();
		$this->assertSame(array(1, 2), $product['categories'], "categories list mismatch");
	}

	public function testAddProdCalculatedPriceWithSale()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '155.50',
				'sale_price' => '55.00',
			));

		$interspireRequest = new Interspire_Request();

		$interspireRequest->setUserParam('products', $product['id']);
		$list = $this->_getResource(false)->getAction($interspireRequest)->getData();

		$product = $list[0];

		$this->assertEquals($product['calculated_price'], '55.00');
	}

	public function testAddProdCalculatedPriceWithoutSale()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '155.50',
			));

		$interspireRequest = new Interspire_Request();

		$interspireRequest->setUserParam('products', $product['id']);
		$list = $this->_getResource(false)->getAction($interspireRequest)->getData();

		$product = $list[0];

		$this->assertEquals($product['calculated_price'], '155.50');
	}

	public function testAddProdCalculatedPriceWithoutPrice()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '0.00',
			));

		$interspireRequest = new Interspire_Request();

		$interspireRequest->setUserParam('products', $product['id']);
		$list = $this->_getResource(false)->getAction($interspireRequest)->getData();

		$product = $list[0];

		$this->assertEquals($product['calculated_price'], '0.00');
	}

	public function testEditProdCalculatedPriceWithSale()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '155.50',
				'sale_price' => '0.00',
			));

		$updated = $this->_updateProduct($product['id'], array(
				'sale_price' => '55.00',
			));

		$this->assertEquals('55.00', $updated['calculated_price'], "calculated price error");
	}

	public function testEditProdCalculatedPriceWithModifiedSale()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '155.50',
				'sale_price' => '55.00',
			));

		$updated = $this->_updateProduct($product['id'], array(
				'price' => '255.00',
				'sale_price' => '155.00',
			));

		$this->assertEquals('155.00', $updated['calculated_price'], "calculated price error");
	}

	public function testEditProdCalculatedPriceWithoutPrice()
	{
		$product = $this->_createAPIDummyProduct(array(
				'price' => '155.50',
			));

		$updated = $this->_updateProduct($product['id'], array(
				'price' => '255.00',
			));

		$this->assertEquals('255.00', $updated['calculated_price'], "calculated price error");
	}

	public function testFilterMinInventoryLevel()
	{
		$product4 = $this->_createAPIDummyProduct(array(
			'name' => 'Expected Product '.uniqid(),
			'price' => '155.50',
			'inventory_tracking' => "simple",
			'inventory_level' => 4,
		));

		$product2 = $this->_createAPIDummyProduct(array(
			'name' => 'Expected Product '.uniqid(),
			'price' => '155.50',
			'inventory_tracking' => "simple",
			'inventory_level' => 2,
		));

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIds = array_map(function($product) {
			return $product['id'];
		}, $list);

		$this->assertEquals(2, count($list));
		$this->assertContains($product4['id'], $productIds);
		$this->assertContains($product2['id'], $productIds);

	}

	public function testFilterMaxInventoryLevel()
	{
		$product4 = $this->_createAPIDummyProduct(array(
			'name' => 'Expected Product '.uniqid(),
			'price' => '155.50',
			'inventory_tracking' => "simple",
			'inventory_level' => 4,
		));

		$product2 = $this->_createAPIDummyProduct(array(
			'name' => 'Expected Product '.uniqid(),
			'price' => '155.50',
			'inventory_tracking' => "simple",
			'inventory_level' => 2,
		));

		$request = new Interspire_Request(array(
			'max_inventory_level' => 2
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIds = array_map(function($product) {
			return $product['id'];
		}, $list);

		$this->assertEquals(1, count($list));
		$this->assertContains($product2['id'], $productIds);
		$this->assertNotContains($product4['id'], $productIds);

	}

	public function testFilterMinMaxInventoryLevel()
	{
		$products = array();
		for ($i = 1; $i <= 5; $i++) {
			$products[] = $this->_createAPIDummyProduct(array(
				'name' => 'Expected Product '.uniqid(),
				'price' => '155.50',
				'inventory_tracking' => "simple",
				'inventory_level' => $i,
			));
		}


		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'max_inventory_level' => 4,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIds = array_map(function($product) {
			return $product['id'];
		}, $list);

		$this->assertEquals(3, count($list));
		$this->assertContains($products[1]['id'], $productIds);
		$this->assertContains($products[2]['id'], $productIds);
		$this->assertContains($products[3]['id'], $productIds);
		$this->assertNotContains($products[0]['id'], $productIds);
		$this->assertNotContains($products[4]['id'], $productIds);

	}


	public function testFilterInventoryLevelOnSkuWithoutFlag()
	{

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'max_inventory_level' => 4,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(0, count($list));

	}

	public function testFilterMinInventoryLevelOnSkuWithFlag()
	{

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(2, count($list));
		$this->assertContains($productIds[0], $productIdFromList);
		$this->assertContains($productIds[1], $productIdFromList);

	}

	public function testFilterMaxInventoryLevelOnSkuWithFlag()
	{

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'max_inventory_level' => 2,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(1, count($list));
		$this->assertContains($productIds[0], $productIdFromList);
		$this->assertNotContains($productIds[1], $productIdFromList);

	}

	public function testFilterMinMaxInventoryLevelOnSkuWithFlagAndSimpleInventory()
	{
		$products = array();
		for ($i = 1; $i <= 5; $i++) {
			$products[] = $this->_createAPIDummyProduct(array(
				'name' => 'Expected Product '.uniqid(),
				'price' => '155.50',
				'inventory_tracking' => "simple",
				'inventory_level' => $i,
			));
		}

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'max_inventory_level' => 4,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(5, count($list));
		$this->assertContains($productIds[0], $productIdFromList);
		$this->assertContains($productIds[1], $productIdFromList);
		$this->assertContains($products[1]['id'], $productIdFromList);
		$this->assertContains($products[2]['id'], $productIdFromList);
		$this->assertContains($products[3]['id'], $productIdFromList);
		$this->assertNotContains($products[0]['id'], $productIdFromList);
		$this->assertNotContains($products[4]['id'], $productIdFromList);

	}

	public function testFilterMinInventoryLevelOnSkuWithFlagAndSimpleInventory()
	{
		$products = array();
		for ($i = 1; $i <= 5; $i++) {
			$products[] = $this->_createAPIDummyProduct(array(
				'name' => 'Expected Product '.uniqid(),
				'price' => '155.50',
				'inventory_tracking' => "simple",
				'inventory_level' => $i,
			));
		}

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(6, count($list));
		$this->assertContains($productIds[0], $productIdFromList);
		$this->assertContains($productIds[1], $productIdFromList);
		$this->assertContains($products[1]['id'], $productIdFromList);
		$this->assertContains($products[2]['id'], $productIdFromList);
		$this->assertContains($products[3]['id'], $productIdFromList);
		$this->assertContains($products[4]['id'], $productIdFromList);
		$this->assertNotContains($products[0]['id'], $productIdFromList);

	}

	public function testFilterMaxInventoryLevelOnSkuWithFlagAndSimpleInventory()
	{
		$products = array();
		for ($i = 1; $i <= 5; $i++) {
			$products[] = $this->_createAPIDummyProduct(array(
				'name' => 'Expected Product '.uniqid(),
				'price' => '155.50',
				'inventory_tracking' => "simple",
				'inventory_level' => $i,
			));
		}

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(2, 4);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'max_inventory_level' => 2,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}

		$this->assertEquals(3, count($list));
		$this->assertContains($productIds[0], $productIdFromList);
		$this->assertContains($products[0]['id'], $productIdFromList);
		$this->assertContains($products[1]['id'], $productIdFromList);
		$this->assertNotContains($productIds[1], $productIdFromList);
		$this->assertNotContains($products[2]['id'], $productIdFromList);
		$this->assertNotContains($products[3]['id'], $productIdFromList);
		$this->assertNotContains($products[4]['id'], $productIdFromList);

	}

	public function testFilterMinMaxInventoryLevelOnSkuWithFlagAndNoResults()
	{

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'max_inventory_level' => 4,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		$this->assertEquals(0, count($list));

	}

	public function testFilterMinInventoryLevelOnSkuWithFlagAndNoResults()
	{

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		$this->assertEquals(0, count($list));

	}

	public function testFilterMaxInventoryLevelOnSkuWithFlagAndNoResults()
	{

		$request = new Interspire_Request(array(
			'max_inventory_level' => 4,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		$this->assertEquals(0, count($list));

	}

	public function testFilterMinMaxInventoryLevelOnSkuWithFlagAndSimpleInventoryWithoutMatchinSku()
	{
		$products = array();
		for ($i = 1; $i <= 5; $i++) {
			$products[] = $this->_createAPIDummyProduct(array(
				'name' => 'Expected Product '.uniqid(),
				'price' => '155.50',
				'inventory_tracking' => "simple",
				'inventory_level' => $i,
			));
		}

		$combinationIterator = Store_Product_Attribute_Combination::find("product_id is not null");
		$combinationsByProductId = array();
		/** @var Store_Product_Attribute_Combination $combination */
		foreach ($combinationIterator as $combination) {
			if (!isset($combinationsByProductId[$combination->getProductId()])) {
				$combinationsByProductId[$combination->getProductId()] = array();
			}
			$combinationsByProductId[$combination->getProductId()][] = $combination;
		}

		$db =  Store::getStoreDb();
		$quantities = array(1, 5);
		$productIds = array_keys($combinationsByProductId);

		for($i = 0; $i < count($quantities); $i++) {
			$productId = $productIds[$i];
			$db->UpdateQuery('products', array(
				'prodinvtrack' => 2,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel($quantities[$i]);
				$combination->save();
			}
		}

		$request = new Interspire_Request(array(
			'min_inventory_level' => 2,
			'max_inventory_level' => 4,
			'include_sku' => true,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();
		$productIdFromList = array_map(function($product) {
			return $product['id'];
		}, $list);

		// tear down
		foreach ($productIds as $productId) {

			$db->UpdateQuery('products', array(
				'prodinvtrack' => 0,
			), 'productid = '.$productId);

			/** @var Store_Product_Attribute_Combination $combination */
			foreach ($combinationsByProductId[$productId] as $combination) {
				$combination->setStockLevel(null);
				$combination->save();
			}

		}
		
		$this->assertEquals(3, count($list));
		$this->assertContains($products[1]['id'], $productIdFromList);
		$this->assertContains($products[2]['id'], $productIdFromList);
		$this->assertContains($products[3]['id'], $productIdFromList);
		$this->assertNotContains($productIds[0], $productIdFromList);
		$this->assertNotContains($productIds[1], $productIdFromList);
		$this->assertNotContains($products[0]['id'], $productIdFromList);
		$this->assertNotContains($products[4]['id'], $productIdFromList);
	}

	public function testFilterByCategoryId()
	{
		$db = Store::getStoreDb();
		$res = $db->Query("select productid, categoryid from [|PREFIX|]categoryassociations");

		$productIdByCategories = array();
		while ($row = $db->Fetch($res)) {
			if (!isset($productIdByCategories[$row['categoryid']])) {
				$productIdByCategories[$row['categoryid']] = array();
			}
			$productIdByCategories[$row['categoryid']][] = $row['productid'];
		}

		foreach ($productIdByCategories as $catId => $productIds) {

			$request = new Interspire_Request(array(
				'category' => $catId,
			), null, null, array('CONTENT_TYPE' => 'application/json'));

			$list = $this->_getResource(false)->getAction($request)->getData();

			$this->assertEquals(count($productIds), count($list));

			$productIdsFromApi = array_map(function($product) { return $product['id']; }, $list);

			foreach ($productIds as $pid) {
				$this->assertContains($pid, $productIdsFromApi);
			}

		}

	}

	public function testFilterByNonExistentCategoryId()
	{

		$request = new Interspire_Request(array(
			'category' => 999999999,
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		$this->assertEquals(0, count($list));

	}

	public function testFilterByCategoryName()
	{
		$db = Store::getStoreDb();
		$res = $db->Query("select productid, catname from [|PREFIX|]categoryassociations ca, [|PREFIX|]categories c where ca.categoryid = c.categoryid");

		$productIdByCategories = array();
		while ($row = $db->Fetch($res)) {
			if (!isset($productIdByCategories[$row['catname']])) {
				$productIdByCategories[$row['catname']] = array();
			}
			$productIdByCategories[$row['catname']][] = $row['productid'];
		}

		foreach ($productIdByCategories as $catName => $productIds) {

			// remove duplicates
			$productIds = array_unique($productIds);

			$request = new Interspire_Request(array(
				'category' => $catName,
			), null, null, array('CONTENT_TYPE' => 'application/json'));

			$list = $this->_getResource(false)->getAction($request)->getData();

			$this->assertEquals(count($productIds), count($list));

			$productIdsFromApi = array_map(function($product) { return $product['id']; }, $list);

			foreach ($productIds as $pid) {
				$this->assertContains($pid, $productIdsFromApi);
			}
		}
	}

	public function testFilterByNonExistentCategoryName()
	{

		$request = new Interspire_Request(array(
			'category' => "__XXX-XXX__",
		), null, null, array('CONTENT_TYPE' => 'application/json'));

		$list = $this->_getResource(false)->getAction($request)->getData();

		$this->assertEquals(0, count($list));

	}

}
