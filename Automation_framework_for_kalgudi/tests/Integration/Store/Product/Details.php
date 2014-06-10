<?php

namespace Integration\Store\Product;

use Store_Product_Details;
use Store_Product_Attribute_Combination;
use ISC_PRODUCT;
use Store\Settings\InventorySettings;
use Store_Config;

class DetailsTest extends \Interspire_IntegrationTest
{
	/**
	 * These methods perform calculations and need test coverage:
	 *
	 * toStoreFrontJavaScript (uses real product info)
	 * toStoreFrontDetails (uses real product info)
	 */

	/**
	 * @covers Store_Product_Details::getStoreFrontSku
	 */
	public function testGetStoreFrontSkuReturnsSkuWhenSkusShown ()
	{
		$showSkuBackup = Store_Config::get('ShowProductSKU');
		Store_Config::override('ShowProductSKU', true);
		$details = new Store_Product_Details;
		$details->setSku('test');
		$this->assertSame('test', $details->getStoreFrontSku());
		Store_Config::override('ShowProductSKU', $showSkuBackup);
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontSku
	 */
	public function testGetStoreFrontSkuReturnsNullWhenSkusHidden ()
	{
		$showSkuBackup = Store_Config::get('ShowProductSKU');
		Store_Config::override('ShowProductSKU', false);
		$details = new Store_Product_Details;
		$details->setSku('test');
		$this->assertNull($details->getStoreFrontSku());
		Store_Config::override('ShowProductSKU', $showSkuBackup);
	}

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsTrueWhenDontTrackInventory()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(0);
        $this->assertTrue($details->getInStockOOS());
    }

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsTrueWhenTrackingPositiveInventoryProduct()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(1);
        $details->setStockLevel(10);
        $this->assertTrue($details->getInStockOOS());
    }

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsFalseWhenTrackingZeroInventoryProduct()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(1);
        $details->setStockLevel(0);
        $this->assertFalse($details->getInStockOOS());
    }

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsFalseWhenTrackingNegativeInventoryProduct()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(1);
        $details->setStockLevel(-10);
        $this->assertFalse($details->getInStockOOS());
    }

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsTrueWhenTrackingOptionInventoryWithInStockAttributeValues()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(2);
        $details->setInStockAttributeValues(array(1, 2));
        $this->assertTrue($details->getInStockOOS());
    }

	/**
	 * @covers Store_Product_Details::getInStockOOS
	 */
    public function testGetInStockIsTrueWhenTrackingOptionInventoryWithoutInStockAttributeValues()
    {
        $details = new Store_Product_Details;
        $details->setInventoryTracking(2);
        $details->setInStockAttributeValues(array());
        $this->assertFalse($details->getInStockOOS());
    }

    private function makeProductWithInventoryTracking($trackingMode)
    {
        $product = $this->getMock('ISC_PRODUCT');
        $product->expects($this->any())
            ->method('GetProductInventoryTracking')
            ->will($this->returnValue($trackingMode));

        return $product;
    }

	/**
	 * @covers Store_Product_Details::getInStock
	 */
	public function testGetInStockIsTrueWhenNoStockLevelDefined ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);

		$this->assertTrue($details->getInStock(), "instock should be true");
		$details->setStockLevel(1);
		$details->setStockLevel(null);
		$this->assertTrue($details->getInStock(), "failed check after setting and unsetting stock level");
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontStockLevel
	 */
	public function testGetStoreFrontStockLevelReturnsNullWhenNoStockLevelDefined ()
	{
		$details = new Store_Product_Details;
		$this->assertNull($details->getStoreFrontStockLevel());
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontStockLevel
	 */
	public function testGetStoreFrontStockLevel ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setStockLevel(2);

		$settings = $product->getInventorySettings();

		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW);
		$product->_prodpreorder = 0;
		$this->assertSame(2, $details->getStoreFrontStockLevel(), "level mismatch");

		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_DONT_SHOW);
		$this->assertNull($details->getStoreFrontStockLevel(), "failed when inventory hidden");

		$settings->setStockLevelDisplay(InventorySettings::STOCK_LEVEL_DISPLAY_DONT_SHOW);
		$settings->setShowPreOrderStockLevels(false);
		$product->_prodpreorder = 1;
		$this->assertNull($details->getStoreFrontStockLevel(), "failed when preorder inventory hidden");
	}

	/**
	 * @covers Store_Product_Details::getInStock
	 */
	public function testGetInStockIsTrueForPositiveStock ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$details->getInventorySettings()->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING);
		$details->setStockLevel(1);

		$this->assertTrue($details->getInStock());
	}

	/**
	 * @covers Store_Product_Details::getInStock
	 */
	public function testGetInStockIsFalseForZeroStock ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$details->setStockLevel(0);
		$details->getInventorySettings()->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING);

		$this->assertFalse($details->getInStock());
	}

	/**
	 * @covers Store_Product_Details::getInStock
	 */
	public function testGetInStockIsFalseForNegativeStock ()
	{
		// this is a hypothetical test since we don't support back ordering / negative stock levels - adding for future
		// testing though just in case we add that support
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$details->getInventorySettings()->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING);
		$details->setStockLevel(-1);

		$this->assertFalse($details->getInStock());
	}

	/**
	 * @covers Store_Product_Details::getSaveAmount
	 */
	public function testGetSaveAmountReturnsZeroWithNoRrp ()
	{
		$details = new Store_Product_Details;
		$this->assertSame(0.0, $details->getSaveAmount());
	}

	/**
	 * @covers Store_Product_Details::getSaveAmount
	 */
	public function testGetSaveAmountReturnsZeroWithZeroRrp ()
	{
		$details = new Store_Product_Details;
		$details->setRrp(0);
		$this->assertSame(0.0, $details->getSaveAmount());
	}

	/**
	 * @covers Store_Product_Details::getSaveAmount
	 */
	public function testGetSaveAmountReturnsZeroWhenPriceHigherThanRrp ()
	{
		$details = new Store_Product_Details;
		$details->setRrp(15)
			->setPriceExTax(20)
			->setPriceIncTax(20);
		$this->assertSame(0.0, $details->getSaveAmount());
	}

	/**
	 * @covers Store_Product_Details::getSaveAmount
	 */
	public function testGetSaveAmountReturnsDifferenceWhenPriceLowerThanRrp ()
	{
		$details = new Store_Product_Details;
		$details->setRrp(15)
			->setPriceExTax(10)
			->setPriceIncTax(10);
		$this->assertSame(5.0, $details->getSaveAmount());
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageReturnsNullForNoStockLevelDefined ()
	{
		$details = new Store_Product_Details;
		$this->assertNull($details->getStockMessage());
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageReturnsNullForPositiveStock ()
	{
		$details = new Store_Product_Details;
		$details->setStockLevel(1);

		Store_Config::override('StockLevelDisplay','dont_show');
		$this->assertNull($details->getStockMessage(), "failed when inventory hidden");

		Store_Config::override('StockLevelDisplay','show');

		$this->assertNull($details->getStockMessage(), "failed when inventory showing");
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageStockLevelDisplayShow ()
	{
		$details = new Store_Product_Details;
		Store_Config::override('StockLevelDisplay','show');

		$product = new ISC_PRODUCT;
		$product->_prodpreorder = false;
		$details->setProduct($product);

		//Positive stock
		$details->setStockLevel(1);
		$this->assertNull($details->getStockMessage(), "failed when inventory showing");

		//Zero stock
		$details->setStockLevel(0);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when inventory showing");

		//Negative stock
		$details->setStockLevel(-1);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when inventory showing");
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageStockLevelDisplayDontShow ()
	{
		$details = new Store_Product_Details;
		Store_Config::override('StockLevelDisplay','dont_show');

		$product = new ISC_PRODUCT;
		$product->_prodpreorder = false;
		$details->setProduct($product);

		//Positive stock
		$details->setStockLevel(1);
		$this->assertNull($details->getStockMessage(), "failed when inventory hidden");

		//Zero stock
		$details->setStockLevel(0);
		$this->assertNull($details->getStockMessage(), "failed when inventory hidden");

		//Negative stock
		$details->setStockLevel(-1);
		$this->assertNull($details->getStockMessage(), "failed when inventory hidden");
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageStockLevelDisplayDontShowPreOrderFalse ()
	{
		$details = new Store_Product_Details;
		Store_Config::override('StockLevelDisplay','dont_show');
		Store_Config::override('ShowPreOrderInventory', false);

		$product = new ISC_PRODUCT;
		$product->_prodpreorder = false;
		$details->setProduct($product);

		//Positive stock
		$details->setStockLevel(1);
		$this->assertNull($details->getStockMessage(), "failed when show pre order false");

		//Zero stock
		$details->setStockLevel(0);
		$this->assertNull($details->getStockMessage(), "failed when show pre order false");

		//Negative stock
		$details->setStockLevel(-1);
		$this->assertNull($details->getStockMessage(), "failed when show pre order false");
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageProdPreorderFalseShowPreOrderTrue ()
	{
		$details = new Store_Product_Details;
		Store_Config::override('StockLevelDisplay','show');
		Store_Config::override('ShowPreOrderInventory', true);

		$product = new ISC_PRODUCT;
		$product->_prodpreorder = false;
		$details->setProduct($product);

		//Positive stock
		$details->setStockLevel(1);
		$this->assertNull($details->getStockMessage(), "failed when show pre order false");

		//Zero stock
		$details->setStockLevel(0);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when show pre order false");

		//Negative stock
		$details->setStockLevel(-1);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when show pre order false");
	}

	/**
	 * @covers Store_Product_Details::getStockMessage
	 */
	public function testGetStockMessageProdPreorderTrueShowPreOrderTrue ()
	{
		$details = new Store_Product_Details;
		Store_Config::override('StockLevelDisplay','show');
		Store_Config::override('ShowPreOrderInventory', true);

		$product = new ISC_PRODUCT;
		$product->_prodpreorder = true;
		$details->setProduct($product);

		//Positive stock
		$details->setStockLevel(1);
		$this->assertNull($details->getStockMessage(), "failed when pre order true");

		//Zero stock
		$details->setStockLevel(0);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when pre order true");

		//Negative stock
		$details->setStockLevel(-1);
		$this->assertInternalType('string', $details->getStockMessage(), "failed when pre order true");
	}


	/**
	 * @covers Store_Product_Details::getStoreFrontPrice
	 */
	public function testGetStoreFrontPriceReturnsNullForNoPriceDefined ()
	{
		$details = new Store_Product_Details;
		$this->assertNull($details->getStoreFrontPrice());
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPrice
	 */
	public function testGetStoreFrontPriceReturnsNullWhenPricesHidden ()
	{
		$details = new Store_Product_Details;
		$details
			->setPriceIncTax(1)
			->setPriceExTax(1);

		$product = new ISC_PRODUCT;
		$details->setProduct($product);

		Store_Config::override('ShowProductPrice', false);
		$product->_prodhideprice = 0;
		$this->assertNull($details->getStoreFrontPrice(), "failed when prices are hidden globally");

		Store_Config::override('ShowProductPrice', true);
		$product->_prodhideprice = 1;
		$this->assertNull($details->getStoreFrontPrice(), "failed when product price is hidden");
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPrice
	 */
	public function testGetStoreFrontPriceCanReturnUnformattedNumber ()
	{
		$details = new Store_Product_Details;
		$details
			->setPriceIncTax(2.2)
			->setPriceExTax(2.2);

		$product = new ISC_PRODUCT;
		$product->_prodhideprice = 0;
		$details->setProduct($product);

		$this->assertSame('2.2', $details->getStoreFrontPrice());
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPrice
	 */
	public function testGetStoreFrontPriceCanReturnFormattedString ()
	{
		$details = new Store_Product_Details;
		$details
			->setPriceIncTax(1)
			->setPriceExTax(1);

		$product = new ISC_PRODUCT;
		$product->_prodhideprice = 0;
		$details->setProduct($product);

		$this->assertInternalType('string', $details->getStoreFrontPrice(true));
	}

	/**
	 * @covers Store_Product_Details::useCombination
	 */
	public function testUseCombinationWithSku ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);

		$combination = new Store_Product_Attribute_Combination;
		$combination->setSku(uniqid('', true));

		$details->useCombination($combination);

		$this->assertFalse($details->getIsBaseProduct(), "base product should be false");
		$this->assertSame($combination->getSku(), $details->getSku(), "value mismatch");
	}

	/**
	 * @covers Store_Product_Details::useCombination
	 */
	public function testUseCombinationWithUpc ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);

		$combination = new Store_Product_Attribute_Combination;
		$combination->setUpc(uniqid('', true));

		$details->useCombination($combination);

		$this->assertFalse($details->getIsBaseProduct(), "base product should be false");
		$this->assertSame($combination->getUpc(), $details->getUpc(), "value mismatch");
	}

	/**
	 * @covers Store_Product_Details::useCombination
	 */
	public function testUseCombinationWithStockLevel ()
	{
		$details = new Store_Product_Details;

		$product = new ISC_PRODUCT;
		$details->setProduct($product);

		$combination = new Store_Product_Attribute_Combination;
		$combination->setStockLevel(1);

		// without inv tracking set on product
		$details->useCombination($combination);

		$this->assertTrue($details->getIsBaseProduct(), "base product should be true");
		$this->assertNull($details->getStockLevel(), "stock level should be null");

		// without inv tracking set to use sku data
		$product->_prodinvtrack = 2;
		$details->useCombination($combination);

		$this->assertFalse($details->getIsBaseProduct(), "base product should be false");
		$this->assertSame($combination->getStockLevel(), $details->getStockLevel(), "value mismatch");
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPriceMessage
	 */
	public function testGetStoreFrontPriceMessageReturnsNullWithNoPriceDefined ()
	{
		$details = new Store_Product_Details;
		$this->assertNull($details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']));
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPriceMessage
	 */
	public function testGetStoreFrontPriceMessageReturnsNullWithPriceWhenNotHidden ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setPriceIncTax(1)
			->setPriceExTax(1);
		$this->assertNull($details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']));
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPriceMessage
	 */
	public function testGetStoreFrontPriceMessageReturnsStringWhenPurchasingDisabled ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setPriceIncTax(1)
			->setPriceExTax(1)
			->setPurchasingEnabled(false);
		$this->assertInternalType('string', $details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']));
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPriceMessage
	 */
	public function testGetStoreFrontPriceMessageReturnsNullWithoutCallLabel ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setPriceIncTax(1)
			->setPriceExTax(1);

		Store_Config::override('ShowProductPrice', false);
		$product->_prodhideprice = 0;
		$this->assertNull($details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']), "failed when prices hidden globally");

		Store_Config::override('ShowProductPrice', true);
		$product->_prodhideprice = 1;
		$this->assertNull($details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']), "failed when product price hidden");
	}

	/**
	 * @covers Store_Product_Details::getStoreFrontPriceMessage
	 */
	public function testGetStoreFrontPriceMessageReturnsStringWithCallLabel ()
	{
		$product = new ISC_PRODUCT;
		$product->_prodcallforpricinglabel = 'test';

		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setPriceIncTax(1)
			->setPriceExTax(1);

		Store_Config::override('ShowProductPrice', true);
		$product->_prodhideprice = 1;
		$this->assertSame($product->_prodcallforpricinglabel, $details->getStoreFrontPriceMessage($GLOBALS['ISC_CLASS_TEMPLATE']));
	}

	public function provideGetSetData ()
	{
		return array(
			array('IsBaseProduct', true, null, false),
			array('Sku', 'foo'),
			array('Upc', 'foo'),
			array('StockLevel', 2),
			array('PriceIncTax', 2.2),
			array('PriceExTax', 2.2),
			array('Weight', 2.2),
			array('BaseImage', 'foo'),
			array('BaseThumb', 'foo'),
			array('Image', 'foo'),
			array('Thumb', 'foo'),
			array('Rrp', 2.2),
			array('PurchasingEnabled', true),
			array('PurchasingMessage', 'foo'),
			array('ImageRuleId', 2),
		);
	}

	/**
	 * @dataProvider provideGetSetData
	 */
	public function testGetSet ($method, $set, $get = null, $testNull = true)
	{
		if ($get === null) {
			$get = $set;
		}

		$setMethod = 'set' . $method;
		$getMethod = 'get' . $method;
		$details = new Store_Product_Details;
		$this->assertSame($details, $details->$setMethod($set, "set(value) return mismatch"));
		$this->assertSame($get, $details->$getMethod(), "get() return mismatch");

		if ($testNull) {
			$this->assertSame($details, $details->$setMethod(null), "set(null) return mismatch");
			$this->assertNull($details->$getMethod(), "get() should have been null");
		}
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsReturnsArray ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$this->assertInternalType('array', $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']));
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsReturnsArrayWithNoNulls ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertInternalType('array', $array);
		foreach ($array as $key => $value) {
			$this->assertNotNull($value, "array contains null value at key $key");
		}
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsPriceMessage ()
	{
		$details = new Store_Product_Details;

		$product = new ISC_PRODUCT;
		$product->_prodhideprice = 1;
		$product->_prodcallforpricinglabel = 'test';

		$details->setProduct($product)
			->setPriceIncTax(2)
			->setPriceExTax(2);

		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertSame($product->_prodcallforpricinglabel, $array['price'], "price message fail for 'call' label");

		$product->_prodhideprice = 0;
		$details->setPurchasingEnabled(false);
		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertNotSame($product->_prodcallforpricinglabel, $array['price'], "price message should not equal call label when pricing enabled but purchasing disabled");
		$this->assertInternalType('string', $array['price'], "price message fail when purchasing disabled");
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsPrice ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product);

		$this->assertArrayNotHasKey('price', $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']));

		$details
			->setPriceIncTax(2)
			->setPriceExTax(2);

		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertSame((string)$details->getPrice(), $array['unformattedPrice']);
		$this->assertInternalType('string', $array['price']);
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsWeight ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details
			->setProduct($product)
			->setWeight(2);
		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertSame($details->getWeight(), $array['unformattedWeight']);
		$this->assertInternalType('string', $array['weight']);
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontDetails
	 */
	public function testToStoreFrontDetailsRrp ()
	{
		$product = new ISC_PRODUCT;
		$details = new Store_Product_Details;
		$details->setProduct($product)
			->setPriceIncTax(2)
			->setPriceExTax(2);

		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertArrayNotHasKey('rrp', $array);
		$this->assertArrayNotHasKey('unformattedRrp', $array);
		$this->assertArrayNotHasKey('saveAmount', $array);

		$details->setRrp(2);
		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertInternalType('string', $array['rrp']);
		$this->assertSame($details->getRrp(), $array['unformattedRrp']);
		$this->assertArrayNotHasKey('saveAmount', $array);

		$details->setRrp(3);
		$array = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertInternalType('string', $array['saveAmount']);
	}

	/**
	 * @covers Store_Product_Details::toStoreFrontJavaScript
	 */
	public function testToStoreFrontJavaScript ()
	{
		$details = new Store_Product_Details;
		$details->setProduct(new ISC_PRODUCT);
		$js = $details->toStoreFrontJavaScript($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertInternalType('string', $js);
		$this->assertNotSame(false, strpos($js, '$("#ProductDetails").updateProductDetails('));
    }

    public function testGetInStockAttributeValues()
    {
        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setInStockAttributeValues(array(1,2));
        $this->assertEquals(array(1,2), $details->getInStockAttributeValues());
        $this->assertFalse($details->getIsBaseProduct());
    }

    public function testToStoreFrontDetailsHasInStockAttributeValues()
    {
        Store_Config::override('OptionOutOfStockBehavior', InventorySettings::OPTION_OUT_OF_STOCK_HIDE);

        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setProduct($this->makeProductWithInventoryTracking(2));
        $details->setInStockAttributeValues(array(1,2));

        $storefront = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
        $this->assertEquals(array(1,2), $storefront['inStockAttributeValues']);
    }

    public function testGetSelectedAttributeValues()
    {
        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setSelectedAttributeValues(array(1,2));
        $this->assertEquals(array(1,2), $details->getSelectedAttributeValues());
    }

    public function testToStoreFrontDetailsHasSelectedAttributeValues()
    {
        Store_Config::override('OptionOutOfStockBehavior', InventorySettings::OPTION_OUT_OF_STOCK_HIDE);

        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setProduct($this->makeProductWithInventoryTracking(2));
        $details->setSelectedAttributeValues(array(1,2));

        $storefront = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
        $this->assertEquals(array(1,2), $storefront['selectedAttributeValues']);
    }

    public function testToStoreFrontDetailsHasOptionOutOfStockBehavior()
    {
        Store_Config::override('OptionOutOfStockBehavior', InventorySettings::OPTION_OUT_OF_STOCK_HIDE);

        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setProduct($this->makeProductWithInventoryTracking(2));

        $storefront = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
        $this->assertEquals(InventorySettings::OPTION_OUT_OF_STOCK_HIDE, $storefront['optionOutOfStockBehavior']);
    }

    public function testToStoreFrontDetailsHasOutOfStockMessage()
    {
        Store_Config::override('OptionOutOfStockBehavior', InventorySettings::OPTION_OUT_OF_STOCK_HIDE);
        Store_Config::override('DefaultOutOfStockMessage', "Unavailable");

        $details = new Store_Product_Details();
        $details->setProduct(new ISC_PRODUCT);
        $details->setProduct($this->makeProductWithInventoryTracking(2));

        $storefront = $details->toStoreFrontDetails($GLOBALS['ISC_CLASS_TEMPLATE']);
        $this->assertEquals("Unavailable", $storefront['outOfStockMessage']);
    }
}
