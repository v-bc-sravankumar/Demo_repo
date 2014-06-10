<?php

require_once dirname(__FILE__) . '/../../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Attribute_Rule_Condition extends ModelLike_TestCase
{
	protected $_productAttributeRule;
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
		$model = new Store_Product_Attribute_Rule_Condition;
		$model->setProductAttributeRuleId($this->_productAttributeRule->getId())
			->setProductAttributeId($this->_productAttribute->getId())
			->setAttributeValueId($this->_attributeValue->getId());
		return $model;
	}

	protected function _getFindSmokeGetMethod ()
	{
		// skip find() check for this bridge table as it's covered elsewhere and hacking a pattern for it isn't worth it
		return false;
	}

	public function setUp ()
	{
		parent::setUp();

		$this->_productAttributeRule = new Store_Product_Attribute_Rule;
		$this->assertTrue($this->_productAttributeRule->setProductId(28)->save(), "failed to save Store_Product_Attribute_Rule");

		$this->_attribute = new Store_Attribute;
		$this->assertTrue($this->_attribute->setName('foo')->setType(new Store_Attribute_Type_Configurable_Entry_Text)->save(), "failed to save Store_Attribute");

		$this->_productAttribute = new Store_Product_Attribute;
		$this->_productAttribute
			->setProductId(28)
			->setAttributeId($this->_attribute->getId());
		$this->assertTrue($this->_productAttribute->save(), "failed to save Store_Product_Attribute");

		$this->_attributeValue = new Store_Attribute_Value;
		$this->assertTrue($this->_attributeValue->setAttributeId($this->_attribute->getId())->setLabel('bar')->save(), "failed to save Store_Attribute_Value");
	}

	public function tearDown ()
	{
		$this->_attribute->delete();
		$this->_productAttributeRule->delete();
		parent::tearDown();
	}

	public function testCanSetNullValueIdToTargetUnselectedPicklist ()
	{
		// ISC-2178 regression test

		$model = $this->_getCrudSmokeInstance();
		$model->setAttributeValueId(null);
		$this->assertTrue($model->save(), "failed to save model");
		$id = $model->getId();

		$model = Store_Product_Attribute_Rule_Condition::find($id)->first();
		$this->assertInstanceOf('Store_Product_Attribute_Rule_Condition', $model, "failed to load model");
		$this->assertNull($model->getAttributeValueId());
	}

	public function testSetGetProductAttributeRuleId ()
	{
		$model = new Store_Product_Attribute_Rule_Condition;
		$this->assertSame($model, $model->setProductAttributeRuleId('2'), "set return value mismatch");
		$this->assertSame(2, $model->getProductAttributeRuleId(), "get return value mismatch");
	}

	public function testDeleteDoesNotDeleteNonEmptyDeleteRule ()
	{
		// ensures that after deleting a rule condition the rule it is connected to remains if the rule is non-empty
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(1)");

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(2)");

		// delete only the second model
		$this->assertTrue($model->delete(), "failed to delete model(2)");

		// check the rule exists
		$this->assertInstanceOf('Store_Product_Attribute_Rule', Store_Product_Attribute_Rule::find($this->_productAttributeRule->getId())->first(), "find(" . $this->_productAttributeRule->getId() . ") for rules failed");
	}

	public function testDeleteDoesDeleteEmptyRule ()
	{
		$models = array();

		// ensures that after deleting a rule condition the rule it is connected to remains if the rule is non-empty
		$models[] = $model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(1)");

		$models[] = $model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(2)");

		// delete each model
		foreach ($models as $model) {
			$this->assertTrue($model->delete(), "failed to delete model(". $model->getId() .")");
		};

		// check the rule has been deleted
		$this->assertFalse(Store_Product_Attribute_Rule::find($this->_productAttributeRule->getId())->first(), "find(" . $this->_productAttributeRule->getId() . ") for rules should have failed");
	}

	public function testDeleteDoesDeleteEmptyRuleWithManualUnitOfWork ()
	{
		$models = array();

		// ensures that after deleting a rule condition the rule it is connected to remains if the rule is non-empty
		$models[] = $model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(1)");

		$models[] = $model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save model(2)");

		$unitOfWork = new DataModel_UnitOfWork;

		// delete each model
		foreach ($models as $model) {
			$this->assertTrue($model->delete($unitOfWork), "failed to delete model(". $model->getId() .")");
		};

		// check the rule exists
		$this->assertInstanceOf('Store_Product_Attribute_Rule', Store_Product_Attribute_Rule::find($this->_productAttributeRule->getId())->first(), "find(" . $this->_productAttributeRule->getId() . ") for rules failed");

		$unitOfWork->commitWork();

		// check the rule has been deleted
		$this->assertFalse(Store_Product_Attribute_Rule::find($this->_productAttributeRule->getId())->first(), "find(" . $this->_productAttributeRule->getId() . ") for rules should have failed");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
