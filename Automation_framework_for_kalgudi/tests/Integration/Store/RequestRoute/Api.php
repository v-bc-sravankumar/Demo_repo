<?php

class Store_Api_Version_2_Resource_Unittest_Url extends Store_Api_Version_2_Resource { }

class Unit_Lib_Store_RequestRoute_StoreApi extends Interspire_UnitTest
{
	public function setUp ()
	{
		Store_Config::override('Feature_Api', true);
	}

	public function tearDown ()
	{
		Store_Config::override('Feature_Api', false);
	}


	private function _getMockRequest ($url = '/api/', $method = 'GET')
	{
		$response = $this->getMock('Interspire_Response', array('sendResponse'));

		$response->expects($this->any())
			->method('sendResponse')
			->will($this->returnValue(true));

		/** @var Interspire_Request */
		$request = $this->getMock('Interspire_Request', array('getAbsolutePath', 'getMethod', 'getAcceptMediaTypes'));
		$request->setUrlParser(new Store_UrlParser_RootApp);
		$request->setResponse($response);

		$request->expects($this->any())
			->method('getMethod')
			->will($this->returnValue($method));

		$request->expects($this->any())
			->method('getAbsolutePath')
			->will($this->returnValue($url));

		$this->assertSame($url, $request->getAppPath());

		return $request;
	}

	private function _getMockThrottle ($isThrottled)
	{
		$throttle = $this->getMock('Store_Api_Throttle', array('touch', 'getRequestCount', 'isThrottled', 'getRemainingRequests'));

		$throttle->expects($this->any())
			->method('touch')
			->will($this->returnValue(true));

		$throttle->expects($this->any())
			->method('getRequestCount')
			->will($this->returnValue(1));

		$throttle->expects($this->any())
			->method('getRemainingRequests')
			->will($this->returnValue($isThrottled ? 0 : 1));

		$throttle->expects($this->any())
			->method('isThrottled')
			->will($this->returnValue((bool)$isThrottled));

		return $throttle;
	}

