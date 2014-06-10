<?php

namespace Unit\Store\Search\Searcher\Storefront\ParametersDecorator;

use Store_Shopper;
use Store\Search\Searcher\Storefront\ParametersDecorator\ProductParametersDecorator;
use Bigcommerce\SearchClient\Parameters;
use Bigcommerce\SearchClient\Filter\AnyFilter;

class ProductParametersDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyFiltersAddIsVisibleFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));

        $parameters = new Parameters();

        $decorator = new ProductParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFilters();
        $this->assertNotEmpty($filters);

        $fields = array('is_visible');

        foreach ($filters as $filter) {
            if ($filter->appliesTo($fields)) {
                break;
            }
        }

        $this->assertTrue($filter->getValue());
    }

    public function testShopperWithoutCategoryRestrictionsDoesntHaveCategoryFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue(true));

        $parameters = new Parameters();

        $decorator = new ProductParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFilters();
        $this->assertNotEmpty($filters);

        $fields = array('categories');

        $categoriesFilter = null;
        foreach ($filters as $filter) {
            if ($filter->appliesTo($fields)) {
                $categoriesFilter = $filter;
                break;
            }
        }

        $this->assertNull($categoriesFilter, 'Parameters should not have a categories filter');
    }

    public function testShopperWithoutAnyCategoryAccessHasNoResultsFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue(false));

        $parameters = new Parameters();

        $decorator = new ProductParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $this->assertTrue($parameters->hasNoResultsFilter());
    }

    public function testShopperWithSpecificCategoryAccessHasCategoryFilter()
    {
        $categories = array(1,5,10,15);

        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue($categories));

        $parameters = new Parameters();

        $decorator = new ProductParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFilters();
        $this->assertNotEmpty($filters);

        $fields = array('category_ids');

        foreach ($filters as $filter) {
            if ($filter->appliesTo($fields)) {
                break;
            }
        }

        $this->assertInstanceOf('\Bigcommerce\SearchClient\Filter\AnyFilter', $filter);
        $this->assertEquals($categories, $filter->getTerms());
    }

    public function testShopperWithSpecificCategoryAccessMergesWithExistingCategoriesFilter()
    {
        // categories customer can access
        $categories = array(1,5,10,15);
        // categories a customer chose (or forged)
        $chosenCategories = array(1,4,5,20);
        // should only be able to access 1 and 5
        $expected = array(1,5);

        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue($categories));

        $parameters = new Parameters();
        $parameters->addFilter(new AnyFilter('category_ids', $chosenCategories));

        $decorator = new ProductParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFilters();
        $this->assertNotEmpty($filters);

        $fields = array('category_ids');

        foreach ($filters as $filter) {
            if ($filter->appliesTo($fields)) {
                break;
            }
        }

        $this->assertInstanceOf('\Bigcommerce\SearchClient\Filter\AnyFilter', $filter);
        $this->assertEquals($expected, array_values($filter->getTerms()));
    }
}
