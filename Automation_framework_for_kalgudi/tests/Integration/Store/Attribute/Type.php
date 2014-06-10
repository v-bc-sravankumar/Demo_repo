<?php

class Unit_Lib_Store_Attribute_Type extends Interspire_IntegrationTest {
	public function setUp()
	{
		parent::setUp();

		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testGetAttributeTypes()
	{
		$allTypes = Store_Attribute_Type::getAttributeTypes();
		$this->assertArrayIsNotEmpty($allTypes, "failed to retrieve all attribute types");

		$configurableTypes = Store_Attribute_Type::getAttributeTypes(Store_Attribute_Type::TYPE_CONFIGURABLE);
		$this->assertArrayIsNotEmpty($configurableTypes, "failed to retrieve configurable attribute types");

		$fixedTypes = Store_Attribute_Type::getAttributeTypes(Store_Attribute_Type::TYPE_FIXED);
		$this->assertArrayIsNotEmpty($fixedTypes, "failed to retrieve fixed attribute types");
	}

	public function testIsConfigurable()
	{
		$textType = new Store_Attribute_Type_Configurable_Entry_Text();
		$this->assertTrue($textType->isConfigurable(), "text type failed to assert as configurable");
	}

	public function testGetConfigurationPanel()
	{
		$textType = new Store_Attribute_Type_Configurable_Entry_Text();

		$attribute = new Store_Attribute();
		$attribute->setDisplayName('Display Name');
		$attribute->setName('Name');

		$template = Interspire_Template::getInstance('admin');

		$html = $textType->getConfigurationPanel($template, $attribute);
		$this->assertTrue(!empty($html), "configuration panel failed to render");
	}

	public function testViewsValid()
	{
		$setType = new Store_Attribute_Type_Configurable_PickList_Set();
		$this->assertTrue($setType->isViewValidForType(new Store_Attribute_View_Select()), "select view wasn't valid for picklist set type");

		$this->assertFalse($setType->isViewValidForType(new Store_Attribute_View_Product_PickList()), "product picklist view was valid for picklist set type");
	}
}
