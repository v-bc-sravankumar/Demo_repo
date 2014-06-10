<?php

namespace Unit\Store\Search\Searcher\SortFieldMapper;

use Store\Search\Searcher\SortFieldMapper\SortFieldMapping;

class SortFieldMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSortField()
    {
        $mapping = new SortFieldMapping('foo', 'asc');
        $this->assertEquals('foo', $mapping->getSortField());
    }

    public function testGetSortOrder()
    {
        $mapping = new SortFieldMapping('foo', 'asc');
        $this->assertEquals('asc', $mapping->getSortOrder());
    }
}
