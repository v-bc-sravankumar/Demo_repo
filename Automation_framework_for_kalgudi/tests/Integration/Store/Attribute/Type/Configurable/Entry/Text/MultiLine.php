<?php

class Unit_Lib_Store_Attribute_Type_Configurable_Entry_Text_MultiLine extends Integration_Store_Attribute_Type_Configurable_Entry_Text
{
	protected $_prefix = 'Configurable_Entry_Text_MultiLine_';

	protected function _getInstance ()
	{
		return new Store_Attribute_Type_Configurable_Entry_Text_MultiLine;
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine
	 */
	public function testConfigureValidateLineLength ()
	{
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'ValidateLineLength' => 'ON',
			$this->_prefix . 'MaxLines' => 100,
		));

		$type = $this->_getInstance();
		$type->setConfigurationSettings(new Store_Attribute, $request);

		$this->assertSame(true, $type->getValidateLineLength());
		$this->assertSame(100, $type->getMaxLines());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::setConfigurationSettings
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::setValidateLineLength
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::getValidateLineLength
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::setMaxLines
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::getMaxLines
	 */
	public function testConfigureValidateCharacterLength ()
	{
		$request = new Interspire_Request(null, array(
			$this->_prefix . 'ValidateCharacterLength' => 'ON',
			$this->_prefix . 'MinLength' => 50,
			$this->_prefix . 'MaxLength' => 150,
		));

		$type = $this->_getInstance();
		$type->setConfigurationSettings(new Store_Attribute, $request);

		$this->assertSame(true, $type->getValidateCharacterLength());
		$this->assertSame(50, $type->getMinLength());
		$this->assertSame(150, $type->getMaxLength());
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Required
	 */
	public function testValidateMultiLineRequiredFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->validateEnteredValue($productAttribute, new Store_Attribute, "\n\n"); // an all-newline string should fail required checks
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::validateEnteredValue
	 */
	public function testValidateLineLengthPass ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateLineLength(true)
			->setMaxLines(2);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, "1\n2\n\n"); // note the trailing newlines should be ignored
	}

	/**
	 * @covers Store_Attribute_Type_Configurable_Entry_Text_MultiLine::validateEnteredValue
	 * @expectedException Store_Attribute_Type_Exception_Validation_Text_MaxLines
	 */
	public function testValidateLineLengthFail ()
	{
		$productAttribute = new Store_Product_Attribute;
		$productAttribute->setRequired(true);

		$type = $this->_getInstance();
		$type->setValidateLineLength(true)
			->setMaxLines(2);

		$type->validateEnteredValue($productAttribute, new Store_Attribute, "\n\n3"); // note the leading newlines should not be ignored
	}
}
