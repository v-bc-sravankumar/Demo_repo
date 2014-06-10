<?php

class Unit_Lib_Store_Api_Throttle extends Unit_Lib_Store_Api_Base
{
	public function setUp ()
	{
		Store_Config::override('Feature_Api', true);
	}

	public function tearDown ()
	{
		Store_Config::override('Feature_Api', false);
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

	public function testUnthrottledRequestSucceeds ()
	{
		$throttle = $this->_getMockThrottle(false);

		$api = $this->_getMockApi(true);
		$api->setThrottle($throttle);

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api);
		$route->setController('Store_Api_Version_2_Resource_Time');

		$request = $this->_getMockRequest('/api/v2/time', 'GET');

		$dispatcher = new Interspire_RequestDispatcher($request);
		$this->assertTrue($route->processFollow($request));
		$this->assertEquals(200, $dispatcher->getResponse()->getStatus());

		$limit = $dispatcher->getResponse()->getHeader('X-BC-ApiLimit-Remaining');
		$this->assertSame('1', $limit);
	}

	public function testThrottledRequestFails ()
	{
		$throttle = $this->_getMockThrottle(true);

		$api = $this->_getMockApi(true);
		$api->setThrottle($throttle);

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api);
		$route->setController('Store_Api_Version_2_Resource_Time');

		$request = $this->_getMockRequest('/api/v2/time', 'GET');

		$dispatcher = new Interspire_RequestDispatcher($request);
		$this->assertTrue($route->processFollow($request));
		$this->assertEquals(509, $dispatcher->getResponse()->getStatus());

		$limit = $dispatcher->getResponse()->getHeader('X-BC-ApiLimit-Remaining');
		$this->assertSame('0', $limit);
	}

	public function testUnauthorisedRequestShowsNoLimitHeaders ()
	{
		$throttle = $this->_getMockThrottle(true);

		$api = $this->_getMockApi(false);
		$api->setThrottle($throttle);

		$route = new Store_RequestRoute_StoreApi();
		$route->setApi($api);
		$route->setController('Store_Api_Version_2_Resource_Time');

		$request = $this->_getMockRequest('/api/v2/time', 'GET');

		$dispatcher = new Interspire_RequestDispatcher($request);
		$this->assertTrue($route->processFollow($request));
		$this->assertEquals(401, $dispatcher->getResponse()->getStatus());

		$limit = $dispatcher->getResponse()->getHeader('X-BC-ApiLimit-Remaining');
		$this->assertFalse($limit);
	}
}
