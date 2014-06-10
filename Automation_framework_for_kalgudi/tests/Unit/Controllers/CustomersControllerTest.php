<?php
use Store\Controllers;

class Unit_Controllers_CustomersControllerTest extends PHPUnit_Framework_TestCase
{
	public function testIndexActionSorter()
	{
		$controller = new CustomersController();
		$requestMock = new Interspire_Request(array(), array(), array(),  array(), array());
		$controller->setRequest($requestMock);

		$repoMock = $this->getMock('Customers', array('findMatching'));
		$repoMock->expects($this->once())
		->method('findMatching')
		->will($this->returnArgument(2));

		$controller->setRepository($repoMock);

		$arg = $controller->indexAction();
		$sorter = $arg['customers'];

		$this->assertEquals($sorter->field(), "date_created");
		$this->assertEquals($sorter->direction(), "desc");
	}

}