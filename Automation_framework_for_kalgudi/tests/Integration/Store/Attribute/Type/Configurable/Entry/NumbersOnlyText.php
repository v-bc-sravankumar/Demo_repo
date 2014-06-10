<?php

class Unit_Lib_Store_Attribute_Type_Configurable_Entry_NumbersOnlyText extends Integration_Store_Attribute_Type_Configurable
{
	protected $_prefix = 'Configurable_Entry_NumbersOnlyText_';

	protected function _getInstance ()
	{
		return new Store_Attribute_Type_Configurable_Entry_NumbersOnlyText;
	}

	public function testSetConfigurationSettingsWithEmptyRequest ()
	{
		$request = new Interspire_Request();

		$type = $this->_getInstance();
		$type->setConfigurationSettings(new Store_Attribute, $request);

		$this->assertSame('', $type->getDefaultValue(), "default value mismatch");
		$this->assertSame(false, $type->getLimitInput(), "limit input mismatch");
		$this->assertSame(0, $type->getLowestValue(), "lowest value mismatch");
		$this->assertSame(0, $type->getHighestValue(), "highest value mismatch");
		$this->assertSame(false, $type->getIntegerOnly(), "integer only mismatch");
		$this->assertSame('lowest', $type->getLimitInputOption(), "limit input option mismatch");
	}

	public function testConfigureDefaultValue ()
	{
		$type = $this->_getInstance();

		$request = new Interspire_Request(null, array(
			$this->_prefix . 'DefaultValue' => '123.4',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame(123.4, $type->getDefaultValue());

		$request = new Interspire_Request(null, array(
			$this->_prefix . 'DefaultValue' => '',
		));
		$type->setConfigurationSettings(new Store_Attribute, $request);
		$this->assertSame('', $type->getDefaultValue());
	}
}
