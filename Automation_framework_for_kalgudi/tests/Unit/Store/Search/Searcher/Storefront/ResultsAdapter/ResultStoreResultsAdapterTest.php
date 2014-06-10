<?php

namespace Unit\Store\Search\Searcher\Storefront\ResultsAdapter;

use Store\Search\Searcher\Storefront\ResultsAdapter\ResultStoreResultsAdapter;
use Bigcommerce\SearchClient\Result\ResultStore;

class ResultStoreResultsAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        ResultStore::clearResults();
    }

    private function getMockResult()
    {
        return $this->getMockBuilder('\Bigcommerce\SearchClient\Result\Result')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHasSearchBeenPerformedIsTrueIfResultsExists()
    {
        ResultStore::storeResult('foo', $this->getMockResult());
        $adapter = new ResultStoreResultsAdapter();
        $this->assertTrue($adapter->hasSearchBeenPerformed());
    }

    public function testHasSearchBeenPerformedIsFalseIfResultsDontExists()
    {
        $adapter = new ResultStoreResultsAdapter();
        $this->assertFalse($adapter->hasSearchBeenPerformed());
    }

    public function testCountSearchResultsForOneType()
    {
        $result1 = $this->getMockResult();
        $result1
            ->expects($this->once())
            ->method('getUnlimitedCount')
            ->will($this->returnValue(267));

        $result2 = $this->getMockResult();
        $result2
            ->expects($this->never())
            ->method('getUnlimitedCount')
            ->will($this->returnValue(987));

        ResultStore::storeResult('foo', $result1);
        ResultStore::storeResult('bar', $result2);

        $adapter = new ResultStoreResultsAdapter();
        $this->assertEquals(267, $adapter->countSearchResults('foo'));
    }

    public function testCountSearchResultsForManyTypes()
    {
        $result1 = $this->getMockResult();
        $result1
            ->expects($this->once())
            ->method('getUnlimitedCount')
            ->will($this->returnValue(444));

        $result2 = $this->getMockResult();
        $result2
            ->expects($this->once())
            ->method('getUnlimitedCount')
            ->will($this->returnValue(555));

        $result3 = $this->getMockResult();
        $result3
            ->expects($this->never())
            ->method('getUnlimitedCount')
            ->will($this->returnValue(666));

        ResultStore::storeResult('foo', $result1);
        ResultStore::storeResult('bar', $result2);
        ResultStore::storeResult('foobar', $result3);

        $adapter = new ResultStoreResultsAdapter();
        $this->assertEquals(999, $adapter->countSearchResults(array('foo', 'bar')));
    }

    public function testGetResultsReturnsFalseForUnknownResult()
    {
        $adapter = new ResultStoreResultsAdapter();
        $this->assertFalse($adapter->getResults('foo'));
    }

    public function testGetResultsReturnsDomainHitIteratorForResult()
    {
        $hitIterator = $this->getMockBuilder('\Bigcommerce\SearchClient\Hit\HitIterator')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->getMockResult();
        $result
            ->expects($this->once())
            ->method('getHits')
            ->will($this->returnValue($hitIterator));

        ResultStore::storeResult('foo', $result);

        $adapter = new ResultStoreResultsAdapter();
        $iterator = $adapter->getResults('foo');

        $this->assertInstanceOf('\Store\Search\Searcher\DomainHitIterator', $iterator);
        $this->assertEquals($hitIterator, $iterator->getInnerIterator());
    }
}
