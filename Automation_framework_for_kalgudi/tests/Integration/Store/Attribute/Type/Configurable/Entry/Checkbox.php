<?php

/**
 * This test harness for the Checkbox type exposes the yes/no value ids for manual setting instead of having to use
 * processPostedAttributeValues...
 */
class Store_Attribute_Type_Configurable_Entry_Checkbox_Test extends Store_Attribute_Type_Configurable_Entry_Checkbox
{
	public function setYesValueId ($value)
	{
		$this->_yesValueId = (int)$value;
		return $this;
	}

	public function setNoValueId ($value)
	{
		$this->_noValueId = (int)$value;
		return $this;
	}
}

class Unit_Lib_Store_Attribute_Type_Configurable_Entry_Checkbox extends Integration_Store_Attribute_Type_Configurable
{
	protected $_prefix = 'Configurable_Entry_Checkbox_';

	protected function _getInstance ()
	{
		$instance = new Store_Attribute_Type_Configurable_Entry_Checkbox_Test;

		return $instance->setYesValueId(1)
			->setNoValueId(2);
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::setDefaultChecked
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getDefaultChecked
	 */
	public function testConfigureDefaultChecked ()
	{
		$type = $this->_getInstance();

		$type->setConfigurationSettings(
			new Store_Attribute,
			new Interspire_Request(null, array(
				$this->_prefix . 'DefaultStatus' => '0',
			))
		);

		$this->assertSame(false, $type->getDefaultChecked());

		$type->setConfigurationSettings(
			new Store_Attribute,
			new Interspire_Request(null, array(
				$this->_prefix . 'DefaultStatus' => '1',
			))
		);

		$this->assertSame(true, $type->getDefaultChecked());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::setLabel
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getLabel
	 */
	public function testConfigureLabel ()
	{
		$type = $this->_getInstance();

		$type->setConfigurationSettings(
			new Store_Attribute,
			new Interspire_Request(null, array(
				$this->_prefix . 'Label' => 123.4,
			))
		);

		$this->assertSame('123.4', $type->getLabel());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::validateEnteredValue
	 */
	public function testValidateNotRequiredPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(false);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, '0');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::validateEnteredValue
	 */
	public function testValidateRequiredPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, '1');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Required
	 */
	public function testValidateRequiredFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, '0');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getCartDisplayValue
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getYesValueId
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getNoValueId
	 */
	public function testGetCartDisplayValue ()
	{
		$type = $this->_getInstance();

		$productAttribute = new ISC_QUOTE_ITEM_PRODUCT_ATTRIBUTE;
		$productAttribute->setDisplayValue('Yes');

		$result = $type->getCartDisplayValue($productAttribute, $type->getYesValueId());
		$this->assertSame('Yes', $result);

		$result = $type->getCartDisplayValue($productAttribute, $type->getNoValueId());
		$this->assertSame('Yes', $result, 'cart display value did not return Yes when item attribute display value was set to yes');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getDisplayValue
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getYesValueId
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getNoValueId
	 */
	public function testGetDisplayValue ()
	{
		$type = $this->_getInstance();

		// will fail if the default language ever changes but I doubt it will

		$result = $type->getDisplayValue($type->getYesValueId());
		$this->assertSame('Yes', $result);

		$result = $type->getDisplayValue($type->getNoValueId());
		$this->assertSame('No', $result);
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getDefaultAttributeValueId
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getYesValueId
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::getNoValueId
	 */
	public function testGetDefaultAttributeValueId ()
	{
		$type = $this->_getInstance();

		$type->setDefaultChecked(false);
		$this->assertSame($type->getNoValueId(), $type->getDefaultAttributeValueId());

		$type->setDefaultChecked(true);
		$this->assertSame($type->getYesValueId(), $type->getDefaultAttributeValueId());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Checkbox::processPostedAttributeValues
	 */
	public function testProcessPostedAttributeValues ()
	{
		$type = new Store_Attribute_Type_Configurable_Entry_Checkbox;

		$attribute = new Store_Attribute;
		$attribute->setType($type);
		$this->assertTrue($attribute->save());

		$this->assertSame(0, $type->getYesValueId(), "'yes' is not zero");
		$this->assertSame(0, $type->getNoValueId(), "'no' is not zero");

		$request = new Interspire_Request();

		$this->assertTrue($type->processPostedAttributeValues($request, $attribute), "processPostedAttributeValues failed");

		$this->assertGreaterThan(0, $type->getYesValueId(), "'yes' value mismatch");
		$this->assertGreaterThan(0, $type->getNoValueId(), "'no' value mismatch");
		$this->assertNotEquals($type->getYesValueId(), $type->getNoValueId(), "yes and no values are the same");

		$yesBefore = $type->getYesValueId();
		$noBefore = $type->getNoValueId();

		$this->assertTrue($type->processPostedAttributeValues($request, $attribute), "processPostedAttributeValues failed(2)");

		$this->assertSame($yesBefore, $type->getYesValueId(), "value for yes has changed");
		$this->assertSame($noBefore, $type->getNoValueId(), "value for no has changed");
	}
}
