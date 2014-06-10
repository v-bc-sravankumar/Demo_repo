<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher;

use Bigcommerce\SearchClient\Parameters;
use Bigcommerce\SearchClient\Provider\ProviderInterface;
use Bigcommerce\SearchClient\Result\ResultSet;
use Bigcommerce\SearchClient\Searcher\DecoratedParametersSearcher;
use Bigcommerce\SearchClient\Searcher\ParametersDecorator\FilteredQueryParametersDecorator;
use Language\LanguageManager;
use Store\Search\Searcher\Storefront\StoreSearcher\StoreSearcher;
use Store_Shopper;

class StoreSearcherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return LanguageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLanguageManager()
    {
        /** @var \Language\LanguageManager|\PHPUnit_Framework_MockObject_MockObject $languageManager */
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'get'))
            ->getMock();

        return $languageManager;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Store_Shopper
     */
    private function getShopper()
    {
        /** @var \Store_Shopper|\PHPUnit_Framework_MockObject_MockObject $shopper */
        $shopper = $this
            ->getMockBuilder('Store_Shopper')
            ->disableOriginalConstructor()
            ->setMethods(array('getPriceFieldForProductSearching'))
            ->getMock();

        return $shopper;
    }

    /**
     * @return DecoratedParametersSearcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getParametersSearcher()
    {
        /** @var DecoratedParametersSearcher|\PHPUnit_Framework_MockObject_MockObject $parametersSearcher */
        $parametersSearcher = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Searcher\DecoratedParametersSearcher')
            ->disableOriginalConstructor()
            ->getMock();

        return $parametersSearcher;
    }

    public function testDecorateProductParameters()
    {
        /** @var FilteredQueryParametersDecorator|\PHPUnit_Framework_MockObject_MockObject $decorator */
        $decorator = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Searcher\ParametersDecorator\FilteredQueryParametersDecorator')
            ->disableOriginalConstructor()
            ->setMethods(array('addQueryFilter', 'decorate'))
            ->getMock();

        // Assert that the correct query filters are added to the product parameters.
        $filters = array(
            array(
                'type'  => 'Store\Search\Searcher\Storefront\StoreSearcher\QueryFilter\ProductPriceQueryFilter',
                'field' => 'test',
            ),
            array(
                'type'  => 'Bigcommerce\SearchClient\Searcher\QueryFilter\BooleanRegexQueryFilter',
                'field' => 'SearchLangFeatured',
            ),
            array(
                'type'  => 'Bigcommerce\SearchClient\Searcher\QueryFilter\BooleanRegexQueryFilter',
                'field' => 'SearchLangFreeShipping',
            ),
            array(
                'type'  => 'Bigcommerce\SearchClient\Searcher\QueryFilter\BooleanRegexQueryFilter',
                'field' => 'SearchLangInStock',
            ),
        );

        foreach ($filters as $index => $filter) {
            $decorator
                ->expects($this->at($index))
                ->method('addQueryFilter')
                ->with($this->logicalAnd(
                    $this->isInstanceOf($filter['type']),
                    $this->attributeEqualTo('filterField', $filter['field'])
                ))
                ->will($this->returnValue($decorator));
        }

        $parameters = new Parameters();

        // Assert that the Parameters object passed in is actually decorated.
        $decorator
            ->expects($this->at(count($filters)))
            ->method('decorate')
            ->with($this->equalTo($parameters));

        $languageManager = $this->getLanguageManager();

        // Assert that the language file is correctly loaded.
        $languageManager
            ->expects($this->at(0))
            ->method('load')
            ->with($this->equalTo('front_language'));

        // Fake language responses.
        $languageManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));

        $shopper = $this->getShopper();

        // Assert that the field type is asked of the shopper.
        $shopper
            ->expects($this->any())
            ->method('getPriceFieldForProductSearching')
            ->will($this->returnValue('test'));

        $searcher = new TestableStoreSearcher(
            $this->getParametersSearcher(),
            $shopper,
            $languageManager,
            new \Store_Settings()
        );

        $searcher->setFilteredQueryDecorator($decorator);
        $searcher->decorateProductParameters($parameters);
    }

    public function testCreateParametersForContentTypes()
    {
        /** @var TestableStoreSearcher|\PHPUnit_Framework_MockObject_MockObject $searcher */
        $searcher = $this
            ->getMockBuilder('Unit\Store\Search\Searcher\Storefront\StoreSearcher\TestableStoreSearcher')
            ->setConstructorArgs(array(
                $this->getParametersSearcher(),
                $this->getShopper(),
                $this->getLanguageManager(false),
                new \Store_Settings()
            ))
            ->setMethods(array('decorateProductParameters'))
            ->getMock();

        $parameters = new Parameters();
        $parameters->setQuery('test-query');

        // Assert that the product parameters are decorated.
        $searcher
            ->expects($this->at(0))
            ->method('decorateProductParameters')
            ->with($this->equalTo($parameters));

        /** @var Parameters[] $typeParameters */
        $typeParameters = $searcher->createParametersForContentTypes($parameters);

        // Assert that the query has been copied over correctly and that the product filters were not persisted.
        $this->assertCount(4, $typeParameters);

        // Assert that the correct types were set.
        $this->assertEquals($typeParameters['product']->getTypes(), array(ProviderInterface::TYPE_PRODUCT));
        $this->assertEquals($typeParameters['category']->getTypes(), array(ProviderInterface::TYPE_CATEGORY));
        $this->assertEquals($typeParameters['brand']->getTypes(), array(ProviderInterface::TYPE_BRAND));
        $this->assertEquals(
            $typeParameters['content']->getTypes(),
            array(ProviderInterface::TYPE_POST, ProviderInterface::TYPE_PAGE)
        );

        foreach ($typeParameters as $typeParameter) {
            $this->assertCount(0, $typeParameter->getFilters());
            $this->assertEquals('test-query', $typeParameter->getQuery());
        }
    }

    public function testSearch()
    {
        $parameterSearcher = $this->getParametersSearcher();

        $productParameters = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Parameters')
            ->setMethods(array('getQuery'))
            ->getMock();

        // Assert that the query is obtained from the product Parameters object.
        $productParameters
            ->expects($this->at(0))
            ->method('getQuery')
            ->will($this->returnValue('parsed-query'));

        $parametersSet = array('product' => $productParameters);

        $resultSet = new ResultSet(array(
            'product'  => 'result1',
            'category' => 'result2',
            'brand'    => 'result3',
            'content'  => 'result4',
        ));

        $parameters = new Parameters();

        // Assert that the results are grabbed from the parameter searcher.
        $parameterSearcher
            ->expects($this->at(0))
            ->method('bulkSearch')
            ->with($this->equalTo($parametersSet))
            ->will($this->returnValue($resultSet));

        /** @var TestableStoreSearcher|\PHPUnit_Framework_MockObject_MockObject $searcher */
        $searcher = $this
            ->getMockBuilder('Unit\Store\Search\Searcher\Storefront\StoreSearcher\TestableStoreSearcher')
            ->setConstructorArgs(array(
                    $parameterSearcher,
                    $this->getShopper(),
                    $this->getLanguageManager(false),
                    new \Store_Settings()
                ))
            ->setMethods(array('createParametersForContentTypes'))
            ->getMock();

        // Assert that the parameter set is created.
        $searcher
            ->expects($this->at(0))
            ->method('createParametersForContentTypes')
            ->with($this->equalTo($parameters))
            ->will($this->returnValue($parametersSet));

        $results = $searcher->search($parameters);

        // Assert that the correct results are returned.
        $this->assertEquals($results->getSearchResults(), array(
            'product'  => 'result1',
            'category' => 'result2',
            'brand'    => 'result3',
            'content'  => 'result4',
        ));
    }

    public function testCreateParametersForContentTypesProductsOnly()
    {
        $searcher = new TestableStoreSearcher(
            $this->getParametersSearcher(),
            $this->getShopper(),
            $this->getLanguageManager(false),
            new \Store_Settings()
        );

        $parameters = new Parameters('my search', array('product'));

        $parametersSet = $searcher->createParametersForContentTypes($parameters);

        $this->assertEquals(array('product' => $parameters), $parametersSet);
    }

    public function testCreateParametersForContentTypesContentOnly()
    {
        $searcher = new TestableStoreSearcher(
            $this->getParametersSearcher(),
            $this->getShopper(),
            $this->getLanguageManager(false),
            new \Store_Settings()
        );

        $parameters = new Parameters('my search', array('content'));

        $parametersSet = $searcher->createParametersForContentTypes($parameters);

        $expected = clone $parameters;
        $expected->setTypes(array('post', 'page'));

        $this->assertEquals(array('content' => $expected), $parametersSet);
    }
}

class TestableStoreSearcher extends StoreSearcher
{
    /**
     * {@inheritdoc}
     */
    public function decorateProductParameters(Parameters $parameters)
    {
        parent::decorateProductParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function createParametersForContentTypes(Parameters $parameters)
    {
        return parent::createParametersForContentTypes($parameters);
    }
}
