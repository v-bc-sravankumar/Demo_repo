<?php

namespace Integration\Product;

use ISC_PRODUCT;
use Store\Settings\InventorySettings;

class ProductTest extends \PHPUnit_Framework_TestCase
{
	private $productController;

	private $originalSettings;

	public function setUp()
	{
		$this->productController = $this
			->getMockBuilder('ISC_PRODUCT')
			->setMethods(null)
			->disableOriginalConstructor()
			->getMock();

		$this->originalSettings = \Store_Config::getInstance();
		\Store_Config::setInstance(clone $this->originalSettings);
	}

	public function tearDown()
	{
		\Store_Config::setInstance($this->originalSettings);
	}

	public function testIsHidden()
	{
		$product = new ISC_PRODUCT();
		$settings = $product->getInventorySettings();

		$settings->setProductOutOfStockBehavior(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE);

		//Feature enabled
		$product->_prodcurrentinv = 1;
		$product->_prodinvtrack = 1;
		$this->assertFalse($product->isHidden());

		$product->_prodcurrentinv = 0;
		$product->_prodinvtrack = 1;
		$this->assertTrue($product->isHidden());

		$product->_prodcurrentinv = 1;
		$product->_prodinvtrack = 0;
		$this->assertFalse($product->isHidden());

		$product->_prodcurrentinv = 0;
		$product->_prodinvtrack = 0;
		$this->assertFalse($product->isHidden());

		//Config disabled
		$settings->setProductOutOfStockBehavior('anything');
		$product->_prodcurrentinv = 1;
		$product->_prodinvtrack = 1;
		$this->assertFalse($product->isHidden());

		$product->_prodcurrentinv = 0;
		$product->_prodinvtrack = 1;
		$this->assertFalse($product->isHidden());

		$product->_prodcurrentinv = 1;
		$product->_prodinvtrack = 0;
		$this->assertFalse($product->isHidden());

		$product->_prodcurrentinv = 0;
		$product->_prodinvtrack = 0;
		$this->assertFalse($product->isHidden());
	}

