<?php

namespace Unit\Store\Search\Searcher\Storefront\ParametersDecorator;

use Store_Shopper;
use Store\Search\Searcher\Storefront\ParametersDecorator\CategoryParametersDecorator;
use Bigcommerce\SearchClient\Parameters;
use Bigcommerce\SearchClient\Filter\IdsFilter;
use Bigcommerce\SearchClient\Filter\AnyFilter;

class CategoryParametersDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyFiltersAddIsVisibleFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));

        $parameters = new Parameters();

        $decorator = new CategoryParametersDecorator($shopper);
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

    public function testShopperWithoutCategoryRestrictionsDoesntHaveIdsFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue(true));

        $parameters = new Parameters();

        $decorator = new CategoryParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $idsFilter = null;
        foreach ($parameters->getFilters() as $filter) {
            if ($filter instanceof IdsFilter) {
                $idsFilter = $filter;
                break;
            }
        }

        $this->assertNull($idsFilter, 'Parameters should not have an ids filter');
    }

    public function testShopperWithoutAnyCategoryAccessHasNoResultsFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue(false));

        $parameters = new Parameters();

        $decorator = new CategoryParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $this->assertTrue($parameters->hasNoResultsFilter());
    }

    public function testShopperWithSpecificCategoryAccessHasIdsFilter()
    {
        $categories = array(4,6,13,23);

        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue($categories));

        $parameters = new Parameters();

        $decorator = new CategoryParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $idsFilter = null;
        foreach ($parameters->getFilters() as $filter) {
            if ($filter instanceof IdsFilter) {
                $idsFilter = $filter;
                break;
            }
        }

        $this->assertNotNull($filter, 'Parameters should have an ids filter');
        $this->assertEquals($categories, $filter->getIds());
        $this->assertEmpty($parameters->getFiltersForField('category_ids'), 'Parameters should not have a category_ids filter.');
    }

    public function testShopperWithSpecificCategoryAccessMergesAnyFilterToCreateIdsFilter()
    {
        // categories customer can access
        $categories = array(4,6,13,23);
        // categories a customer chose (or forged)
        $chosenCategories = array(4,5,6,20);
        // should only be able to access 4 and 6
        $expected = array(4,6);

        $shopper = $this->getMock('\Store_Shopper', array('getAccessibleCategories'));
        $shopper
            ->expects($this->once())
            ->method('getAccessibleCategories')
            ->will($this->returnValue($categories));

        $parameters = new Parameters();
        $parameters->addFilter(new AnyFilter('category_ids', $chosenCategories));

        $decorator = new CategoryParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $idsFilter = null;
        foreach ($parameters->getFilters() as $filter) {
            if ($filter instanceof IdsFilter) {
                $idsFilter = $filter;
                break;
            }
        }

        $this->assertNotNull($filter, 'Parameters should have an ids filter');
        $this->assertEquals($expected, array_values($filter->getIds()));
        $this->assertEmpty($parameters->getFiltersForField('category_ids'), 'Parameters should not have a category_ids filter.');
    }
}
