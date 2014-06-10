<?php

require_once dirname(__FILE__) . '/../../ModelLike_TestCase.php';

class Unit_Lib_Store_Order_Product_Attribute extends ModelLike_TestCase
{
	public function setUp ()
	{
		parent::setUp();

		$orderProduct = array(
			"ordprodsku" => "foo",
			"ordprodname" => "bar",
		);

		$this->_orderProductId = $this->fixtures->InsertQuery("order_products", $orderProduct);
		$this->assertGreaterThan(0, $this->_orderProductId, "failed to insert order_products record");
	}

	public function tearDown ()
	{
		parent::tearDown();
	}

	public function _getCrudSmokeGetMethod ()
	{
		return 'getDisplayName';
	}

	public function _getCrudSmokeSetMethod ()
	{
		return 'setDisplayName';
	}

	protected function _getFindSmokeColumn ()
	{
		return 'display_name';
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Order_Product_Attribute;
		$model->setOrderProductId($this->_orderProductId);
		return $model;
	}

	public function testSetGetOrderProductId ()
	{
		$model = new Store_Order_Product_Attribute;
		$this->assertSame($model, $model->setOrderProductId('2'), "set return value mismatch");
		$this->assertSame(2, $model->getOrderProductId(), "get return value mismatch");
	}

	public function testSetGetAttributeId ()
	{
		$model = new Store_Order_Product_Attribute;
		$this->assertSame($model, $model->setAttributeId('2'), "set return value mismatch");
		$this->assertSame(2, $model->getAttributeId(), "get return value mismatch");
	}

	public function testSetGetAttributeTypeClassName ()
	{
		$model = new Store_Order_Product_Attribute;
		$this->assertSame($model, $model->setAttributeTypeClassName(2), "set return value mismatch");
		$this->assertSame('2', $model->getAttributeTypeClassName(), "get return value mismatch");
	}

	public function testSetGetValidatedValue ()
	{
		$model = new Store_Order_Product_Attribute;
		$this->assertSame($model, $model->setValidatedValue(2), "set return value mismatch");
		$this->assertSame('2', $model->getValidatedValue(), "get return value mismatch");
	}

	public function testSetGetDisplayValue ()
	{
		$model = new Store_Order_Product_Attribute;
		$this->assertSame($model, $model->setDisplayValue(2), "set return value mismatch");
		$this->assertSame('2', $model->getDisplayValue(), "get return value mismatch");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
