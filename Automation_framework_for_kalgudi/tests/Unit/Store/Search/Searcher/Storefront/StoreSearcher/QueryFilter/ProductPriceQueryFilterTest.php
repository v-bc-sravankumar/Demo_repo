<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\QueryFilter;

use Language\LanguageManager;
use Store\Search\Searcher\Storefront\StoreSearcher\QueryFilter\ProductPriceQueryFilter;

class ProductPriceQueryFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return LanguageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLanguageManager()
    {
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'get'))
            ->getMock();

        $languageManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));

        return $languageManager;
    }

    /**
     * @return \Store_Settings
     */
    private function getSettings()
    {
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'CurrencyToken'  => '$',
            'DecimalPlaces'  => 2,
            'DecimalToken'   => '.',
            'ThousandsToken' => ',',
        )));
        $settings->load();

        return $settings;
    }

    public function testConstructor()
    {
        $languageManager = $this->getLanguageManager();
        $settings        = $this->getSettings();

        $filter = new ProductPriceQueryFilter($languageManager, $settings, 'test-filter');

        // Assert that the language manager is set correctly.
        $this->assertAttributeEquals($languageManager, 'languageManager', $filter);

        // Assert that the settings object is set correctly.
        $this->assertAttributeEquals($settings, 'settings', $filter);

        // Assert that the filter field is set correctly.
        $this->assertAttributeEquals('test-filter', 'filterField', $filter);
    }

    public function testGetRegexPattern()
    {
        $strangeSettings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'CurrencyToken'  => '(',
            'DecimalPlaces'  => 2,
            'DecimalToken'   => '+',
            'ThousandsToken' => '?',
        )));
        $strangeSettings->load();

        $filter = new TestableProductPriceQueryFilter(
            $this->getLanguageManager(),
            $strangeSettings
        );

        $pattern = $filter->getRegexPattern();

        // Ensure that the store currency is passed through preg_quote().
        // Ensure that the decimal token is passed through preg_quote().
        // Ensure that the thousands separator is passed through preg_quote().
        $this->assertEquals('SearchLangPrice:(<|>)?([0-9\$\(\+\?]+)-?([0-9\$\(\+\?]+)?', $pattern);

        $filter = new TestableProductPriceQueryFilter(
            $this->getLanguageManager(),
            $this->getSettings()
        );

        $inputs =  array(
            // Ensure that the following patterns (with no token customisation) are matched:
            'SearchLangPrice:>$1.00',
            'SearchLangPrice:<$1.00',
            'SearchLangPrice:$1.00-$5.00',
            'SearchLangPrice:$1.00',
            'SearchLangPrice:$1,000.00',

            // Ensure that using a different decimal token works:
            'SearchLangPrice:>$1+00',

            // Ensure that using a different thousands token works:
            'SearchLangPrice:>$1?000+00',

            // Ensure that using a different currency token works:
            'SearchLangPrice:>(1?000+00',
        );

        foreach ($inputs as $input) {
            $this->assertEquals(1, preg_match("#$pattern#", $input));
        }

        // Ensure that some other pattern is not matched.
        $this->assertEquals(0, preg_match("#$pattern#", "this ain't right"));
    }

    public function testGetFiltersFromRegexMatches()
    {
        $settings    = $this->getSettings();
        $oldInstance = \Store_Config::getInstance();
        \Store_Config::setInstance($settings);

        $filter = new TestableProductPriceQueryFilter(
            $this->getLanguageManager(),
            $settings,
            'test-field'
        );

        $pattern = $filter->getRegexPattern();

        $inputs = array(
            'SearchLangPrice:$1,000.00',
        );

        preg_match("#$pattern#", 'SearchLangPrice:>$1.00', $matches);
        $filters = $filter->getFiltersFromRegexMatches($matches);
        $this->assertEquals(1, count($filters));
        $this->assertAttributeEquals(null, 'min', $filters[0]);
        $this->assertAttributeEquals(1.0, 'max', $filters[0]);
        $this->assertAttributeEquals('test-field', 'field', $filters[0]);

        preg_match("#$pattern#", 'SearchLangPrice:<$1.00', $matches);
        $filters = $filter->getFiltersFromRegexMatches($matches);
        $this->assertEquals(1, count($filters));
        $this->assertAttributeEquals(1.0, 'min', $filters[0]);
        $this->assertAttributeEquals(null, 'max', $filters[0]);
        $this->assertAttributeEquals('test-field', 'field', $filters[0]);

        preg_match("#$pattern#", 'SearchLangPrice:$1.00-$5.00', $matches);
        $filters = $filter->getFiltersFromRegexMatches($matches);
        $this->assertEquals(1, count($filters));
        $this->assertAttributeEquals(1.0, 'min', $filters[0]);
        $this->assertAttributeEquals(5.0, 'max', $filters[0]);
        $this->assertAttributeEquals('test-field', 'field', $filters[0]);

        preg_match("#$pattern#", 'SearchLangPrice:$1.00', $matches);
        $filters = $filter->getFiltersFromRegexMatches($matches);
        $this->assertEquals(1, count($filters));
        $this->assertAttributeEquals(1.0, 'value', $filters[0]);
        $this->assertAttributeEquals('test-field', 'field', $filters[0]);

        preg_match("#$pattern#", 'SearchLangPrice:$1,000.00', $matches);
        $filters = $filter->getFiltersFromRegexMatches($matches);
        $this->assertEquals(1, count($filters));
        $this->assertAttributeEquals(1000.0, 'value', $filters[0]);
        $this->assertAttributeEquals('test-field', 'field', $filters[0]);

        \Store_Config::setInstance($oldInstance);
    }
}

class TestableProductPriceQueryFilter extends ProductPriceQueryFilter
{
    /**
     * {@inheritdoc}
     */
    public function getRegexPattern()
    {
        return parent::getRegexPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltersFromRegexMatches($matches)
    {
        return parent::getFiltersFromRegexMatches($matches);
    }
}
