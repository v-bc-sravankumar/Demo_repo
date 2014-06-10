<?php

namespace Unit\Store\Search\Searcher\Storefront\ParametersDecorator;

use Store_Shopper;
use Store\Search\Searcher\Storefront\ParametersDecorator\AbstractShopperParametersDecorator;
use Bigcommerce\SearchClient\Parameters;

class AbstractShopperParametersDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testDecorateAppliesFilters()
    {
        $shopper = new Store_Shopper();
        $parameters = new Parameters();

        $decorator = $this->getMockBuilder('\Store\Search\Searcher\Storefront\ParametersDecorator\AbstractShopperParametersDecorator')
            ->setConstructorArgs(array($shopper))
            ->setMethods(array('applyFilters'))
            ->getMock();

        $decorator
            ->expects($this->once())
            ->method('applyFilters')
            ->with($this->equalTo($parameters));

        $decorator->decorate($parameters);
    }
}
