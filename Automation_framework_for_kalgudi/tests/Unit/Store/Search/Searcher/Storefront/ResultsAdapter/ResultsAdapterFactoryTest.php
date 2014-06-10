<?php

namespace Unit\Store\Search\Searcher\Storefront\ResultsAdapter;

use Store\Search\Searcher\Storefront\ResultsAdapter\ResultsAdapterFactory;

class ResultsAdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        \Config\Experiment::disableCache();
    }

    public function testGetResultsAdapterWhenFullyRampedUp()
    {
        \Config\Experiment::addSelector(
            'elastic_%_rampup',
            new \Abacus\Selectors\WeightedPercentSelector(
                array(
                    'mysql'   => 0,
                    'elastic' => 100
                ),
                new \Abacus\Selectors\HashRandomizer('search.storefront.backend')
            )
        );

        \Config\Experiment::define(array(
            'id'          => 'search.storefront.backend',
            'description' => 'Enable new Elasticsearch storefront search provider',
            'selector'    => 'elastic_%_rampup',
            'variants'    => array('mysql', 'elastic'),
        ));

        $adapter = ResultsAdapterFactory::getResultsAdapter();
        $this->assertInstanceOf('Store\Search\Searcher\Storefront\ResultsAdapter\ResultStoreResultsAdapter', $adapter);
    }

    public function testGetResultsAdapterWhenExperimentDisabled()
    {
        \Config\Experiment::addSelector(
            'elastic_%_rampup',
            new \Abacus\Selectors\WeightedPercentSelector(
                array(
                    'mysql'   => 100,
                    'elastic' => 0
                ),
                new \Abacus\Selectors\HashRandomizer('search.storefront.backend')
            )
        );

        \Config\Experiment::define(array(
            'id'          => 'search.storefront.backend',
            'description' => 'Enable new Elasticsearch storefront search provider',
            'selector'    => 'elastic_%_rampup',
            'variants'    => array('mysql', 'elastic'),
        ));

        $enabled = \Store_Feature::isEnabled('SearchController');
        \Store_Feature::override('SearchController', false);

        $adapter = ResultsAdapterFactory::getResultsAdapter();
        $this->assertInstanceOf('Store\Search\Searcher\Storefront\ResultsAdapter\LegacySearchResultsAdapter', $adapter);

        \Store_Feature::override('SearchController', $enabled);
    }
}
