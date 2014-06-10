<?php

require_once dirname(__FILE__) . '/ModelLike_TestCase.php';

class Unit_Lib_Store_Attribute extends ModelLike_TestCase
{
	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Attribute;
		$model->setName($this->_getCrudSmokeValue1())
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);

		return $model;
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		return array(
			array('getType'),
		);
	}

	/**
	 * @covers Store_Attribute::setDisplayName
	 * @covers Store_Attribute::getDisplayName
	 */
	public function testGetSetDisplayName ()
	{
		$model = new Store_Attribute;
		$model->setDisplayName('foo');
		$this->assertEquals('foo', $model->getDisplayName());
	}

	/**
	 * @covers Store_Attribute::_beforeInsert
	 * @covers Store_Attribute::_beforeUpdate
	 * @covers Store_Attribute::_beforeInsertOrUpdate
	 * @covers Store_Attribute::getType
	 */
	public function testSaveWithoutTypeFails ()
	{
		$model = new Store_Attribute;
		$model->setName('foo');
		$this->assertFalse($model->getType());
		$this->assertFalse($model->save());
	}

	/**
	 * @covers Store_Attribute::_beforeDelete
	 */
	public function testDeleteCascadesToProductTypeAttributes ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute");

		$productType = new Store_Product_Type;
		$productType->setName('foo');
		$this->assertTrue($productType->save(), "failed to save Store_Product_Type");

		$productTypeAttribute = new Store_Product_Type_Attribute;
		$productTypeAttribute->setProductTypeId($productType->getId())
			->setAttributeId($model->getId());
		$this->assertTrue($productTypeAttribute->save(), "failed to save Store_Product_Type_Attribute");
		$this->assertTrue($productTypeAttribute->load(), "failed to save-load Store_Product_Type_Attribute");
		$this->assertTrue($model->delete(), "failed to delete Store_Attribute");
		$this->assertFalse($productTypeAttribute->load(), "loading Store_Product_Type_Attribute worked but should have failed");
	}

	/**
	 * @covers Store_Attribute::_beforeDelete
	 */
	public function testDeleteCascadesToProductAttributes ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setAttributeId($model->getId())
			->setProductId(28);
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");
		$this->assertTrue($productAttribute->load(), "failed to save-load Store_Product_Attribute");
		$this->assertTrue($model->delete(), "failed to delete Store_Attribute");
		$this->assertFalse($productAttribute->load(), "loading Store_Product_Attribute worked but should have failed");
	}

	/**
	 * @covers Store_Attribute::_beforeDelete
	 */
	public function testDeleteCascadesToValues ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($model->getId());
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");
		$this->assertTrue($attributeValue->load(), "failed to save-load Store_Attribute_Value");
		$this->assertTrue($model->delete(), "failed to delete Store_Attribute");
		$this->assertFalse($attributeValue->load(), "loading Store_Attribute_Value worked but should have failed");
	}

	/**
	 * @covers Store_Attribute::isValidValue
	 */
	public function testIsValidValueIsFalseForNonInt ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertFalse($model->isValidValue('abc'));
	}

	/**
	 * @covers Store_Attribute::isValidValue
	 */
	public function testIsValidValueIsFalseForInvalidValue ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute");
		$this->assertFalse($model->isValidValue(1));
	}

	/**
	 * @covers Store_Attribute::isValidValue
	 */
	public function testIsValidValueIsTrueForValidValue ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($model->getId());
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$this->assertTrue($model->isValidValue($attributeValue->getId()), "value({$attributeValue->getId()}) should be valid for attribute({$model->getId()})");
	}

	/**
	 * @covers Store_Attribute::__clone
	 * @covers Store_Attribute::getValues
	 */
	public function testClonedAttributeInstanceReturnsValuesFromOriginalInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue->setAttributeId($model->getId());
		$this->assertTrue($attributeValue->save(), "failed to save value");

		$clone = $model->copy();
		$this->assertNull($clone->getId(), "cloned product_type id mismatch");
		$subClone = $clone->getValues()->first();
		$this->assertInstanceOf('Store_Attribute_Value', $subClone);
		$this->assertNull($subClone->getId(), "subclone id was not null");
		$this->assertEquals($subClone->getAttributeId(), $model->getId(), "attribute id mismatch");
	}

	/**
	 * @covers Store_Attribute::getJson
	 */
	public function testGetJsonReturnsFalseWithoutType ()
	{
		$productAttribute = new Store_Product_Attribute;
		$model = new Store_Attribute;
		$this->assertFalse($model->getJson($productAttribute));
	}

	/**
	 * @covers Store_Attribute::getFormHtml
	 */
	public function testGetFormHtml ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setAttributeId($model->getId())
			->setProductId(28);
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$string = $model->getFormHtml($productAttribute);
		$this->assertInternalType('string', $string);
		$this->assertGreaterThan(0, strlen($string));
	}

	/**
	 * @covers Store_Attribute::getFormJavaScript
	 */
	public function testGetFormJavaScript ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setAttributeId($model->getId())
			->setProductId(28);
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$string = $model->getFormJavaScript($productAttribute);
		$this->assertInternalType('string', $string);
		$this->assertGreaterThan(0, strlen($string));
	}

	/**
	 * @covers Store_Attribute::validateEnteredValue
	 */
	public function testValidateEnteredValue ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setAttributeId($model->getId())
			->setProductId(28);
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$value = 'test';
		$result = $model->validateEnteredValue($productAttribute, $value);
		$this->assertSame($value, $result);
	}
}
