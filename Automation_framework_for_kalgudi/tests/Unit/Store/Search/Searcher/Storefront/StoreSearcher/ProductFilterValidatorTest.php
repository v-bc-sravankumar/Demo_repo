<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher;

use Store\Search\Searcher\Storefront\StoreSearcher\ProductFilterValidator;
use Store\Settings\InventorySettings;

class ProductFilterValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $backupSettings;

    public function setUp()
    {
        $this->backupSettings = \Store_Config::getInstance();

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'CurrencyToken'  => '$',
            'DecimalToken'   => '.',
            'ThousandsToken' => ',',
            'DecimalPlaces'  => 2,
        )));
        $settings->load();

        \Store_Config::setInstance($settings);
    }

    public function tearDown()
    {
        \Store_Config::setInstance($this->backupSettings);
    }

    private function getValidator($inventorySettings = null)
    {
        if ($inventorySettings === null) {
            $inventorySettings = new InventorySettings();
        }

        return new ProductFilterValidator($inventorySettings);
    }

    private function validateFilters($filters, $expectedKey, $expectedValue, $message = null, $validator = null)
    {
        if ($validator === null) {
            $validator = $this->getValidator();
        }

        $validatedFilters = $validator->validate($filters);

        $this->assertArrayHasKey($expectedKey, $validatedFilters);
        $this->assertSame($expectedValue, $validatedFilters[$expectedKey], $message);
    }

    public function testCategoryIdIsConvertedToCategories()
    {
        $filters = array(
            'categoryid' => '5',
        );

        $this->validateFilters($filters, 'categories', array(5));
    }

    public function testCategoryIsConvertedToCategories()
    {
        $filters = array(
            'category' => '13',
        );

        $this->validateFilters($filters, 'categories', array(13));
    }

    public function testCategoryArrayConvertedToCategories()
    {
        $filters = array(
            'category' => array('3',10,14,'15'),
        );

        $this->validateFilters($filters, 'categories', array(3,10,14,15));
    }

    public function boolFilterDataProvider()
    {
        return array(
            array('searchsubs'),
        );
    }

    /**
     * @dataProvider boolFilterDataProvider
     */
    public function testBoolFilters($filter)
    {
        $trueValues = array(
            1,
            '1',
            '2',
            'hi',
        );

        foreach ($trueValues as $i => $value) {
            $filters = array(
                $filter => $value,
            );

            $this->validateFilters($filters, $filter, 1, $filter . ' should be 1 for value ' . $i . ': ' . $value);
        }

        $filters = array(
            $filter => '',
        );

        $this->validateFilters($filters, $filter, 0, $filter . ' should be 0 when empty value given');

        $this->validateFilters(array(), $filter, 0, $filter . ' should be 0 when field not supplied');
    }

    public function testPriceIsConvertedFromFriendlyFormat()
    {
        $filters = array(
            'price' => ' 1,234.567 ',
        );

        $this->validateFilters($filters, 'price', '1234.57');
    }

    public function testPriceFromIsConvertedFromFriendlyFormat()
    {
        $filters = array(
            'price_from' => ' 32,442.9',
        );

        $this->validateFilters($filters, 'price_from', '32442.90');
    }

    public function testPriceToIsConvertedFromFriendlyFormat()
    {
        $filters = array(
            'price_to' => ' 93 ',
        );

        $this->validateFilters($filters, 'price_to', '93.00');
    }

    public function testFreeShippingIsConvertedToShipping()
    {
        $filters = array(
            'freeshipping' => '1',
        );

        $this->validateFilters($filters, 'shipping', 1);
    }

    public function numericOptionFilterDataProvider()
    {
        return array(
            array('featured'),
            array('shipping'),
            array('instock'),
        );
    }

    /**
     * @dataProvider numericOptionFilterDataProvider
     */
    public function testNumericOptionFilters($filter)
    {
        $numericValues = array(
            array(0, 0),
            array('0', 0),
            array(1, 1),
            array('1', 1),
            array(2, 2),
            array('2', 2),
        );

        foreach ($numericValues as $valuePair) {
            list($value, $expected) = $valuePair;

            $filters = array(
                $filter => $value,
            );

            $this->validateFilters($filters, $filter, $expected, $filter . ' should be ' . $expected .' for value ' . $value);
        }

        $filters = array(
            $filter => '',
        );

        $this->validateFilters($filters, $filter, 0, $filter . ' should be 0 when empty value given');

        $this->validateFilters(array(), $filter, 0, $filter . ' should be 0 when field not supplied');
    }

    public function testHideProductIfOutOfStockSettingOverridesInStock()
    {
        $backupSetting = \Store_Feature::isEnabled('InventorySettings');
        \Store_Feature::override('InventorySettings', true);

        $inventorySettings = new InventorySettings();
        $inventorySettings->setProductOutOfStockBehavior(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE);

        $validator = $this->getValidator($inventorySettings);

        $filters = array(
            'instock' => '0',
        );

        $this->validateFilters($filters, 'instock', 1, null, $validator);

        \Store_Feature::override('InventorySettings', $backupSetting);
    }
}
