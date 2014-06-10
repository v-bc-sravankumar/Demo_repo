<?php

class Integration_Store_Attribute_Type_Configurable_Entry_Text extends Integration_Store_Attribute_Type_Configurable
{
	protected $_prefix = 'Configurable_Entry_Text_';

	protected function _getInstance ()
	{
		return new Store_Attribute_Type_Configurable_Entry_Text;
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text
	 */
	public function testSetConfigurationSettingsWithEmptyRequest ()
	{
		$request = new Interspire_Request();

		$type = $this->_getInstance();
		$type->setConfigurationSettings(new Store_Attribute, $request);

		$this->assertSame('', $type->getDefaultValue(), "default value mismatch");
		$this->assertSame(false, $type->getValidateCharacterLength(), "validate character length mismatch");
		$this->assertSame(0, $type->getMinLength(), "min length mismatch");
		$this->assertSame(0, $type->getMaxLength(), "max length mismatch");
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setDefaultValue
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::getDefaultValue
	 */
	public function testConfigureDefaultValue ()
	{
		$type = $this->_getInstance();

		$type->setConfigurationSettings(
			new Store_Attribute,
			new Interspire_Request(null, array(
				$this->_prefix . 'DefaultValue' => 123.4,
			))
		);

		$this->assertSame('123.4', $type->getDefaultValue());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setValidateCharacterLength
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::getValidateCharacterLength
	 */
	public function testConfigureValidateCharacterLength ()
	{
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'ValidateCharacterLength' => 'ON',
		));

		$type = $this->_getInstance();
		$type->setConfigurationSettings(new Store_Attribute, $request);

		$this->assertSame(true, $type->getValidateCharacterLength());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setMinLength
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::getMinLength
	 */
	public function testConfigureMinLength ()
	{
		$type = $this->_getInstance();

		// min length shouldn't change unless validation is enabled
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'MinLength' => '2.9',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame(0, $type->getMinLength());

		// this time it should though
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'ValidateCharacterLength' => 'ON',
			$this->_prefix . 'MinLength' => '2.9',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame(2, $type->getMinLength());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::setMaxLength
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::getMaxLength
	 */
	public function testConfigureMaxLength ()
	{
		$type = $this->_getInstance();

		// max length shouldn't change unless validation is enabled
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'MaxLength' => '2.9',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame(0, $type->getMaxLength());

		// this time it should though
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'ValidateCharacterLength' => 'ON',
			$this->_prefix . 'MaxLength' => '2.9',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame(2, $type->getMaxLength());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 */
	public function testValidateNotRequiredPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(false);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, '');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 */
	public function testValidateRequiredPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, 'test');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Required
	 */
	public function testValidateRequiredFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, '');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 */
	public function testValidateMinLengthPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateCharacterLength(true)
			->setMinLength(3);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, 'test');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Text_MinLength
	 */
	public function testValidateMinLengthFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateCharacterLength(true)
			->setMinLength(5);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, 'test');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 */
	public function testValidateMaxLengthPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateCharacterLength(true)
			->setMaxLength(5);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, 'test');
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Text_MaxLength
	 */
	public function testValidateMaxLengthFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateCharacterLength(true)
			->setMaxLength(3);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, 'test');
	}

	public function testGetCartDisplayValue ()
	{
		$type = $this->_getInstance();

		$value = "test&\ntest";
		$expected = "test&amp;&#8203;<br />\ntest";

		$productAttribute = new ISC_QUOTE_ITEM_PRODUCT_ATTRIBUTE;
		$productAttribute->setDisplayValue($value);

		$actual = $type->getCartDisplayValue($productAttribute, $value);

		$this->assertSame($expected, $actual);
	}

	public function testGetDisplayValue ()
	{
		$type = $this->_getInstance();

		$value = "test&\ntest";
		$actual = $type->getDisplayValue($value);

		$this->assertSame($value, $actual);
	}
}
