<?php

namespace Unit\Store\Search\Searcher\Storefront\ParametersDecorator;

use Store_Shopper;
use Store\Search\Searcher\Storefront\ParametersDecorator\PostParametersDecorator;
use Bigcommerce\SearchClient\Parameters;

class PostParametersDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyFiltersAddIsVisibleFilter()
    {
        $parameters = new Parameters();

        $decorator = new PostParametersDecorator(new Store_Shopper());
        $decorator->decorate($parameters);

        $filters = $parameters->getFiltersForField('is_visible');
        $this->assertNotEmpty($filters);

        $filter = $filters[0];
        $this->assertTrue($filter->getValue());
    }
}