	/**
	* put your comment there...
	*
	* @param mixed $authenticated
	* @param mixed $throttled
	* @return Store_Api
	*/
	private function _getMockApi ($authenticated = true, $throttled = false)
	{
		// setup an api which is always authenticated for the purpose of these tests
		// additionally, mock executeRequest so that the api doesn't need to actually call a real controller

		/** @var Store_Api */
		$api = $this->getMock('Store_Api', array('authenticate', 'getAuthenticatedUserId', 'checkPermission', 'executeRequest', '_logRequest', 'touchThrottle'));

		if ($authenticated) {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->returnValue((bool)$authenticated));

			$api->expects($this->any())
				->method('getAuthenticatedUserId')
				->will($this->returnValue(1));
		} else {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->throwException(new Store_Api_Exception_Authentication_CredentialsNotSupplied));
		}

		$api->expects($this->any())
			->method('checkPermission')
			->will($this->returnValue((bool)$authenticated));

		$api->setThrottle($this->_getMockThrottle($throttled));

		return $api;
	}

	public function testUnauthenticatedRouteFails ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(false);
		$api->expects($this->exactly(0))
			->method('executeRequest');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(401, $dispatcher->getResponse()->getStatus());
	}

	public function testUnauthenticatedRouteIsntThrottled ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(false);
		$api->expects($this->exactly(0))
			->method('executeRequest');

		$api->expects($this->exactly(0))
			->method('touchThrottle');

		$api->setThrottle($this->_getMockThrottle(true));

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(401, $dispatcher->getResponse()->getStatus());
	}

	public function testUnauthenticatedRouteDoesntSendThrottleInfo ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(false);
		$api->expects($this->exactly(0))
			->method('executeRequest');

		$api->expects($this->exactly(0))
			->method('touchThrottle');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(401, $dispatcher->getResponse()->getStatus());
		$this->assertFalse($dispatcher->getResponse()->getHeader('X-BC-ApiLimit-Remaining'));
	}

	public function testAuthenticatedRouteSucceeds ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(true);
		$api->expects($this->exactly(1))
			->method('executeRequest');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(200, $dispatcher->getResponse()->getStatus());
	}

	public function testAuthenticatedRouteSendsThrottleInfo ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(true);
		$api->expects($this->exactly(1))
			->method('executeRequest');

		$api->expects($this->exactly(1))
			->method('touchThrottle');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(200, $dispatcher->getResponse()->getStatus());
		$this->assertSame('1', $dispatcher->getResponse()->getHeader('X-BC-ApiLimit-Remaining'));
	}

	public function testAuthenticatedThrottledRouteFails ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(true, true);
		$api->expects($this->exactly(0))
			->method('executeRequest');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time')
			->setParameters(array());

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(509, $dispatcher->getRequest()->getResponse()->getStatus());
	}

	public function testRouteParameters ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$parameters = array(
			'alpha',
			'foo' => 'bar',
		);

		$api = $this->_getMockApi(true);
		$api->expects($this->exactly(1))
			->method('executeRequest');

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time')
			->setParameters($parameters);

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(200, $dispatcher->getResponse()->getStatus());
		$this->assertSame($parameters, $dispatcher->getRequest()->getUserParams());
	}

	public function testEndpointExceptionFails ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		$api = $this->_getMockApi(true);
		$api->expects($this->exactly(1))
			->method('executeRequest')
			->will($this->throwException(new Store_Api_Exception_Resource_Conflict));

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api)
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(Interspire_Response::STATUS_CONFLICT, $dispatcher->getResponse()->getStatus());
	}

	public function testHighPriorityAcceptHeader ()
	{
		$dispatcher = new Interspire_RequestDispatcher($this->_getMockRequest());

		// force the route mock to return the type we specify
		$types = array(
			'application/xml',
		);

		$dispatcher->getRequest()
			->expects($this->any())
			->method('getAcceptMediaTypes')
			->will($this->returnValue($types));

		$api = $this->_getMockApi(true);

		// set up an expectation that executeRequest will be called with an xml data type even though we'll set the
		// data type on the route to json
		$api->expects($this->exactly(1))
			->method('executeRequest')
			->with($this->anything(), $this->anything(), $this->equalTo('xml'));

		$route = new Store_RequestRoute_StoreApi();
		$route
			->setApi($api)
			->setDataType('json')
			->setController('Store_Api_Version_2_Resource_Time');

		$this->assertTrue($route->processFollow($dispatcher->getRequest()));
		$this->assertSame(200, $dispatcher->getResponse()->getStatus());
		$this->assertSame('json', $route->getDataType());
	}

	public function testApiNotSerialized ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$api = $route->getApi();
		$this->assertInstanceOf('Store_Api', $api);
		$route = unserialize(serialize($route));
		$this->assertInstanceOf('Store_RequestRoute_StoreApi', $route);
		$this->assertNotSame($api, $route->getApi());
	}

	public function testIsCacheable ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$this->assertTrue($route->isCacheable());
	}

	public function testGetUrlForNonexistantController ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Foo');
		$this->assertFalse($route->getUrl());
	}

	public function testGetUrlForNonControllerClass ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Store_Api');
		$this->assertFalse($route->getUrl());
	}

	public function testGetUrlForValidController ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Store_Api_Version_2_Resource_Unittest_Url');
		$route->setDataType('json');
		$this->assertSame('/api/v2/unittest/url.json', $route->getUrl());
	}

	public function testGetUrlWithParameters ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Store_Api_Version_2_Resource_Unittest_Url');
		$route->setDataType('json');
		$route->setParameters(array(
			'unittest' => 1,
			'url' => 2,
			'foo' => 3,
			'bar' => 4,
		));
		$this->assertSame('/api/v2/unittest/1/url/2.json', $route->getUrl());
	}

	public function testGetApiRelativeUrl ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Store_Api_Version_2_Resource_Unittest_Url');
		$this->assertSame('/unittest/url.xml', $route->getApiRelativeUrl());
	}

	public function testGetApiResourcePath ()
	{
		$route = new Store_RequestRoute_StoreApi();
		$route->setController('Store_Api_Version_2_Resource_Unittest_Url');
		$this->assertSame('/unittest/url', $route->getApiResourcePath());
	}
}
