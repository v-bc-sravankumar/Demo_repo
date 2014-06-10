<?php

class Unit_Lib_Store_RequestRouter_StoreApi extends Interspire_UnitTest
{
	public function setUp ()
	{
		Store_Config::override('Feature_Api', true);
	}

	public function tearDown ()
	{
		Store_Config::override('Feature_Api', false);
	}

	private function _getMockRequest ($url, $method = 'GET')
	{
		$response = $this->getMock('Interspire_Response', array('sendResponse'));

		$response->expects($this->any())
			->method('sendResponse')
			->will($this->returnValue(true));

		/** @var Interspire_Request */
		$request = $this->getMock('Interspire_Request', array('getAbsolutePath', 'getMethod'));
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

	private function _getMockApi ($authenticated)
	{
		// setup an api which is always authenticated for the purpose of these tests
		$api = $this->getMock('Store_Api', array('authenticate','checkPermission'));

		if ($authenticated) {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->returnValue((bool)$authenticated));
		} else {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->throwException(new Store_Api_Exception_Authentication_CredentialsNotSupplied));
		}

		$api->expects($this->any())
			->method('checkPermission')
			->will($this->returnValue((bool)$authenticated));

		return $api;
	}

	public function testNonApiUrlReturnsFalse ()
	{
		$request = $this->_getMockRequest('/foo');
		$router = new Store_RequestRouter_StoreApi();
		$this->assertFalse($router->getRouteForRequest($request));
	}

	public function testApiExceptionReturnsNullRoute ()
	{
		// this may be any sort of exception (unauthenticated, invalid url) so we just care about it being a Null route
		$request = $this->_getMockRequest('/api/');
		$router = new Store_RequestRouter_StoreApi();
		$this->assertInstanceOf('Interspire_RequestRoute_Null', $router->getRouteForRequest($request));
	}

	public function testRoutingUnauthenticatedRequestFails ()
	{
		$request = $this->_getMockRequest('/api/v2/time');
		$router = new Store_RequestRouter_StoreApi();
		$router->setApi($this->_getMockApi(false));
		$route = $router->getRouteForRequest($request);
		$route->processFollow($request);
		$this->assertSame(401, $request->getResponse()->getStatus());
	}

	public function testRoutingAuthenticatedRequestSucceeds ()
	{
		$request = $this->_getMockRequest('/api/v2/time');
		$router = new Store_RequestRouter_StoreApi();
		$router->setApi($this->_getMockApi(true));
		$route = $router->getRouteForRequest($request);
		$this->assertInstanceOf('Store_RequestRoute_StoreApi', $route);
		$this->assertSame(200, $request->getResponse()->getStatus());
		$this->assertSame('Store_Api_Version_2_Resource_Time', $route->getController());
	}

	public function getPathSegmentsDataProvider ()
	{
		$data = array();

		$data[] = array('/api', array());
		$data[] = array('/api/', array());
		$data[] = array('api', array());
		$data[] = array('api/', array());
		$data[] = array('/api/foo', array('foo'));
		$data[] = array('/api/foo/', array('foo'));
		$data[] = array('api/foo', array('foo'));
		$data[] = array('api/foo/', array('foo'));
		$data[] = array('/api/foo/bar', array('foo', 'bar'));
		$data[] = array('/api/foo/bar/', array('foo', 'bar'));
		$data[] = array('api/foo/bar', array('foo', 'bar'));
		$data[] = array('api/foo/bar/', array('foo', 'bar'));
		$data[] = array('/foo', new Store_Api_Exception_Request_InvalidSyntax);
		$data[] = array('foo', new Store_Api_Exception_Request_InvalidSyntax);
		$data[] = array('/foo/', new Store_Api_Exception_Request_InvalidSyntax);
		$data[] = array('foo/', new Store_Api_Exception_Request_InvalidSyntax);

		return $data;
	}

	/**
	* @dataProvider getPathSegmentsDataProvider
	*/
	public function testGetPathSegments ($path, $expected)
	{
		if ($expected instanceof Exception) {
			$this->setExpectedException(get_class($expected));
			Store_RequestRouter_StoreApi::getPathSegments($path);
		} else {
			$this->assertSame($expected, Store_RequestRouter_StoreApi::getPathSegments($path));
		}
	}

	public function getVersionForSegmentDataProvider ()
	{
		$data = array();

		$data[] = array('v2', 2.0);
		$data[] = array('v2.', new Store_Api_Exception_Request_InvalidVersion);
		$data[] = array('v2.0', 2.0);
		$data[] = array('v2.00', 2.0);
		$data[] = array('v2.31', 2.31);

		$data[] = array('2', new Store_Api_Exception_Request_InvalidVersion);
		$data[] = array('2.', new Store_Api_Exception_Request_InvalidVersion);
		$data[] = array('2.0', new Store_Api_Exception_Request_InvalidVersion);
		$data[] = array('2.00', new Store_Api_Exception_Request_InvalidVersion);
		$data[] = array('2.31', new Store_Api_Exception_Request_InvalidVersion);

		return $data;
	}

	/**
	* @dataProvider getVersionForSegmentDataProvider
	*/
	public function testGetVersionForSegment ($segment, $expected)
	{
		if ($expected instanceof Exception) {
			$this->setExpectedException(get_class($expected));
			Store_RequestRouter_StoreApi::getVersionForSegment($segment);
		} else {
			$expected = (float)$expected;
			$this->assertEquals($expected, Store_RequestRouter_StoreApi::getVersionForSegment($segment), '', 0.01);
		}
	}

	public function getDataTypeFromSegmentDataProvider ()
	{
		$data = array();

		$data[] = array(array('foo', 'bar.xml'), 'xml');
		$data[] = array(array('foo', 'bar.json'), 'json');
		$data[] = array(array('foo', 'bar.csv'), new Store_Api_Exception_OutputEncoder_InvalidOutputType);
		$data[] = array(array('foo', 'bar'), false);

		$data[] = array(array('foo.xml'), 'xml');
		$data[] = array(array('foo'), false);

		return $data;
	}

	/**
	* @dataProvider getDataTypeFromSegmentDataProvider
	*/
	public function testGetDataTypeFromSegment ($segments, $expected)
	{
		if ($expected instanceof Exception) {
			$this->setExpectedException(get_class($expected));
			Store_RequestRouter_StoreApi::getDataTypeFromSegment($segments);
		} else {
			$this->assertSame($expected, Store_RequestRouter_StoreApi::getDataTypeFromSegment($segments));
		}
	}

	public function getDataTypeFromHeaderDataProvider ()
	{
		$data = array();

		$data[] = array('*/*', false);
		$data[] = array('application/xml', 'xml');
		$data[] = array('application/json', 'json');

		// note: I'm really not sure if this is the correct behaviour but I believe the impact is minor
		// consider changing it to expect Store_Api_Exception_OutputEncoder_InvalidOutputType
		$data[] = array('foo', false);

		$data[] = array('application/foo', new Store_Api_Exception_OutputEncoder_InvalidOutputType);
		$data[] = array('foo,application/xml', 'xml');

		return $data;
	}

	/**
	* @dataProvider getDataTypeFromHeaderDataProvider
	*/
	public function testGetDataTypeFromHeader ($httpAccept, $expected)
	{
		$request = new Interspire_Request(null, null, null, array(
			'HTTP_ACCEPT' => $httpAccept,
		));

		if ($expected instanceof Exception) {
			$this->setExpectedException(get_class($expected));
			Store_RequestRouter_StoreApi::getDataTypeFromHeader($request);
		} else {
			$this->assertSame($expected, Store_RequestRouter_StoreApi::getDataTypeFromHeader($request));
		}
	}

	public function testGetDataTypeFromSegmentsOrRequest ()
	{
		$this->markTestIncomplete();
	}

	public function getResourceForSegmentsDataProvider ()
	{
		$data = array();

		$data[] = array(
			array('foo'),
			array('foo'),
		);

		$data[] = array(
			array('foo', 'bar'),
			array('foo', 'bar'),
		);

		$data[] = array(
			array(),
			array('index'),
		);

		$data[] = array(
			array('foo', '1', 'bar'),
			array('foo', 'bar'),
			array('foo' => '1'),
		);

		$data[] = array(
			array('1', 'foo'),
			new Store_Api_Exception_Request_InvalidSyntax(),
		);

		$data[] = array(
			array('foo', '1', '2'),
			new Store_Api_Exception_Request_InvalidSyntax(),
		);

		return $data;
	}

	/**
	* @dataProvider getResourceForSegmentsDataProvider
	*/
	public function testGetResourceForSegments ($segments, $expectedResource, $expectedParameters = array())
	{
		if ($expectedResource instanceof Exception) {
			$this->setExpectedException(get_class($expectedResource));
			Store_RequestRouter_StoreApi::getResourceForSegments($segments, $parameters);
		} else {
			$this->assertSame($expectedResource, Store_RequestRouter_StoreApi::getResourceForSegments($segments, $parameters), "resource mismatch");
			$this->assertSame($expectedParameters, $parameters, "parameters mismatch");
		}
	}

	public function getCallbackForRequestDataProvider ()
	{
		$data = array();

		$data[] = array(
			2,
			array('time'),
			'GET',
			array('Store_Api_Version_2_Resource_Time', 'getAction'),
		);

		$data[] = array(
			2,
			array('time'),
			'POST',
			new Store_Api_Exception_Resource_MethodNotFound,
		);

		$data[] = array(
			2,
			array('time'),
			'PUT',
			new Store_Api_Exception_Resource_MethodNotFound,
		);

		$data[] = array(
			2,
			array('brands'),
			'GET',
			array('Store_Api_Version_2_Resource_Brands', 'getAction'),
		);

		$data[] = array(
			2,
			array('brands', 'count'),
			'GET',
			array('Store_Api_Version_2_Resource_Brands_Count', 'getAction'),
		);

		$data[] = array(
			2,
			array('brands'),
			'PUT',
			array('Store_Api_Version_2_Resource_Brands', 'putAction'),
		);

		$data[] = array(
			2,
			array('foo'),
			'GET',
			new Store_Api_Exception_Resource_ResourceNotFound,
		);

		$data[] = array(
			2,
			array('brands', 'foo'),
			'GET',
			new Store_Api_Exception_Resource_ResourceNotFound,
		);

		return $data;
	}

	/**
	* @dataProvider getCallbackForRequestDataProvider
	* @param double $version
	* @param array $resource
	* @param string $method
	* @param array $expected callback or an instance of an expected exception
	*/
	public function testGetCallbackForRequest ($version, $resource, $method, $expected)
	{
		// note: this test WILL currently break in the future if the version it tests against is deprecated or if the
		// real resources it tests against are changed drastically or removed

		// if this happens just update getCallbackForRequestDataProvider accordingly or refactor to allow easy mocking
		// of the dependancies that getCallbackForRequest has

		if ($expected instanceof Exception) {
			$this->setExpectedException(get_class($expected));
			Store_RequestRouter_StoreApi::getCallbackForRequest($version, $resource, $method);
		} else {
			$this->assertSame($expected, Store_RequestRouter_StoreApi::getCallbackForRequest($version, $resource, $method));
		}
	}

	public function testGetApiRoute ()
	{
		$this->markTestIncomplete();
	}

	public function testGetUrlForRouteFailsForNonApiRoutes ()
	{
		$router = new Store_RequestRouter_StoreApi();
		$route = new Interspire_RequestRoute_Null();
		$this->assertFalse(false, $router->getUrlForRoute($route));
	}

	public function testGetUrlForRouteSucceedsForApiRoutes ()
	{
		$router = new Store_RequestRouter_StoreApi();
		$route = new Store_RequestRoute_StoreApi();
		$this->assertFalse($router->getUrlForRoute($route), "getUrlForRoute should be false if the route isn't configured properly");

		// now configure the route and try again
		$route->setController('Store_Api_Version_2_Resource_Time');
		$url = $router->getUrlForRoute($route);
		$this->assertSame('/api/v2/time.xml', $url);
	}
}
