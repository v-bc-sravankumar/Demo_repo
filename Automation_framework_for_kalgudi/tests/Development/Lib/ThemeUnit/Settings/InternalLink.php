<?php

class Unit_Lib_Theme_Settings_InternalLink extends Interspire_UnitTest
{
	/**
	 * @var Theme_Settings_Link
	 */
	private $_link;

	public function setUp()
	{
		parent::setUp();
		$this->_link = new Theme_Settings_InternalLink('product', 1);
	}

	public function testToStringIncludesShopPath()
	{
		$shopPath = Store_Config::get('ShopPath');
		$this->assertContains($shopPath, (string)$this->_link);
	}

}