	public function testIsInventoryVisibleWhenDisplayIsLowStock()
	{
		$product = new ISC_PRODUCT();
		$product->_prodinvtrack = 1;

		$settings = $product->getInventorySettings();

		//Low stock
		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW_WHEN_LOW);
		$product->_prodcurrentinv = 1;
		$product->_prodlowinv = 1;

		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 1;
		$product->_prodlowinv = 2;

		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 0;
		$product->_prodlowinv = 2;
		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 5;
		$product->_prodlowinv = 2;
		$this->assertFalse($product->isInventoryVisible());
	}

	public function testIsInventoryVisibleWhenDisplayIsShow()
	{
		$product = new ISC_PRODUCT();
		$product->_prodinvtrack = 1;

		$settings = $product->getInventorySettings();

		//Show levels
		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW);
		$product->_prodcurrentinv = 1;
		$product->_prodlowinv = 1;

		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 5;
		$product->_prodlowinv = 6;

		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 0;
		$product->_prodlowinv = 2;
		$this->assertTrue($product->isInventoryVisible());

		$product->_prodcurrentinv = 6;
		$product->_prodlowinv = 2;
		$this->assertTrue($product->isInventoryVisible());
	}

	public function testIsInventoryVisibleWhenDisplayIsDontShow()
	{
		$product = new ISC_PRODUCT();
		$product->_prodinvtrack = 1;

		$settings = $product->getInventorySettings();

		//Hide levels
		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_DONT_SHOW);
		$product->_prodcurrentinv = 1;
		$product->_prodlowinv = 1;

		$this->assertFalse($product->isInventoryVisible());

		$product->_prodcurrentinv = 5;
		$product->_prodlowinv = 6;

		$this->assertFalse($product->isInventoryVisible());

		$product->_prodcurrentinv = 0;
		$product->_prodlowinv = 2;
		$this->assertFalse($product->isInventoryVisible());

		$product->_prodcurrentinv = 6;
		$product->_prodlowinv = 2;
		$this->assertFalse($product->isInventoryVisible());
	}

	public function testIsInventoryVisibleForPreOrder()
	{
		$product = new ISC_PRODUCT();
		$product->_prodinvtrack = 1;

		$settings = $product->getInventorySettings();

		//Pre-order
		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW);
		$product->_prodpreorder = 0;
		$this->assertTrue($product->isInventoryVisible());

		$product->_prodpreorder = 1;
		$this->assertFalse($product->isInventoryVisible());

		$product->_prodpreorder = 1;
		$settings->setShowPreOrderStockLevels(true);
		$this->assertTrue($product->isInventoryVisible());

		$product->_prodpreorder = 1;
		$settings->setShowPreOrderStockLevels(false);
		$this->assertFalse($product->isInventoryVisible());
	}

	public function testGetListingOutOfStockMessage()
	{
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', true);

		$this->assertEquals('Unavailable', \ISC_PRODUCT::getListingOutOfStockMessage());
	}

	public function testGetListingOutOfStockMessageForProductOutOfStockBehavior()
	{
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('ProductOutOfStockBehavior', 'Not Do Nothing');
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', true);

		$this->assertFalse(\ISC_PRODUCT::getListingOutOfStockMessage());
	}

	public function testGetListingOutOfStockMessageForShowOutOfStockMessage()
	{
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', false);

		$this->assertFalse(\ISC_PRODUCT::getListingOutOfStockMessage());
	}

	public function testGetListingOutOfStockMessageForEscapedMessage()
	{
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		\Store_Config::override('DefaultOutOfStockMessage', '<test>');
		\Store_Config::override('ShowOutOfStockMessage', true);

		$this->assertEquals("&lt;test&gt;", \ISC_PRODUCT::getListingOutOfStockMessage());
	}

	public function testIsAddToCartVisibleFalseForProductPrice()
	{
		$product= array();
		\Store_Config::override('ShowProductPrice', false);
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));

		$product['prodhideprice'] =1;
		\Store_Config::override('ShowProductPrice', true);
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleFalseForAllowPurchasing()
	{
		$product= array();
		\Store_Config::override('AllowPurchasing', false);
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));

		$product['prodallowpurchases'] = false;
		\Store_Config::override('AllowPurchasing', true);
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleFalseForEmptyProductWithInventorySettings()
	{
		$product= array();
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', true);
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleFalseWithoutInventoryOOS()
	{
		$product= array();
		\Store_Feature::override('InventorySettings', false);
		$product['prodinvtrack'] = 1;
		$product['prodcurrentinv'] = 0;
		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleTrueWithoutInventorySettings()
	{
		$product= array();
		\Store_Feature::override('InventorySettings', false);
		\Store_Config::override('AllowPurchasing', true);
		\Store_Config::override('ShowProductPrice', true);

		$product['prodhideprice'] = 0;
		$product['prodallowpurchases'] = true;
		$product['prodinvtrack'] = 1;
		$product['prodcurrentinv'] = 1;

		$this->assertTrue(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleTrueWithInventorySettings()
	{
		$product= array();
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('AllowPurchasing', true);
		\Store_Config::override('ShowProductPrice', true);

		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', true);

		$product['prodhideprice'] = 0;
		$product['prodallowpurchases'] = true;
		$product['prodinvtrack'] = 1;
		$product['prodcurrentinv'] = 1;

		$this->assertTrue(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testIsAddToCartVisibleFalseWithInventorySettings()
	{
		$product= array();
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('AllowPurchasing', true);
		\Store_Config::override('ShowProductPrice', true);

		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		\Store_Config::override('DefaultOutOfStockMessage', 'Unavailable');
		\Store_Config::override('ShowOutOfStockMessage', true);

		$product['prodhideprice'] = 0;
		$product['prodallowpurchases'] = true;
		$product['prodinvtrack'] = 1;
		$product['prodcurrentinv'] = 0;

		$this->assertFalse(\ISC_PRODUCT::isAddToCartVisible($product));
	}

	public function testEmptyLayoutDefaultsToProduct()
	{
		$this->productController->setLayoutFile('');
		$this->assertEquals('product', $this->productController->getLayoutFile());
	}

	public function testMissingLayoutDefaultsToProduct()
	{
		$this->productController->setLayoutFile('foo.html');
		$this->assertEquals('product', $this->productController->getLayoutFile());
	}

	public function testSetLayoutStripsExtension()
	{
		$this->productController->setLayoutFile('default.html');
		$this->assertEquals('default', $this->productController->getLayoutFile());
	}
}
