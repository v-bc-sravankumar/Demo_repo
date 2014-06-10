<?php

/**
 * Stub implementation of RequestRouter
 */
class Stub_RequestRouter extends Store_RequestRouter
{

	public function getRouteForRequest(Interspire_Request $request)
	{
		$url = $request->getAbsoluteUrl();
		if ($url == '/') {
			return new Stub_RequestRoute;
		}
		return false;
	}

	public function getUrlForRoute(Interspire_RequestRoute $route)
	{
		return true;
	}

}

/**
 * Stub implementation of a RequestRoute
 */
class Stub_RequestRoute extends Interspire_RequestRoute {

	protected function _follow(Interspire_Request $request)
	{
		return true;
	}
}

/**
 * Stub implementation of legacy page handler
 */
class Stub_Handler {

	/**
	 * Ad-hoc call counter as mock object cannot be used
	 * to handle the route.
	 */
	static public $callCount = 0;

	public function HandlePage()
	{
		self::$callCount++;
	}

}

class Unit_Store_RequestDispatcherTest extends PHPUnit_Framework_TestCase
{

	public function testDispatchToMockRoute()
	{
		$mockRequest = $this->getMock('Interspire_Request');
		$mockRequest->expects($this->any())->method('getAbsoluteUrl')->will($this->returnValue('/'));

		$dispatcher = new Interspire_RequestDispatcher;

		$mockRouter = $this->getMock('Stub_RequestRouter');
		$mockRouter->expects($this->once())->method('getRoute')->with($dispatcher)->will($this->returnValue(new Stub_RequestRoute));

		$dispatcher->setRequest($mockRequest);
		$dispatcher->addRouter($mockRouter);

		$this->assertTrue($dispatcher->dispatch());
	}

	public function testDispatchToStubRoute()
	{
		$mockRequest = $this->getMock('Interspire_Request');
		$mockRequest->expects($this->any())->method('getAbsoluteUrl')->will($this->returnValue('/'));

		$dispatcher = new Interspire_RequestDispatcher;
		$dispatcher->setRequest($mockRequest);
		$dispatcher->addRouter(new Stub_RequestRouter);

		$this->assertTrue($dispatcher->dispatch());
	}

	public function testDispatchToMissingRoute()
	{
		$mockRequest = $this->getMock('Interspire_Request');
		$mockRequest->expects($this->any())->method('getAbsoluteUrl')->will($this->returnValue('/missing'));

		$dispatcher = new Interspire_RequestDispatcher;
		$dispatcher->setRequest($mockRequest);
		$dispatcher->addRouter(new Stub_RequestRouter);

		$this->assertFalse($dispatcher->dispatch());
	}

	public function testDispatchToExistingLegacyStaticUrl()
	{
		$mockRequest = $this->getMock('Interspire_Request');
		$mockRequest->expects($this->any())->method('getAppPath')->will($this->returnValue('/match-this-path'));

		$staticRouter = new Store_RequestRouter_LegacyStatic();
		$staticRouter->addStaticRoute('/match-this-path', 'Stub_Handler');

		$dispatcher = new Interspire_RequestDispatcher;
		$dispatcher->setRequest($mockRequest);
		$dispatcher->addRouter($staticRouter);

		$this->assertEquals(0, Stub_Handler::$callCount);
		$this->assertTrue($dispatcher->dispatch());
		$this->assertEquals(1, Stub_Handler::$callCount);
	}

	public function testDispatchToNonExistingLegacyStaticUrl()
	{
		$mockRequest = $this->getMock('Interspire_Request');
		$mockRequest->expects($this->any())->method('getAppPath')->will($this->returnValue('/non-existing-path'));

		$staticRouter = new Store_RequestRouter_LegacyStatic();
		$staticRouter->addStaticRoute('/match-this-path', 'Stub_Handler');

		$dispatcher = new Interspire_RequestDispatcher;
		$dispatcher->setRequest($mockRequest);
		$dispatcher->addRouter($staticRouter);

		$this->assertFalse($dispatcher->dispatch());
	}

}