<?php

class Unit_Store_PagingHelper extends PHPUnit_Framework_TestCase
{

	public function testFlatListInIncrementsOfTen()
	{
		$pager = new DataModel_Legacy_PagingHelper(10);
		$pager->setList(array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30));

		$this->assertEquals(30, $pager->getTotal());
		$this->assertEquals(3, $pager->getTotalPages());

		$this->assertEquals(0, $pager->getStartIndex());
		$this->assertEquals(9, $pager->getEndIndex());
		$this->assertEquals(array(1,2,3,4,5,6,7,8,9,10), $pager->getList());

		$pager->setCurrent(2);

		$this->assertEquals(10, $pager->getStartIndex());
		$this->assertEquals(19, $pager->getEndIndex());
		$this->assertEquals(array(11,12,13,14,15,16,17,18,19,20), $pager->getList());

		$pager->setCurrent(3);

		$this->assertEquals(20, $pager->getStartIndex());
		$this->assertEquals(29, $pager->getEndIndex());
		$this->assertEquals(array(21,22,23,24,25,26,27,28,29,30), $pager->getList());
	}
}