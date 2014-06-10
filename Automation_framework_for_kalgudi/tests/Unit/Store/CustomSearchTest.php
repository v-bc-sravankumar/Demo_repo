<?php

namespace Store;

class CustomSearchTest extends \PHPUnit_Framework_TestCase
{
	public function testSomething()
	{
		$type = 'orders';
		$mockDb = $this->getMock('Db_Base');
		$mockDb->expects($this->once())->method('Quote')->with(1)->will($this->returnValue('1'));
		$mockDb->expects($this->once())->method('Fetch')
			->will($this->returnValue(array(
				'searchvars' => 'viewName=Asc&preorders[]=0&preorders[]=1&searchDeletedOrders=no&SearchByDate=0&sortField=orderid&sortOrder=asc',
			)));

		\Store\CustomSearch::setDb($mockDb);

		$vars = \Store\CustomSearch::getCustomSearchVars($type, 1);

		$this->assertEquals(array(
			'viewName' => 'Asc',
			'preorders' => array('0', '1'),
			'searchDeletedOrders' => 'no',
			'SearchByDate' => '0',
			'sortField' => 'orderid',
			'sortOrder' => 'asc',
		), $vars);
	}
}