<?php

require_once dirname(__FILE__) . '/../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Attribute_Combination extends ModelLike_TestCase
{
	protected function _getCrudSmokeGetMethod ()
	{
		return 'getStockLevel';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setStockLevel';
	}

	protected function _getCrudSmokeValue1 ()
	{
		return 12;
	}

	protected function _getCrudSmokeValue2 ()
	{
		return 7;
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Attribute_Combination;
		$model->setSku(rand(1000000, 9999999))
			->setProductId(28)
			->setStockLevel($this->_getCrudSmokeValue1());
		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'stock_level';
	}

	protected function _getFindSmokeSetPattern ()
	{
		return '10%s';
	}

	protected function _getFindSmokeLikePattern ()
	{
		return '10%';
	}

	public function testDeleteCascadesToProductAttributeCombinationValues ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Combination");

		$attribute = new Store_Attribute;
		$attribute
			->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setProductId($model->getProductId())
			->setAttributeId($attribute->getId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue
			->setAttributeId($attribute->getId())
			->setLabel('bar');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$combinationValue = new Store_Product_Attribute_Combination_Value;
		$combinationValue
			->setProductAttributeCombinationId($model->getId())
			->setProductAttributeId($productAttribute->getId())
			->setAttributeValueId($attributeValue->getId());

		$this->assertTrue($combinationValue->save(), "failed to save Store_Product_Attribute_Combination_Value");
		$this->assertTrue($combinationValue->load(), "failed to save-load Store_Product_Attribute_Combination_Value");

		$this->assertTrue($model->delete(), "failed to delete Store_Product_Attribute_Combination");

		$this->assertFalse($combinationValue->load(), "loading Store_Product_Attribute_Combination_Value worked but should have failed");
	}

	public function testSetGetProductId ()
	{
		$model = new Store_Product_Attribute_Combination;
		$value = 28;
		$this->assertSame($model, $model->setProductId($value));
		$this->assertSame($value, $model->getProductId());
	}

	public function testSetGetSku ()
	{
		$model = new Store_Product_Attribute_Combination;
		$value = 'foo';
		$this->assertSame($model, $model->setSku($value));
		$this->assertSame($value, $model->getSku());
	}

	public function testSetGetUpc ()
	{
		$model = new Store_Product_Attribute_Combination;
		$value = '12345678901';
		$this->assertSame($model, $model->setUpc($value));
		$this->assertSame($value, $model->getUpc());
	}

	public function testSetGetStockLevel ()
	{
		$model = new Store_Product_Attribute_Combination;
		$value = 5;
		$this->assertSame($model, $model->setStockLevel($value));
		$this->assertSame($value, $model->getStockLevel());
	}

	public function testSetGetLowStockLevel ()
	{
		$model = new Store_Product_Attribute_Combination;
		$value = 3;
		$this->assertSame($model, $model->setLowStockLevel($value));
		$this->assertSame($value, $model->getLowStockLevel());
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
