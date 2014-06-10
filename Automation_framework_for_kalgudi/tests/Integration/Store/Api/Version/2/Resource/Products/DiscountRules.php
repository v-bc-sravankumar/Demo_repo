<?php

use Store\Product\DiscountRule;

class Integration_Store_Api_Version_2_Resource_Products_DiscountRules extends Interspire_IntegrationTest
{
	/** @var $resource Store_Api_Version_2_Resource_Products_Discountrules */
	private $resource = null;

	public static function setUpBeforeClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('product_discounts');
		Interspire_DataFixtures::getInstance()->loadData('product_discounts');
	}

	public static function tearDownAfterClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('product_discounts');
	}

	public function setUp()
	{
		$this->resource = new Store_Api_Version_2_Resource_Products_Discountrules();
	}

	public function tearDown()
	{
		$this->resource = null;
	}

	public function getIdentityTestCases()
	{
		return array(
			array(1, 11, array("id" => 1, "product_id" => 11, /* no min */ "max" => 4,  "type" => "fixed",   "type_value" => 20.0)),
			array(2, 12, array("id" => 2, "product_id" => 12, "min" => 7, /* no max */  "type" => "percent", "type_value" => 10.0)),
			array(3, 13, array("id" => 3, "product_id" => 13, "min" => 5,  "max" => 10, "type" => "price",   "type_value" => 5.0)),
			array(4, 13, array("id" => 4, "product_id" => 13, "min" => 15, "max" => 20, "type" => "price",   "type_value" => 10.0)),
			array(5, 13, array("id" => 5, "product_id" => 13, "min" => 25, "max" => 30, "type" => "price",   "type_value" => 20.0)),
		);
	}

	/**
	 * @dataProvider getIdentityTestCases
	 */
	public function testGetIdentity($id, $product_id, $expected_response)
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('discountrules', $id);
		$request->setUserParam('products', $product_id);
		$result = $this->resource->getAction($request);
		$data = $result->getData();
		$this->assertEquals($expected_response, $data);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testGetActionFail()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('products', 1);
		$request->setUserParam('discountrules', 999999999);
		$this->resource->getAction($request);
	}

	public function testGetAllActionSuccess()
	{
		$productId = 13;
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('products', $productId);
		$result = $this->resource->getAction($request);
		$data = $result->getData();
		$expected = array_map(
			function ($el) { return $el[2]; },
			array_values(array_filter(
				$this->getIdentityTestCases(),
				function ($el) use ($productId) { return $el[1] == $productId; }
			))
		);
		$this->assertEquals($expected, $data);
	}

	/**
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostMissingType()
	{
		$json = json_encode(array('type_value' => 5, 'min' => 0, 'max' => 1));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostMissingTypeValue()
	{
		$json = json_encode(array('type' => 'fixed', 'min' => 0, 'max' => 1));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostInvalidType()
	{
		$json = json_encode(array('type' => 'foo', 'type_value' => 5, 'min' => 0, 'max' => 1));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_MethodNotFound
	 * @expectedExceptionCode 405
	 */
	public function testPostToIdError()
	{
		$json = json_encode(array('type' => 'fixed', 'type_value' => 5, 'min' => 0, 'max' => 1));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$request->setUserParam('discountrules', 1);
		$this->resource->postAction($request);
	}

	/**
	 * ($productId, $min, $max)
	 */
	public function overlapDataProvider()
	{
		return array(
			// Product 11 has (min: unbounded, max: 4)
			array(11, null, 5),
			array(11, 1, 3),
			array(11, 3, 5),
			// Product 12 has (min: 7, max: unbounded)
			array(12, 6, null),
			array(12, 6, 8),
			array(12, 8, 12),
			// Product 13 has (min: 5, max: 10), (min: 15, max: 20), (min: 25, max: 30)
			array(13, 6, 9),
			array(13, 4, 6),
			array(13, 9, 11),
			array(13, 4, 11),
			array(13, 7, 17),
			array(13, 4, 21),
		);
	}

	/**
	 * @dataProvider overlapDataProvider
	 * @expectedException Store_Api_Exception_Resource_Conflict
	 * @expectedExceptionCode 409
	 */
	public function testPostOverlapError($productId, $min, $max) {
		$data = array('type' => 'fixed', 'type_value' => 15);
		if (!is_null($min)) $data['min'] = $min;
		if (!is_null($max)) $data['max'] = $max;
		$json = json_encode($data);
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', $productId);
		$this->resource->postAction($request)->getData(true);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_Conflict
	 * @expectedExceptionCode 409
	 */
	public function testPostMaxLessThanMinError() {
		$json = json_encode(array('type' => 'fixed', 'type_value' => 5, 'min' => 5, 'max' => 1));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_Conflict
	 * @expectedExceptionCode 409
	 */
	public function testPostBothMaxMinUnboundError() {
		$json = json_encode(array('type' => 'fixed', 'type_value' => 5, 'min' => 0, 'max' => 0));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	public function testPostSuccess()
	{
		$json = json_encode(array('type' => 'fixed', 'type_value' => 15, 'min' => 5, 'max' => 10));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$discountRule = $this->resource->postAction($request)->getData(true);
		$this->assertNotEmpty($discountRule);

		$this->assertEquals('fixed', $discountRule['type']);
		$this->assertEquals(15, $discountRule['type_value']);
		$this->assertEquals(5, $discountRule['min']);
		$this->assertEquals(10, $discountRule['max']);
		$this->assertEquals(1, $discountRule['product_id']);

		DiscountRule::find($discountRule['id'])->first()->delete();
	}

	public function testPutActionSuccess()
	{
		$discountRules = $this->getIdentityTestCases();
		$discountRule = $discountRules[0][2];

		$json = json_encode(array('type' => 'percent', 'type_value' => 15, 'min' => 2, 'max' => 6));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', $discountRule['product_id']);
		$request->setUserParam('discountrules', $discountRule['id']);
		$discountRuleResult = $this->resource->putAction($request)->getData(true);

		$this->assertNotEmpty($discountRuleResult);
		$this->assertEquals($discountRule['id'], $discountRuleResult['id']);
		$this->assertEquals($discountRule['product_id'], $discountRuleResult['product_id']);
		$this->assertEquals('percent', $discountRuleResult['type']);
		$this->assertEquals(15, $discountRuleResult['type_value']);
		$this->assertEquals(2, $discountRuleResult['min']);
		$this->assertEquals(6, $discountRuleResult['max']);

		// restore state
		$resetDiscountRule = new DiscountRule();
		$resetDiscountRule->setId($discountRule['id']);
		$resetDiscountRule->setProductId($discountRule['product_id']);
		$resetDiscountRule->setDiscountType($discountRule['type']);
		$resetDiscountRule->setDiscountAmount($discountRule['type_value']);
		$resetDiscountRule->setQuantityMin($discountRule['min']);
		$resetDiscountRule->setQuantityMax($discountRule['max']);
		$resetDiscountRule->save();
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testPutActionFail()
	{
		$json = json_encode(array('type' => 'percent', 'type_value' => 15, 'min' => 2, 'max' => 6));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$request->setUserParam('discountrules', 999999999);
		$this->resource->putAction($request)->getData(true);
	}

	public function testDeleteActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$discountToDelete = new DiscountRule();
		$discountToDelete->setProductId(1);
		$discountToDelete->setDiscountType('fixed');
		$discountToDelete->setDiscountAmount(5);
		$discountToDelete->setQuantityMin(10);
		$discountToDelete->setQuantityMax(20);
		$discountToDelete->save();

		$this->assertEquals(1, DiscountRule::find($discountToDelete->getId())->count());

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', 1);
		$request->setUserParam('discountrules', $discountToDelete->getId());
		$this->resource->deleteAction($request);

		$this->assertEquals(0, DiscountRule::find($discountToDelete->getId())->count());
	}

	public function testDeleteAllForAGivenProductSuccess()
	{
		$existingCount = DiscountRule::find()->count();
		$this->assertGreaterThan(0, $existingCount);

		$productId = 20;
		$numToDelete = 4;
		for ($i = 0; $i < $numToDelete; $i++) {
			$dr = new DiscountRule();
			$dr->setQuantityMin($i * 5);
			$dr->setQuantityMin($i * 5 + 3);
			$dr->setDiscountAmount($i + 5);
			$dr->setDiscountType('price');
			$dr->setProductId($productId);
			$dr->save();
		}

		$this->assertEquals($existingCount + $numToDelete, DiscountRule::find()->count());

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', $productId);
		$this->resource->deleteAction($request);

		$this->assertEquals($existingCount, DiscountRule::find()->count());
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testDeleteActionFail()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', 1);
		$request->setUserParam('discountrules', 999999999);
		$this->resource->deleteAction($request);
	}
}