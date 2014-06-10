<?php

class DomainModel_PagedCollectionTest extends PHPUnit_Framework_TestCase
{
	public function testEmptyCollection()
	{
		$collection = new DataModel_PagedCollection(array(), new DomainModel\Query\Pager(1, 1));

		$this->assertEquals(0, $collection->count());
	}

	public function testPagedListShorterThanLimit()
	{
		$list = range(1,9);
		$collection = new DataModel_PagedCollection($list, new DomainModel\Query\Pager(1, 10));

		$this->assertEquals(9, $collection->count());
		$this->assertEquals(1, $collection->getCurrentPage());
		$this->assertEquals(1, $collection->getTotalPages());
	}

	public function testFirstPageWithMultiplePagedList()
	{
		$list = range(1,21);
		$collection = new DataModel_PagedCollection($list, new DomainModel\Query\Pager(1, 10));

		$this->assertEquals(1, $collection->getCurrentPage());
		$this->assertEquals(3, $collection->getTotalPages());
	}

	public function testSecondPageWithMultiplePagedList()
	{
		$list = range(1,21);
		$collection = new DataModel_PagedCollection($list, new DomainModel\Query\Pager(2, 10));

		$this->assertEquals(2, $collection->getCurrentPage());
		$this->assertEquals(3, $collection->getTotalPages());

		$this->assertEquals(11, $collection->current());
		$collection->next();
		$this->assertEquals(12, $collection->current());
	}

	public function testSecondPageWithPreFetchedPagedListSlice()
	{
		$list = range(11,20);
		$collection = new DataModel_PagedCollection($list, new DomainModel\Query\Pager(2, 10));
		$collection->setTotalItems(21);

		$this->assertEquals(2, $collection->getCurrentPage());
		$this->assertEquals(3, $collection->getTotalPages());

		$this->assertEquals(11, $collection->current());
		$collection->next();
		$this->assertEquals(12, $collection->current());
	}

	public function testPrecalculatedTotalIsAppliedToArray()
	{
		$total = 76;
		$list = array_fill(0, $total, 0);
		$collection = new DataModel_PagedCollection($list, new \DomainModel\Query\Pager(2, 10), true, $total);
		$this->assertEquals($total, $collection->getTotalItems());
	}

	public function testPrecalculatedTotalIsAppliedToQueryIterator()
	{
		$total = 76;
		$iterator = $this->getMockBuilder('\DataModel_QueryIterator')->disableOriginalConstructor()->getMock();
		$iterator->expects($this->any())->method('limit')->will($this->returnSelf());
		$iterator->expects($this->any())->method('valid')->will($this->returnValue(false));
		$collection = new DataModel_PagedCollection($iterator, new \DomainModel\Query\Pager(2, 10), true, $total);
		$this->assertEquals($total, $collection->getTotalItems());
	}

	public function testPrecalculatedTotalIsAppliedToSelectQuery()
	{
		$total = 76;
		$list = new DataModel_SelectQuery('SELECT * FROM orders', new Db_Bridge(new Db_Stub()));
		$collection = new DataModel_PagedCollection($list, new \DomainModel\Query\Pager(2, 10), true, $total);
		$this->assertEquals($total, $collection->getTotalItems());
	}

}