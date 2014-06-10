<?php

use Store\Controllers;

class Integration_Controllers_ProductDownloadsControllerTest extends Interspire_IntegrationTest
{
	public function testIndexActionProductValidId()
	{
		$controller = $this->setupIndexAction('111', true);

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$this->assertEquals($controller->indexAction(), array("productdownloads" => array('dummy')));
		$this->assertEquals($responseMock->getStatus(), 200);
	}

	private function setupIndexAction($productIdentifier, $isValidIdentifier)
	{
		$repositoryMock = $this->getMock('ProductsDownloads', array('findByProductIdentifier'));

		if ($isValidIdentifier) {
			$repositoryMock->expects($this->any())
				->method('findByProductIdentifier')
				->will($this->returnValue(array('dummy')));
		} else {
			$repositoryMock->expects($this->any())
				->method('findByProductIdentifier')
				->will($this->throwException(new InvalidArgumentException("Invalid argument!")));
		}

		$requestMock = new Interspire_Request(array(), array(), array(), array('REQUEST_METHOD' => 'ANY'), array());

		$requestMock->setUserParam('productIdentifier', $productIdentifier);

		$controller = new ProductDownloadsController();

		$controller->setRequest($requestMock);
		$controller->setRepository($repositoryMock);

		return $controller;
	}

}
