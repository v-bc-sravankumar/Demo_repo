<?php

namespace Unit\Store\Search\Searcher\Storefront\ParametersDecorator;

use Store_Shopper;
use Store\Search\Searcher\Storefront\ParametersDecorator\PageParametersDecorator;
use Bigcommerce\SearchClient\Parameters;

class PageParametersDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyFiltersAddIsVisibleFilter()
    {
        $shopper = $this->getMock('\Store_Shopper', array('isGuest'));

        $parameters = new Parameters();

        $decorator = new PageParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFiltersForField('is_visible');
        $this->assertNotEmpty($filters);

        $filter = $filters[0];
        $this->assertTrue($filter->getValue());
    }

    public function testApplyFiltersForGuestRestrictsToPublicPages()
    {
        $shopper = $this->getMock('\Store_Shopper', array('isGuest'));
        $shopper
            ->expects($this->once())
            ->method('isGuest')
            ->will($this->returnValue(true));

        $parameters = new Parameters();

        $decorator = new PageParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFiltersForField('customers_only');
        $this->assertNotEmpty($filters);

        $filter = $filters[0];
        $this->assertFalse($filter->getValue());
    }

    public function testApplyFiltersForRegisteredCustomerDoesntRestrictPages()
    {
        $shopper = $this->getMock('\Store_Shopper', array('isGuest'));
        $shopper
            ->expects($this->once())
            ->method('isGuest')
            ->will($this->returnValue(false));

        $parameters = new Parameters();

        $decorator = new PageParametersDecorator($shopper);
        $decorator->decorate($parameters);

        $filters = $parameters->getFiltersForField('customers_only');
        $this->assertEmpty($filters, 'Parameters should not have a customers_only filter');
    }
}
