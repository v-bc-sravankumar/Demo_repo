<?php

abstract class Unit_Lib_Store_Attribute_View extends Interspire_IntegrationTest
{
	abstract public function getTestInstance ();

	private function _getTestProductAttribute ()
	{
		// a properly configured product_attribute is required to render a view
		$attributeType = new Store_Attribute_Type_Configurable_PickList_Set;
		$attributeType
			->setView($this->getTestInstance());

		$attribute = new Store_Attribute;
		$attribute
			->setType($attributeType);

		$this->assertTrue($attribute->save(), "failed to save attribute");

		$value = new Store_Attribute_Value;
		$value
			->setAttributeId($attribute->getId())
			->setSortOrder(0)
			->setLabel('one');

		$this->assertTrue($value->save(), "failed to save value (1)");

		$value = new Store_Attribute_Value;
		$value
			->setAttributeId($attribute->getId())
			->setSortOrder(1)
			->setLabel('two');

		$this->assertTrue($value->save(), "failed to save value (2)");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute
			->setProductId(28)
			->setAttributeId($attribute->getId());

		$this->assertTrue($productAttribute->save(), "failed to save product attribute");

		return $productAttribute;
	}

	public $productAttribute;

	public $attributeView;

	public function setUp ()
	{
		parent::setUp();
		$this->productAttribute = $this->_getTestProductAttribute();
		$this->attributeView = $this->productAttribute->getAttribute()->getType()->getView();
	}

	public function tearDown ()
	{
		if (is_object($this->productAttribute)) {
			$attribute = $this->productAttribute->getAttribute();
			if (is_object($attribute)) {
				$this->assertTrue($this->productAttribute->getAttribute()->delete(), "failed to delete attribute");
			}
		}
		parent::tearDown();
	}

	public function testGetJqueryPluginName ()
	{
		$string = $this->attributeView->getJqueryPluginName();
		$this->assertInternalType('string', $string);
		$this->assertFalse(empty($string), "string is empty");
		$this->assertContains('productOptionView', $string);
	}

	public function testGetClass ()
	{
		$string = $this->attributeView->getClass();
		$this->assertInternalType('string', $string);
		$this->assertFalse(empty($string), "string is empty");
	}

	public function testGetPublicClassName ()
	{
		$string = $this->attributeView->getPublicClassName();
		$this->assertInternalType('string', $string);
		$this->assertFalse(empty($string), "string is empty");
	}

	public function testGetName ()
	{
		$string = $this->attributeView->getName();
		$this->assertInternalType('string', $string);
		$this->assertFalse(empty($string), "string is empty");
	}

	public function testGetTip ()
	{
		$string = $this->attributeView->getTip();
		$this->assertInternalType('string', $string);
	}
}
