<?php

require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

class Test_Store_Attribute_ValueData_Dummy extends Store_Attribute_ValueData
{
	public function processPostedValue (Store_Attribute $attribute, Store_Attribute_Value $attributeValue, $postedValue = array())
	{
		return true;
	}
}

class Unit_Lib_Store_Attribute_Value extends ModelLike_TestCase
{
	protected $_attribute;

	public function setUp ()
	{
		parent::setUp();
		$this->_attribute = new Store_Attribute;
		$this->_attribute->setName('attribute_value_parent_test')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($this->_attribute->save(), "failed to create attribute_value_parent_test");
	}

	public function tearDown ()
	{
		$this->_attribute->delete();
	}

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getLabel';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setLabel';
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Attribute_Value;
		$model->setLabel($this->_getCrudSmokeValue1())
			->setAttributeId($this->_attribute->getId())
			->setValueData(new Test_Store_Attribute_ValueData_Dummy);

		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'label';
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		return array(
			array('getAttribute'),
			array('getValueData'),
		);
	}

	public function testSetGetSortOrder ()
	{
		$model = new Store_Attribute_Value;
		$model->setSortOrder('2');
		$this->assertEquals(2, $model->getSortOrder());
	}

	public function testSetGetValueData ()
	{
		$valueData = new Store_Attribute_ValueData_Swatch_OneColour;
		$model = new Store_Attribute_Value;
		$model->setValueData($valueData);
		$this->assertSame($valueData, $model->getValueData());
	}

	public function testNewValuesDoNotPropogateToUncustomisedProductTypeAttributes ()
	{
		$type = new Store_Product_Type;
		$type->setName('foo');
		$this->assertTrue($type->save());

		$typeAttribute = new Store_Product_Type_Attribute;
		$typeAttribute->setAttributeId($this->_attribute->getId())
			->setProductTypeId($type->getId());
		$this->assertTrue($typeAttribute->save());

		$values = Store_Product_Type_Attribute_Value::find("product_type_attribute_id = " . $typeAttribute->getId());
		$this->assertEquals(0, $values->count(), "found some product_type_attribute_values but should be none");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save());

		$this->assertEquals(0, $values->count(), "found some product_type_attribute_values but should be none");
	}

	public function testNewValuesPropogateToCustomisedProductTypeAttributes ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute_Value");

		$type = new Store_Product_Type;
		$type->setName('foo');
		$this->assertTrue($type->save(), "failed to save Store_Product_Type");

		$typeAttribute = new Store_Product_Type_Attribute;
		$typeAttribute->setAttributeId($this->_attribute->getId())
			->setProductTypeId($type->getId());
		$this->assertTrue($typeAttribute->save(), "failed to save Store_Product_Type_Attribute");

		$typeAttributeValue = $typeAttribute->addValue($model);
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $typeAttributeValue, "addValue failed for Store_Product_Type_Attribute");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute_Value");

		$values = Store_Product_Type_Attribute_Value::find("product_type_attribute_id = " . $typeAttribute->getId());
		$this->assertEquals(2, $values->count(), "product_type_attribute_values count mismatch");
	}

	public function testDeleteCascadesToProductTypeAttributeValues ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save());

		$type = new Store_Product_Type;
		$type->setName('foo');
		$this->assertTrue($type->save());

		$typeAttribute = new Store_Product_Type_Attribute;
		$typeAttribute->setAttributeId($this->_attribute->getId())
			->setProductTypeId($type->getId());
		$this->assertTrue($typeAttribute->save());

		$typeAttributeValue = $typeAttribute->addValue($model);
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $typeAttributeValue, "addValue failed for Store_Product_Type_Attribute");

		$values = Store_Product_Type_Attribute_Value::find("product_type_attribute_id = " . $typeAttribute->getId());
		$this->assertEquals(1, $values->count(), "product_type_attribute_values count mismatch");

		$this->assertTrue($model->delete(), "failed to delete Store_Attribute_Value");

		$this->assertEquals(0, $values->count(), "product_type_attribute_values count mismatch");
	}

	public function testDeleteCascadesToProductAttributeRuleConditions ()
	{
		$rule = new Store_Product_Attribute_Rule;
		$rule->setProductId(1);
		$this->assertTrue($rule->save(), "failed to save Store_Product_Attribute_Rule");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute_Value");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute
			->setProductId($rule->getProductId())
			->setAttributeId($model->getAttributeId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$condition = new Store_Product_Attribute_Rule_Condition;
		$condition->setProductAttributeRuleId($rule->getId())
			->setProductAttributeId($productAttribute->getId())
			->setAttributeValueId($model->getId());
		$this->assertTrue($condition->save(), "failed to save Store_Product_Attribute_Rule_Condition: " . $condition->getDb()->getErrorMsg());
		$this->assertTrue($condition->load(), "failed to save-load Store_Product_Attribute_Rule_Condition: " . $condition->getDb()->getErrorMsg());

		$this->assertTrue($model->delete(), "failed to delete Store_Attribute_Value");
		$this->assertFalse($condition->load(), "loading Store_Product_Attribute_Rule_Condition worked but should have failed");
	}

	public function testDeleteCascadesToProductAttributeCombinationValues ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute_Value");

		$combination = new Store_Product_Attribute_Combination;
		$combination->setProductId(1);
		$this->assertTrue($combination->save(), "failed to save Store_Product_Attribute_Combination");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setProductId(1)
			->setAttributeId($model->getAttributeId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$combinationValue = new Store_Product_Attribute_Combination_Value;
		$combinationValue->setProductAttributeCombinationId($combination->getId())
			->setProductAttributeId($productAttribute->getId())
			->setAttributeValueId($model->getId());
		$this->assertTrue($combinationValue->save(), "failed to save Store_Product_Attribute_Combination_Value: " . $combinationValue->getDb()->getErrorMsg());
		$this->assertTrue($combinationValue->load(), "failed to save-load Store_Product_Attribute_Combination_Value");

		$this->assertTrue($model->delete(), "failed to delete Store_Attribute_Value");
		$this->assertFalse($combinationValue->load(), "loading Store_Product_Attribute_Combination_Value worked but should have failed");
	}

	public function testDeleteCascadesToImageFiles ()
	{
		// this test should still be needed when the attribute value is a swatch but it's currently not valid
		$this->markTestSkipped();

		$model = $this->_getCrudSmokeInstance();

		$image = 'Unit_Lib_Store_Attribute_Value__Image.jpg';
		$thumb = 'Unit_Lib_Store_Attribute_Value__Thumb.jpg';
		$zoom = 'Unit_Lib_Store_Attribute_Value__Zoom.jpg';

		$model->setImagePath($image)
			->setThumbPath($thumb)
			->setZoomPath($zoom);

		$this->assertTrue($model->save());

		$this->assertTrue(touch($model->getImagePath(true)));
		$this->assertTrue(touch($model->getThumbPath(true)));
		$this->assertTrue(touch($model->getZoomPath(true)));

		$this->assertTrue($model->delete());

		$this->assertFalse(file_exists($model->getImagePath(true)));
		$this->assertFalse(file_exists($model->getThumbPath(true)));
		$this->assertFalse(file_exists($model->getZoomPath(true)));
	}

	public function testGetAttributeFailsWithoutAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save());
		$this->assertTrue($this->_attribute->delete());
		$this->assertFalse($model->getAttribute());
	}
}
