<?php

namespace Unit\Store\Search\Searcher\Storefront\QuickSearcher;

use Bigcommerce\SearchClient\Parameters;
use Bigcommerce\SearchClient\Provider\ProviderInterface;
use Bigcommerce\SearchClient\Searcher\DecoratedParametersSearcher;
use Language\LanguageManager;
use Store\Search\Searcher\Storefront\QuickSearcher\QuickSearcher;

class QuickSearcherTest extends \PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        /** @var Parameters|\PHPUnit_Framework_MockObject_MockObject $parameters */
        $parameters = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Parameters')
            ->disableOriginalConstructor()
            ->setMethods(array('setTypes', 'setPage', 'setLimit'))
            ->getMock();

        // Assert that the correct parameters are added.
        $parameters
            ->expects($this->at(0))
            ->method('setTypes')
            ->with($this->equalTo(array(
                ProviderInterface::TYPE_PRODUCT,
                ProviderInterface::TYPE_PAGE,
                ProviderInterface::TYPE_POST,
            )))
            ->will($this->returnValue($parameters));
        $parameters
            ->expects($this->at(1))
            ->method('setPage')
            ->with($this->equalTo(1))
            ->will($this->returnValue($parameters));
        $parameters
            ->expects($this->at(2))
            ->method('setLimit')
            ->with($this->equalTo(5))
            ->will($this->returnValue($parameters));

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

        // Assert that a valid SearcherSearchResult object is returned.

        /** @var LanguageManager|\PHPUnit_Framework_MockObject_MockObject $languageManager */
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->getMock();

        $quickSearcher = new QuickSearcher(
            $searcher,
            new \Store_Shopper(),
            $languageManager,
            new \Store_Settings()
        );
        $result = $quickSearcher->search($parameters);
        $this->assertAttributeEquals(array('all' => $results), 'results', $result);
    }
}
