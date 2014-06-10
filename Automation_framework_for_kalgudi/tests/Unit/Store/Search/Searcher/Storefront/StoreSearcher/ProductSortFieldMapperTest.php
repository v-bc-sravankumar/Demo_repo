<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher;

use Store\Search\Searcher\Storefront\StoreSearcher\ProductSortFieldMapper;
use Bigcommerce\SearchClient\Parameters;

class ProductSortFieldMapperTest extends \PHPUnit_Framework_TestCase
{
    public function mapFieldDataProvider()
    {
        return array(
            array('relevance',         '',               ''),
            array('featured',          'is_featured',    Parameters::SORT_DESCENDING),
            array('newest',            'date_created',   Parameters::SORT_DESCENDING),
            array('bestselling',       'quantity_sold',  Parameters::SORT_DESCENDING),
            array('alphaasc',          'name',           Parameters::SORT_ASCENDING),
            array('alphadesc',         'name',           Parameters::SORT_DESCENDING),
            array('avgcustomerreview', 'average_rating', Parameters::SORT_DESCENDING),
        );
    }

    /**
     * @dataProvider mapFieldDataProvider
     */
    public function testMapField($field, $mappedField, $mappedSortOrder)
    {
        $shopper = $this->getMock('\Store_Shopper', array('getTaxZoneIdForProductSearching'));
        $shopper
            ->expects($this->once())
            ->method('getTaxZoneIdForProductSearching')
            ->will($this->returnValue(false));

        $mapper = new ProductSortFieldMapper($shopper);
        $mapping = $mapper->map($field);

        $this->assertEquals($mappedField, $mapping->getSortField());
        $this->assertEquals($mappedSortOrder, $mapping->getSortOrder());
    }

    public function priceFieldDataProvider()
    {
        return array(
            array('priceasc', Parameters::SORT_ASCENDING),
            array('pricedesc', Parameters::SORT_DESCENDING),
        );
    }

    /**
     * @dataProvider priceFieldDataProvider
     */
    public function testPriceFieldForShopperInTaxZone($field, $mappedSortOrder)
    {
        $shopper = $this->getMock('\Store_Shopper', array('getTaxZoneIdForProductSearching'));
        $shopper
            ->expects($this->once())
            ->method('getTaxZoneIdForProductSearching')
            ->will($this->returnValue(5));

        $mapper = new ProductSortFieldMapper($shopper);
        $mapping = $mapper->map($field);

        $this->assertEquals('prices.tax_zone_prices.tax_zone_id_5', $mapping->getSortField());
        $this->assertEquals($mappedSortOrder, $mapping->getSortOrder());
    }

    /**
     * @dataProvider priceFieldDataProvider
     */
    public function testPriceFieldForShopperNotInTaxZone($field, $expectedSortOrder)
    {
        $shopper = $this->getMock('\Store_Shopper', array('getTaxZoneIdForProductSearching'));
        $shopper
            ->expects($this->once())
            ->method('getTaxZoneIdForProductSearching')
            ->will($this->returnValue(false));

        $mapper = new ProductSortFieldMapper($shopper);
        $mapping = $mapper->map($field);

        $this->assertEquals('prices.calculated', $mapping->getSortField());
        $this->assertEquals($expectedSortOrder, $mapping->getSortOrder());
    }
}
