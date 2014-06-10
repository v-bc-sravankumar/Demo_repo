<?php

namespace Unit\Store\Seach\Searcher;

use PHPUnit_Framework_TestCase;
use Store\Search\Searcher\SearcherSearchResult;

class SearcherSearchResultTest extends PHPUnit_Framework_TestCase
{
    public function testGetSearchResults()
    {
        $result = new SearcherSearchResult(array('1', '2', '3'), '');

        $this->assertEquals(array('1', '2', '3'), $result->getSearchResults());
    }

    public function testGetParsedQuery()
    {
        $result = new SearcherSearchResult(array(), 'test-query');

        $this->assertEquals('test-query', $result->getParsedQuery());
    }
}
