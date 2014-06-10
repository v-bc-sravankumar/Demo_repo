<?php
use Store\Controllers;

class Unit_Controllers_ProductsControllerTest extends Interspire_IntegrationTest
{
	private function setupRuleAction($rule, $request, $method)
	{
		$repoMock = $this->getMock('Products', array('findRule', 'deleteRule', 'updateRule'));
		$repoMock->expects($this->any())
			->method('findRule')
			->will($this->returnValue($rule));

		$repoMock->expects($this->any())
			->method('deleteRule')
			->will($this->returnValue(true));

		$repoMock->expects($this->any())
			->method('updateRule')
			->will($this->returnValue($rule));

		$requestMock = new Interspire_Request(array(), array(), array(),  array('REQUEST_METHOD' => $method), array());

		$requestMock->setUserParam('productId', $request['productId']);
		$requestMock->setUserParam('ruleId', $request['ruleId']);
		$requestMock->setUserParam('productHash', $request['productHash']);

		$controller = new ProductsController();

		$controller->setRequest($requestMock);
		$controller->setRepository($repoMock);

		return $controller;
	}

	public function testRuleActionProductIdInvalid()
	{
		$rule = array('product_id' =>'11122');
		$request = array('productId' =>'111', 'ruleId' => 999, 'productHash' => null);

		$controller = $this->setupRuleAction($rule, $request, 'ANY');

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$this->assertEquals($controller->ruleAction() , array());
		$this->assertEquals($responseMock->getStatus() , 404);
	}

	public function testRuleActionProductHashInvalid()
	{
		$rule = array('product_hash' =>'InvalidABCD9999');
		$request = array('productHash' => 'ABCD1234', 'ruleId' => 999,'productId' =>null);

		$controller = $this->setupRuleAction($rule, $request, 'ANY');

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$this->assertEquals($controller->ruleAction() , array());
		$this->assertEquals($responseMock->getStatus() , 404);
	}

	public function testRuleActionDeleteProductIdValid()
	{
		$rule = array('product_id' =>'111');
		$request = array('productId' =>'111', 'ruleId' => 999, 'productHash' => null);

		$controller = $this->setupRuleAction($rule, $request, 'DELETE');
		$this->assertEquals($controller->ruleAction() , array('rules'=>array()));
	}

	public function testRuleActionDeleteProductHashValid()
	{
		$rule = array('product_hash' =>'ABCD1234');
		$request = array('productHash' => 'ABCD1234', 'ruleId' => 999,'productId' =>null);

		$controller = $this->setupRuleAction($rule, $request, 'DELETE');
		$this->assertEquals($controller->ruleAction() , array('rules'=>array()));
	}

	public function testRuleActionPutProductIdValid()
	{
		$rule = array('product_id' =>'111');
		$request = array('productId' =>'111', 'ruleId' => 999, 'productHash' => null);

		$controller = $this->setupRuleAction($rule, $request, 'PUT');
		$rules = $controller->ruleAction();

		$this->assertEquals(count($rules['rules']) , 1);
	}

	public function testRuleActionPutProductHashValid()
	{
		$rule = array('product_hash' =>'ABCD1234');
		$request = array('productHash' => 'ABCD1234', 'ruleId' => 999,'productId' =>null);

		$controller = $this->setupRuleAction($rule, $request, 'PUT');
		$rules = $controller->ruleAction();

		$this->assertEquals(count($rules['rules']) , 1);
	}

	//validateAction
	public function testValidateIsDuplicatedCalled()
	{
		$controller = $this->getMock('ProductsController', array('isDuplicateUrl'));

		$controller->expects($this->once())
			->method('isDuplicateUrl')
			->with($this->equalTo('/something/'));

		$product = array('product' => array('id' =>'111', 'url' =>'/something/'));
		$requestMock = new Interspire_Request(array(), array(), $product,  array(), array());
		$controller->setRequest($requestMock);

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$result = $controller->validateAction();
	}

	public function testValidateIsCustomUrlValidCalled()
	{
		$controller = $this->getMock('ProductsController', array('isCustomUrlValid'));

		$controller->expects($this->once())
			->method('isCustomUrlValid')
			->with($this->equalTo('/something/'));

		$product = array('product' => array('id' =>'111', 'url' =>'/something/'));
		$requestMock = new Interspire_Request(array(), array(), $product,  array(), array());
		$controller->setRequest($requestMock);

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$result = $controller->validateAction();
	}

