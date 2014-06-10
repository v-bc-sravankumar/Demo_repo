<?php

namespace Unit\Store\Search\Searcher\Storefront\ResultsAdapter;

use Store\Search\Searcher\Storefront\ResultsAdapter\LegacySearchResultsAdapter;

class LegacySearchResultsAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \ISC_SEARCH|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getController()
    {
        return $this->getMockBuilder('\ISC_SEARCH')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \ISC_SEARCH $controller
     * @return LegacySearchResultsAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdapter($controller = null)
    {
        if ($controller === null) {
            $controller = $this->getController();
        }

        $adapter = $this->getMockBuilder('\Store\Search\Searcher\Storefront\ResultsAdapter\LegacySearchResultsAdapter')
            ->setMethods(array('getSearchClass'))
            ->getMock();

        $adapter
            ->expects($this->once())
            ->method('getSearchClass')
            ->will($this->returnValue($controller));

        return $adapter;
    }

    public function testHasSearchBeenPerformedIsTrueIfSearchIsLoaded()
    {
        $controller = $this->getController();
        $controller
            ->expects($this->once())
            ->method('searchIsLoaded')
            ->will($this->returnValue(true));

        $adapter = $this->getAdapter($controller);
        $this->assertTrue($adapter->hasSearchBeenPerformed());
    }

    public function testHasSearchBeenPerformedIsFalseIfSearchIsNotLoaded()
    {
        $controller = $this->getController();
        $controller
            ->expects($this->once())
            ->method('searchIsLoaded')
            ->will($this->returnValue(false));

        $adapter = $this->getAdapter($controller);
        $this->assertFalse($adapter->hasSearchBeenPerformed());
    }

    public function testCountSearchResultsForOneType()
    {
        $type = 'foo';

        $controller = $this->getController();
        $controller
            ->expects($this->once())
            ->method('GetNumResults')
            ->with(array($type))
            ->will($this->returnValue(255));

        $adapter = $this->getAdapter($controller);
        $this->assertEquals(255, $adapter->countSearchResults($type));
    }

    public function testCountSearchResultsForManyTypes()
    {
        $types = array('foo', 'bar');

        $controller = $this->getController();
        $controller
            ->expects($this->once())
            ->method('GetNumResults')
            ->with($types)
            ->will($this->returnValue(3887));

        $adapter = $this->getAdapter($controller);
        $this->assertEquals(3887, $adapter->countSearchResults($types));
    }

    public function testGetResultsForType()
    {
        $type        = 'foo';
        $results     = array('results' => array('hello', 'world'));
        $transformed = array('results' => array(array('data' => 'hello'), array('data' => 'world')));

        $controller = $this->getController();
        $controller
            ->expects($this->once())
            ->method('GetResults')
            ->with($type)
            ->will($this->returnValue($results));

        $adapter = $this->getAdapter($controller);
        $this->assertEquals($transformed['results'], $adapter->getResults($type));
    }
}
