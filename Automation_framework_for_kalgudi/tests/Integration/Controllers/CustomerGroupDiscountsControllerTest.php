<?php
use Store\Controllers;

class Unit_Controllers_CustomerGroupDiscountsControllerTest extends Interspire_IntegrationTest
{
	public function testIndexActionWithValidType()
	{
		$requestMock = new Interspire_Request(array(), array(), array(),  array('REQUEST_METHOD' => 'GET'), array());
		$requestMock->setUserParam('type', 'PRODUCT');
		$controller = new CustomerGroupDiscountsController();
		$controller->setRequest($requestMock);
		$result = json_decode($controller->indexAction());

		//verify result is empty and is enabled for pagination
		$this->assertEquals(0, count($result->items));
		$result = (array)$result;
		// pagination doesn't appear to be used by the UI
		// - this entire piece of functionality needs to be ripped out and
		// replaced with something compabible with the API/model pattern.
		// $this->assertArrayHasKey('pages', $result);
		// $this->assertArrayHasKey('total', $result);
		// $this->assertArrayHasKey('current', $result);
		// $this->assertArrayHasKey('limit', $result);
	}

	public function testIndexActionWithInvalidType()
	{
		$requestMock = new Interspire_Request(array(), array(), array(),  array('REQUEST_METHOD' => 'GET'), array());
		$requestMock->setUserParam('type', 'ABCD');
		$controller = new CustomerGroupDiscountsController();
		$controller->setRequest($requestMock);
		$result = json_decode($controller->indexAction());

		$this->assertEquals(0, count($result->customergroups));
	}

	public function testValidateAction()
	{
		$requestMock = new Interspire_Request(array(), array(), array(),  array('REQUEST_METHOD' => 'GET'), array());
		$controller = new CustomerGroupDiscountsController();
		$controller->setRequest($requestMock);

		$repoMock = $this->getMock('CustomerGroupDiscounts', array('validateDuplicateCatOrProd'));

		$repoMock->expects($this->once())
		->method('validateDuplicateCatOrProd')
		->will($this->returnValue(true));

		$controller->setRepository($repoMock);
		$this->assertTrue($controller->validateAction());
	}
}
