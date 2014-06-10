<?php

use Store\Product\CustomField;

class Integration_Store_Api_Version_2_Resource_Products_CustomFields extends Interspire_IntegrationTest
{
	/** @var $resource Store_Api_Version_2_Resource_Products_Customfields */
	private $resource = null;

	public static function setUpBeforeClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('product_customfields');
		Interspire_DataFixtures::getInstance()->loadData('product_customfields');
	}

	public static function tearDownAfterClass()
	{
		Interspire_DataFixtures::getInstance()->removeData('product_customfields');
	}

	public function setUp()
	{
		$this->resource = new Store_Api_Version_2_Resource_Products_Customfields();
	}

	public function tearDown()
	{
		$this->resource = null;
	}

	public function getIdentityTestCases()
	{
		return array(
			array(1, 11, array("id" => 1, "product_id" => 11, "name" => "foo", "text" => "bar")),
			array(2, 12, array("id" => 2, "product_id" => 12, "name" => "foo", "text" => "baz")),
			array(3, 13, array("id" => 3, "product_id" => 13, "name" => "foo", "text" => "qux")),
			array(4, 13, array("id" => 4, "product_id" => 13, "name" => "foz", "text" => "qux")),
			array(5, 13, array("id" => 5, "product_id" => 13, "name" => "fuz", "text" => "qux")),
		);
	}

	/**
	 * @dataProvider getIdentityTestCases
	 */
	public function testGetIdentity($id, $product_id, $expected_response)
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('customfields', $id);
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
		$request->setUserParam('customfields', 999999999);
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
	public function testPostMissingName()
	{
		$json = json_encode(array('text' => 'bar'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostMissingValue()
	{
		$json = json_encode(array('name' => 'bar'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostMissingProductId()
	{
		$json = json_encode(array('name' => 'foo', 'text' => 'bar'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$this->resource->postAction($request);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_MethodNotFound
	 * @expectedExceptionCode 405
	 */
	public function testPostToIdError()
	{
		$json = json_encode(array('name' => 'foo', 'text' => 'bar'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$request->setUserParam('customfields', 1);
		$this->resource->postAction($request);
	}

	public function testPostSuccess()
	{
		$json = json_encode(array('name' => 'foo', 'text' => 'bar'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$customField = $this->resource->postAction($request)->getData(true);
		$this->assertNotEmpty($customField);

		$this->assertEquals('foo', $customField['name']);
		$this->assertEquals('bar', $customField['text']);

		CustomField::find($customField['id'])->first()->delete();
	}

	public function testPutActionSuccess()
	{
		$customFields = $this->getIdentityTestCases();
		$customField = $customFields[0][2];

		$json = json_encode(array('name' => 'testPutName', 'text' => 'testPutText'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', $customField['product_id']);
		$request->setUserParam('customfields', $customField['id']);
		$customFieldResult = $this->resource->putAction($request)->getData(true);
		$this->assertNotEmpty($customFieldResult);
		$this->assertEquals($customField['id'], $customFieldResult['id']);
		$this->assertEquals($customField['product_id'], $customFieldResult['product_id']);
		$this->assertEquals('testPutName', $customFieldResult['name']);
		$this->assertEquals('testPutText', $customFieldResult['text']);

		// restore state
		$restoreCf = new CustomField();
		$restoreCf->setId($customField['id']);
		$restoreCf->setProductId($customField['product_id']);
		$restoreCf->setName($customField['name']);
		$restoreCf->setValue($customField['text']);
		$restoreCf->save();
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testPutActionFail()
	{
		$json = json_encode(array('name' => 'testPutName', 'text' => 'testPutText'));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('products', 1);
		$request->setUserParam('customfields', 999999999);
		$this->resource->putAction($request)->getData(true);
	}

	public function testDeleteActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$cfToDelete = new CustomField();
		$cfToDelete->setProductId(1);
		$cfToDelete->setName('deleteName');
		$cfToDelete->setValue('deleteValue');
		$cfToDelete->save();

		$this->assertEquals(1, CustomField::find($cfToDelete->getId())->count());

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', 1);
		$request->setUserParam('customfields', $cfToDelete->getId());
		$this->resource->deleteAction($request);

		$this->assertEquals(0, CustomField::find($cfToDelete->getId())->count());
	}

	public function testDeleteAllForAGivenProductSuccess()
	{
		$existingCount = CustomField::find()->count();
		$this->assertGreaterThan(0, $existingCount);

		$productId = 20;
		$numToDelete = 4;
		for ($i = 0; $i < $numToDelete; $i++) {
			$cf = new CustomField();
			$cf->setName('name_'.$i);
			$cf->setValue('value_'.$i);
			$cf->setProductId($productId);
			$cf->save();
		}

		$this->assertEquals($existingCount + $numToDelete, CustomField::find()->count());

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', $productId);
		$this->resource->deleteAction($request);

		$this->assertEquals($existingCount, CustomField::find()->count());
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testDeleteActionFail()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('products', 1);
		$request->setUserParam('customfields', 999999999);
		$this->resource->deleteAction($request);
	}

}