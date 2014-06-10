<?php

namespace Unit\App\Model\Store;

use Bigcommerce\SearchClient\Document\ProductDocument;
use Bigcommerce\SearchClient\Filter\IdsFilter;
use Bigcommerce\SearchClient\Filter\RangeFilter;
use Bigcommerce\SearchClient\Filter\ValueFilter;
use Bigcommerce\SearchClient\Parameters;
use Bigcommerce\SearchClient\Provider\ProviderInterface;
use DomainModel\Query\Pager;
use DomainModel\Query\Sorter;

class ProductSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $searchVars
     * @return array
     */
    private function buildParameters(array $searchVars)
    {
        $pager      = new Pager(1, 2);
        $sorter     = new Sorter('inventory_level', 'asc');
        $parameters = \Store_ProductSearch::buildParameters($searchVars, $pager, $sorter);
        $types      = $parameters->getTypes();
        $filters    = $parameters->getFilters();

        $filters->rewind();

        return array($parameters, $types, $filters);
    }

    public function testBuildParametersFilter()
    {
        $searchVars = array('filter' => array('keyword_filter' => 'test-keyword-filter'));

        /** @var Parameters $parameters */
        /** @var string[] $types */
        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertEquals(1, $parameters->getPage());
        $this->assertEquals(2, $parameters->getLimit());
        $this->assertEquals('inventory_level', $parameters->getSortField());
        $this->assertEquals(Parameters::SORT_ASCENDING, $parameters->getSortOrder());
        $this->assertEquals($types, array(ProviderInterface::TYPE_PRODUCT));
        $this->assertCount(0, $filters);
        $this->assertEquals('test-keyword-filter', $parameters->getQuery());
    }

    public function testBuildParametersProductId()
    {
        $searchVars = array('productId' => array(1, 2, 3, 4, 5));

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var IdsFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\IdsFilter', $filter);
        $this->assertEquals(array(1, 2, 3, 4, 5), $filter->getIds());
    }

    public function testBuildParametersNameFirstLetter()
    {
        $searchVars = array('letter' => 'A');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('name_first_letter', $filter->getField());
        $this->assertEquals('a', $filter->getValue());
    }

    public function testBuildParametersQuantitySoldFrom()
    {
        $searchVars = array('soldFrom' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('quantity_sold', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(null, $filter->getMax());
    }

    public function testBuildParametersQuantitySoldTo()
    {
        $searchVars = array('soldTo' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('quantity_sold', $filter->getField());
        $this->assertEquals(null, $filter->getMin());
        $this->assertEquals(123, $filter->getMax());
    }

    public function testBuildParametersQuantitySoldBetween()
    {
        $searchVars = array('soldFrom' => 123, 'soldTo' => 456);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('quantity_sold', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(456, $filter->getMax());
    }

    public function testBuildParametersPriceFrom()
    {
        $searchVars = array('priceFrom' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('prices.calculated', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(null, $filter->getMax());
    }

    public function testBuildParametersPriceTo()
    {
        $searchVars = array('priceTo' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('prices.calculated', $filter->getField());
        $this->assertEquals(null, $filter->getMin());
        $this->assertEquals(123, $filter->getMax());
    }

    public function testBuildParametersPriceBetween()
    {
        $searchVars = array('priceFrom' => 123, 'priceTo' => 456);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('prices.calculated', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(456, $filter->getMax());
    }

    public function testBuildParametersInventoryFrom()
    {
        $searchVars = array('inventoryFrom' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('inventory_level', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(null, $filter->getMax());
    }

    public function testBuildParametersInventoryTo()
    {
        $searchVars = array('inventoryTo' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('inventory_level', $filter->getField());
        $this->assertEquals(null, $filter->getMin());
        $this->assertEquals(123, $filter->getMax());
    }

    public function testBuildParametersInventoryBetween()
    {
        $searchVars = array('inventoryFrom' => 123, 'inventoryTo' => 456);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var RangeFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\RangeFilter', $filter);
        $this->assertEquals('inventory_level', $filter->getField());
        $this->assertEquals(123, $filter->getMin());
        $this->assertEquals(456, $filter->getMax());
    }

    public function testBuildParametersBrand()
    {
        $searchVars = array('brand' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('brand_id', $filter->getField());
        $this->assertEquals(1, $filter->getValue());
    }

    public function testBuildParametersVisible()
    {
        $searchVars = array('visibility' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('is_visible', $filter->getField());
        $this->assertEquals(true, $filter->getValue());
    }

    public function testBuildParametersNotVisible()
    {
        $searchVars = array('visibility' => '0');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('is_visible', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }

    public function testBuildParametersFeatured()
    {
        $searchVars = array('featured' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('is_featured', $filter->getField());
        $this->assertEquals(true, $filter->getValue());
    }

    public function testBuildParametersNotFeatured()
    {
        $searchVars = array('featured' => '0');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('is_featured', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }

    public function testBuildParametersAvailabilityAvailable()
    {
        $searchVars = array('status' => 'selling');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('availability', $filter->getField());
        $this->assertEquals(ProductDocument::AVAILABILITY_AVAILABLE, $filter->getValue());
    }

    public function testBuildParametersAvailabilityPreorder()
    {
        $searchVars = array('status' => 'preorder');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('availability', $filter->getField());
        $this->assertEquals(ProductDocument::AVAILABILITY_PREORDER, $filter->getValue());
    }

    public function testBuildParametersAvailabilityDisabled()
    {
        $searchVars = array('status' => 'catalogue');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('availability', $filter->getField());
        $this->assertEquals(ProductDocument::AVAILABILITY_DISABLED, $filter->getValue());
    }

    public function testBuildParametersFreeShipping()
    {
        $searchVars = array('freeShipping' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('has_free_shipping', $filter->getField());
        $this->assertEquals(true, $filter->getValue());
    }

    public function testBuildParametersNotFreeShipping()
    {
        $searchVars = array('freeShipping' => '0');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('has_free_shipping', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }

    public function testBuildParametersProductType()
    {
        $searchVars = array('productType' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('product_type_id', $filter->getField());
        $this->assertEquals(123, $filter->getValue());
    }

    public function testBuildParametersNoProductType()
    {
        $searchVars = array('productType' => 'NULL');

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('product_type_id', $filter->getField());
        $this->assertEquals(0, $filter->getValue());
    }

    public function testBuildParametersAttributeId()
    {
        $searchVars = array('optionId' => 123);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('attributes.id', $filter->getField());
        $this->assertEquals(123, $filter->getValue());
    }

    public function testBuildParametersNotDigital()
    {
        $searchVars = array('ignoreDownloadableProducts' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('is_digital', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }

    public function testBuildParametersNotConfigurable()
    {
        $searchVars = array('ignoreConfigurableProducts' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('has_configurable_fields', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }

    public function testBuildParametersNotDateField()
    {
        $searchVars = array('ignoreProductsWithDateFields' => 1);

        /** @var \SplObjectStorage $filters */
        list($parameters, $types, $filters) = $this->buildParameters($searchVars);

        $this->assertCount(1, $filters);

        /** @var ValueFilter $filter */
        $filter = $filters->current();

        $this->assertInstanceOf('Bigcommerce\SearchClient\Filter\ValueFilter', $filter);
        $this->assertEquals('has_event_date', $filter->getField());
        $this->assertEquals(false, $filter->getValue());
    }
}