	public function testValidateIsValidSKUCalled()
	{
		$controller = $this->getMock('ProductsController', array('isValidSKU'));

		$controller->expects($this->once())
			->method('isValidSKU')
			->with($this->equalTo('ABC001'));

		$product = array('product' => array('id' =>'111', 'sku' =>'ABC001'));
		$requestMock = new Interspire_Request(array(), array(), $product,  array(), array());
		$controller->setRequest($requestMock);

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$result = $controller->validateAction();
	}

	public function testValidateIsUniqueNameCalled()
	{
		$controller = $this->getMock('ProductsController', array('isUniqueName'));

		$controller->expects($this->once())
			->method('isUniqueName')
			->with($this->equalTo('something'));

		$product = array('product' => array('id' =>'111', 'name' =>'something'));
		$requestMock = new Interspire_Request(array(), array(), $product,  array(), array());
		$controller->setRequest($requestMock);

		$responseMock = new Interspire_Response();
		$controller->setResponse($responseMock);

		$result = $controller->validateAction();
	}

	//getSorterByParams
	public function testSortVarsDefault()
	{
		$controller = new \ProductsController();
		$controller->setRequest(new Interspire_Request());

		$sorter = $controller->getSorterByParams(array());

		$this->assertEquals('id', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());
	}

	public function testCustomSearchSortOverridesDefaultSort()
	{
		$controller = new \ProductsController();
		$controller->setRequest(new Interspire_Request());

		$searchVars = array('sortField' => 'prodcalculatedprice', 'sortOrder' => 'desc');
		$sorter = $controller->getSorterByParams($searchVars);

		$this->assertEquals('calculated_price', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());
	}

	public function testParamSortOverridesCustomViewSort()
	{
		$controller = new \ProductsController();
		$controller->setRequest(new Interspire_Request(array('sort' => 'name', 'direction' => 'asc')));

		$searchVars = array('sortField' => 'prodcalculatedprice', 'sortOrder' => 'desc', 'sort' => 'name', 'direction' => 'asc');
		$sorter = $controller->getSorterByParams($searchVars);

		$this->assertEquals('name', $sorter->field());
		$this->assertEquals('asc', $sorter->direction());
	}

	public function testParamSortOverridesDefaultSort()
	{
		$controller = new \ProductsController();
		$controller->setRequest(new Interspire_Request(array('sort' => 'name', 'direction' => 'asc')));

		$sorter = $controller->getSorterByParams(array());

		$this->assertEquals('name', $sorter->field());
		$this->assertEquals('asc', $sorter->direction());
	}

	public function testRequiresSearch()
	{
		$controller = new \ProductsController();
		//imported products
		$this->assertTrue($controller->requiresSearch(array('lastImport'=>1)));
		//option set (assigned to)
		$this->assertTrue($controller->requiresSearch(array('productType'=>18)));
		//option id
		$this->assertTrue($controller->requiresSearch(array('optionId'=>3)));
		//low inventory
		$this->assertTrue($controller->requiresSearch(array('inventoryLow'=>1)));
		//out of stock
		$this->assertTrue($controller->requiresSearch(array('outOfStock'=>1)));
		//advanced search
		$advSearch = array('searchQuery' => 'apple', 'letter' => '', 'brand' => '',
				'category' => array(17), 'subCats' => '1', 'priceFrom' => '', 'priceTo' => '',
				'soldFrom' => '', 'soldTo' => '', 'inventoryFrom' => '', 'inventoryTo' => '',
				'visibility' => '', 'featured' => '', 'freeShipping' => '', 'status' => '',
				'sortField' => 'productid', 'sortOrder' => 'asc', 'searchId' => '0',
				);
		$this->assertTrue($controller->requiresSearch($advSearch));
		//custom search
		$this->assertTrue($controller->requiresSearch(array('searchId'=>3)));
		//keyword filter
		$this->assertTrue($controller->requiresSearch(array('filter'=>array('keyword_filter' => 'apple'))));
	}

	//custom search
	public function testCustomSearch()
	{
		$controller = new \ProductsController();
		$params = array();
		$this->assertFalse($controller->hasCustomSearch($params));

		$params = array('searchId' => 2);
		$this->assertTrue($controller->hasCustomSearch($params));
		$this->assertEquals(2 , $controller->getCustomSearchId($params));
	}
}
