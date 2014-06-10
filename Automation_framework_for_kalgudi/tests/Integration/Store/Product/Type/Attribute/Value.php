<?php

require_once dirname(__FILE__) . '/../../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Type_Attribute_Value extends ModelLike_TestCase
{
	protected $_attribute;
	protected $_attributeValue;
	protected $_productType;
	protected $_productTypeAttribute;

	public function setUp ()
	{
		parent::setUp();

		$this->_attribute = new Store_Attribute;
		$this->_attribute
			->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($this->_attribute->save(), "failed to setUp Store_Attribute");

		$this->_attributeValue = new Store_Attribute_Value;
		$this->_attributeValue
			->setAttributeId($this->_attribute->getId())
			->setLabel('foo');
		$this->assertTrue($this->_attributeValue->save(), "failed to setUp Store_Attribute_Value");

		$this->_productType = new Store_Product_Type;
		$this->_productType->setName('foo');
		$this->assertTrue($this->_productType->save(), "failed to setUp Store_Product_Type");

		$this->_productTypeAttribute = new Store_Product_Type_Attribute;
		$this->_productTypeAttribute
			->setProductTypeId($this->_productType->getId())
			->setAttributeId($this->_attribute->getId());
		$this->assertTrue($this->_productTypeAttribute->save(), "failed to setUp Store_Product_Type_Attribute");
	}

	public function tearDown ()
	{
		$this->_productTypeAttribute->delete();
		$this->_productType->delete();
		$this->_attributeValue->delete();
		$this->_attribute->delete();

		parent::tearDown();
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		return array(
			array('getAttributeValue'),
		);
	}

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
		$model = new Store_Product_Type_Attribute_Value;
		$model->setProductTypeAttributeId($this->_productTypeAttribute->getId())
			->setAttributeValueId($this->_attributeValue->getId());

		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'attribute_value_id';
	}

	protected function _getFindSmokeSetPattern ()
	{
		return $this->_attributeValue->getId();
	}

	protected function _getFindSmokeLikePattern ()
	{
		return $this->_attributeValue->getId() . '%';
	}

	public function testSetGetProductTypeAttributeId ()
	{
		$model = new Store_Product_Type_Attribute_Value;
		$model->setProductTypeAttributeId('2');
		$this->assertSame(2, $model->getProductTypeAttributeId());
	}

	public function testFindEagerLoadsAttributeValue ()
	{
		$this->markTestSkipped();

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type_Attribute_Value");
		$model = Store_Product_Type_Attribute_Value::find('`[|PREFIX|]product_type_attribute_values`.`id` = ' . $model->getId())->first();
		$this->assertInstanceOf('Store_Product_Type_Attribute_Value', $model, "failed to find() saved Store_Product_Type_Attribute_Value");
		$this->_attributeValue->delete();
		$attributeValue = $model->getAttributeValue();
		$this->assertInstanceOf('Store_Attribute_Value', $attributeValue);
	}
}
