<?php

require_once dirname(__FILE__) . '/../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Type_Attribute extends ModelLike_TestCase
{
	protected $_productType;

	protected $_attribute;

	public function setUp ()
	{
		parent::setUp();

		$this->_productType = new Store_Product_Type;
		$this->_productType
			->setName('product_type test parent');
		$this->assertTrue($this->_productType->save(), "failed to create product_type test parent");

		$this->_attribute = new Store_Attribute;
		$this->_attribute->setName('attribute_value_parent_test')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($this->_attribute->save(), "failed to create attribute_value_parent_test");
	}

	public function tearDown ()
	{
		$this->_productType->delete();
		$this->_attribute->delete();

		parent::tearDown();
	}

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getSortOrder';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setSortOrder';
	}

	protected function _getCrudSmokeValue1 ()
	{
		return 2;
	}

	protected function _getCrudSmokeValue2 ()
	{
		return 3;
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Type_Attribute;
		$model->setProductTypeId($this->_productType->getId())
			->setAttributeId($this->_attribute->getId())
			->setSortOrder($this->_getCrudSmokeValue1());

		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'sort_order';
	}

	protected function _getFindSmokeSetPattern ()
	{
		return '10%s';
	}

	protected function _getFindSmokeLikePattern ()
	{
		return '10%';
	}

	public function testSetGetRequired ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setRequired(1);
		$this->assertSame(true, $model->getRequired());
		$model->setRequired(0);
		$this->assertSame(false, $model->getRequired());
	}

	public function testSetGetDisplayName ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setDisplayName(1234);
		$this->assertSame('1234', $model->getDisplayName());
	}

	public function testGetAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");
		$this->assertInstanceOf('Store_Attribute', $model->getAttribute());
		$this->assertEquals($this->_attribute->getId(), $model->getAttribute()->getId());
	}

	public function testAddValue ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$productTypeAttributeValue = $model->addValue($attributeValue);
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $productTypeAttributeValue, "addValue failed");
		$this->assertSame($attributeValue->getId(), $productTypeAttributeValue->getAttributeValueId(), "attribute_value_id mismatch");
	}

	public function testAddValueWithoutSaveFails ()
	{
		$model = $this->_getCrudSmokeInstance();

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$productTypeAttributeValue = $model->addValue($attributeValue);
		$this->assertFalse($productTypeAttributeValue, "addValue worked but should have failed");
	}

	public function testAddValueWithoutSavedAttributeValueFails ()
	{
		$this->markTestSkipped(); // skipping this for BC only due to missing foreign keys

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo');

		$productTypeAttributeValue = $model->addValue($attributeValue);
		$this->assertFalse($productTypeAttributeValue, "addValue worked but should have failed");
	}

	public function testDeleteCascadesToProductTypeAttributeValues ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$productTypeAttributeValue = $model->addValue($attributeValue);
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $productTypeAttributeValue, "addValue failed");
		$this->assertTrue($productTypeAttributeValue->load(), "failed to add-load Store_Product_Type_Attribute_Value");
		$this->assertTrue($model->delete(), "failed to delete Store_Product_Type_Attribute");
		$this->assertFalse($productTypeAttributeValue->load(), "loading Store_Product_Type_Attribute_Value worked but should have failed");
	}

	public function testDeleteCascadesToProductAttributesForPreloadedInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute
			->setProductId(28)
			->setAttributeId($this->_attribute->getId())
			->setProductTypeAttributeId($model->getId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");
		$this->assertTrue($productAttribute->load(), "failed to save-load Store_Product_Attribute");
		$this->assertTrue($model->delete(), "failed to delete Store_Product_Type_Attribute");
		$this->assertFalse($productAttribute->load(), "loading Store_Product_Attribute worked but should have failed");
	}

	public function testDeleteCascadesToProductAttributesForSparseInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");
		$id = $model->getId();

		$model = new Store_Product_Type_Attribute;
		$model->setId($id);

		$productAttribute = new Store_Product_Attribute;
		$productAttribute
			->setProductId(28)
			->setAttributeId($this->_attribute->getId())
			->setProductTypeAttributeId($model->getId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");
		$this->assertTrue($productAttribute->load(), "failed to save-load Store_Product_Attribute");
		$this->assertTrue($model->delete(), "failed to delete Store_Product_Type_Attribute");
		$this->assertFalse($productAttribute->load(), "loading Store_Product_Attribute worked but should have failed");
	}

	public function testGetAttributeValuesWithUnsavedTypeFails ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertFalse($model->getAttributeValues());
	}

	public function testGetAttributeValuesWithNoTypeAttributeValues ()
	{
		// add the attribute values first before saving $model otherwise the values will propagate

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo')
			->setSortOrder(1);
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('bar')
			->setSortOrder(0);
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$attributeValues = $model->getAttributeValues();
		$this->assertEquals(2, $attributeValues->count(), "count() mismatch");

		$actual = array();
		foreach ($attributeValues as $attributeValue) {
			$this->assertInstanceOf('Store_Attribute_Value', $attributeValue);
			$actual[] = $attributeValue->getLabel();
		}

		$expected = array(
			'bar',
			'foo',
		);

		$this->assertEquals($expected, $actual);
	}

	public function testGetAttributeValuesWithTypeAttributeValues ()
	{
		// add the attribute values first before saving $model otherwise the values will propagate

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo')
			->setSortOrder(1);
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('bar')
			->setSortOrder(0);
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $model->addValue($attributeValue), "addValue() failed");

		$attributeValues = $model->getAttributeValues();
		$this->assertEquals(1, $attributeValues->count(), "count() mismatch");

		$actual = array();
		foreach ($attributeValues as $attributeValue) {
			$this->assertInstanceOf('Store_Attribute_Value', $attributeValue);
			$actual[] = $attributeValue->getLabel();
		}

		$expected = array(
			'bar',
		);

		$this->assertEquals($expected, $actual);
	}

	public function testCloneToProductAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model
			->setDisplayName('foo')
			->setRequired(true);
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$productAttribute = $model->cloneToProductAttribute(28);

		$this->assertInstanceOf('Store_Product_Attribute', $productAttribute);
		$this->assertNull($productAttribute->getId());
		$this->assertSame(28, $productAttribute->getProductId());

		$this->assertSame($model->getSortOrder(), $productAttribute->getSortOrder(), "sort_order mismatch");
		$this->assertSame($model->getRequired(), $productAttribute->getRequired(), "required mismatch");
		$this->assertSame($model->getDisplayName(), $productAttribute->getDisplayName(), "display_name mismatch");
		$this->assertSame($model->getAttributeId(), $productAttribute->getAttributeId(), "attribute_id mismatch");

		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute after clone from Store_Product_Type_Attribute");
	}

	public function testClonedProductTypeAttributeInstanceReturnsAttributeValuesFromOriginalInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($this->_attribute->getId())
			->setLabel('foo');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$productTypeAttributeValue = $model->addValue($attributeValue);
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $productTypeAttributeValue, "addValue failed");
		$this->assertSame($attributeValue->getId(), $productTypeAttributeValue->getAttributeValueId(), "attribute_value_id mismatch");

		$clone = $model->copy();
		$subClone = $clone->getProductTypeAttributeValues()->first();
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $subClone, "invalid result from clone's getProductTypeAttributeValues: " . $clone->getDb()->getErrorMsg());
		$this->assertEquals(null, $subClone->getId(), "product_type_attribute_value id mismatch");
		$this->assertEquals($subClone->getAttributeValueId(), $attributeValue->getId(), "attribute_value id mismatch");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
