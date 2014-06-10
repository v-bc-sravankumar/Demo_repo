<?php

namespace Unit\Store\Search\Searcher\Storefront;

use Store\Search\Searcher\Storefront\ShopperSearcher;
use Bigcommerce\SearchClient\Parameters;

class ShopperSearcherTest extends \PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        $parameters = new Parameters();

        /** @var DecoratedParametersSearcher|\PHPUnit_Framework_MockObject_MockObject $searcher */
        $searcher = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Searcher\DecoratedParametersSearcher')
            ->disableOriginalConstructor()
            ->setMethods(array('search'))
            ->getMock();

        $results = new \stdClass();

        // Assert that the search method on the searcher is invoked with the correct parameters.
        $searcher
            ->expects($this->at(0))
            ->method('search')
            ->with($this->equalTo($parameters))
            ->will($this->returnValue($results));

        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->getMock();

        $shopperSearcher = new ShopperSearcher(
            $searcher,
            new \Store_Shopper(),
            $languageManager,
            new \Store_Settings()
        );

        $result = $shopperSearcher->search($parameters);
        $this->assertAttributeEquals(array('all' => $results), 'results', $result);
    }
}
