<?php

require_once dirname(__FILE__) . '/../../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Attribute_Combination_Value extends ModelLike_TestCase
{
	protected $_productAttributeCombination;
	protected $_attribute;
	protected $_productAttribute;
	protected $_attributeValue;

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getAttributeValueId';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setAttributeValueId';
	}

	protected function _getCrudSmokeValue1 ()
	{
		return $this->_attributeValue->getId();
	}

	protected function _getCrudSmokeValue2 ()
	{
		return $this->_attributeValue->getId();
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Attribute_Combination_Value;
		$model->setProductAttributeCombinationId($this->_productAttributeCombination->getId())
			->setProductAttributeId($this->_productAttribute->getId())
			->setAttributeValueId($this->_attributeValue->getId());
		return $model;
	}

	protected function _getFindSmokeGetMethod ()
	{
		// skip find() check for this bridge table as it's covered elsewhere and hacking a pattern for it isn't worth it
		return false;
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		return array(
			array('getAttributeValue'),
		);
	}

	public function setUp ()
	{
		parent::setUp();

		$this->_productAttributeCombination = new Store_Product_Attribute_Combination;
		$this->assertTrue($this->_productAttributeCombination->setSku(rand(10000000,99999999))->setProductId(28)->save(), "failed to save Store_Product_Attribute_Combination");

		$this->_attribute = new Store_Attribute;
		$this->assertTrue($this->_attribute->setName('foo')->setType(new Store_Attribute_Type_Configurable_Entry_Text)->save(), "failed to save Store_Attribute");

		$this->_productAttribute = new Store_Product_Attribute;
		$this->assertTrue($this->_productAttribute->setProductId(28)->setAttributeId($this->_attribute->getId())->save(), "failed to save Store_Product_Attribute");

		$this->_attributeValue = new Store_Attribute_Value;
		$this->assertTrue($this->_attributeValue->setAttributeId($this->_attribute->getId())->setLabel('bar')->save(), "failed to save Store_Attribute_Value");
	}

	public function tearDown ()
	{
		if (is_object($this->_attribute)) {
			$this->_attribute->delete();
		}

		if (is_object($this->_productAttributeCombination)) {
			$this->_productAttributeCombination->delete();
		}

		parent::tearDown();
	}

	public function testSetGetProductAttributeCombinationId ()
	{
		$model = new Store_Product_Attribute_Combination_Value;
		$this->assertSame($model, $model->setProductAttributeCombinationId('2'), "set return value mismatch");
		$this->assertSame(2, $model->getProductAttributeCombinationId(), "get return value mismatch");
	}

	public function testGetAttributeValue ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Combination_Value");

		$attributeValue = $model->getAttributeValue();
		$this->assertInstanceOf('Store_Attribute_Value', $attributeValue, "getAttributeValue return value mismatch");
		$this->assertSame($attributeValue, $model->getAttributeValue(), "internal caching failure");
		$this->assertEquals($model->getAttributeValueId(), $attributeValue->getId(), "attribute value id mismatch");
	}
}
