<?php

class Unit_Interspire_RequestRouter_PatternTest extends PHPUnit_Framework_TestCase
{
	private function getMockRequest($uri)
	{
		$request = $this->getMock('Interspire_Request');
		$request->expects($this->any())
			->method('getAppPath')
			->will($this->returnValue($uri));

		return $request;
	}

	public function testCanConnectForGet()
	{
		$request = $this->getMockRequest('/foo/bar');
		$request->expects($this->any())
			->method('getMethod')
			->will($this->returnValue('get'));

		$router = new Interspire_RequestRouter_Pattern();
		$router->connect("/foo/bar", array(
			'controller' => "Foo\\Bar",
			'action' => 'bar',
			'methods' => array('get'),
		));
		$result = $router->getRouteForRequest($request);
		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('Foo\Bar', $result->getControllerName());
	}

	public function testCanConnectForPost()
	{
		$request = $this->getMockRequest('/foo/bar');
		$request->expects($this->any())
			->method('getMethod')
			->will($this->returnValue('post'));

		$router = new Interspire_RequestRouter_Pattern();
		$router->connect("/foo/bar", array(
			'controller' => "Foo\\Bar",
			'action' => 'bar',
			'methods' => array('post'),
		));
		$result = $router->getRouteForRequest($request);
		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('Foo\Bar', $result->getControllerName());
	}

	public function testCantConnectForPost()
	{
		$request = $this->getMockRequest('/foo/bar');
		$request->expects($this->any())
			->method('getMethod')
			->will($this->returnValue('post'));

		$router = new Interspire_RequestRouter_Pattern();
		$router->connect("/foo/bar", array(
			'controller' => "Foo\\Bar",
			'action' => 'bar',
			'methods' => array('get'),
		));
		$result = $router->getRouteForRequest($request);
		$this->assertFalse($result);
	}

	public function testCanMatchFixedPattern()
	{
		$request = $this->getMockRequest('/foo/bar');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo/bar', 'FooBar');
		$result = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('FooBar', $result->getControllerName());
	}

	public function testCanCaptureNamedParameter()
	{
		$request = $this->getMockRequest('/foo/bar');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo/{:baz}', 'Foo');
		$result = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('Foo', $result->getControllerName());
	}

	public function testCanMatchMissingOptionalParameter()
	{
		$request = $this->getMockRequest('/foo');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo{/bar}?', 'FooBar');
		$result = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('FooBar', $result->getControllerName());
	}

	public function testCanMatchOptionalParameter()
	{
		$request = $this->getMockRequest('/foo/bar');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo{/bar}?', 'FooBar');
		$result = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $result);
		$this->assertSame('FooBar', $result->getControllerName());
	}

	public function testCanMatchIntegerPlaceholder()
	{
		$request = $this->getMockRequest('/foo/1.bar');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo/{i:numeric}.bar', 'Foo');
		$route = $router->getRouteForRequest($request);

		$expected = array(
			'numeric' => '1',
		);

		$this->assertInstanceOf('Interspire_RequestRoute', $route);
		$this->assertSame($expected, $route->getParameters());
	}

	public function testCanMatchAlphanumericParameters()
	{
		$request = $this->getMockRequest('/foo/alpha-beta-gamma');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo/{a:one}-{a:two}-{a:three}', 'Foo');
		$route = $router->getRouteForRequest($request);

		$expected = array(
			'one' => 'alpha',
			'two' => 'beta',
			'three' => 'gamma',
		);

		$this->assertInstanceOf('Interspire_RequestRoute', $route);
		$this->assertSame($expected, $route->getParameters());
	}

	// regression for product images patterns
	public function testCanMatchProductImagesPattern()
	{
		$pattern = '/products/{:productIdentifier}/images/{i:imageId}/{h:hash}.{i:width}.{i:height}.{a:extension}';
		$request = $this->getMockRequest('/products/ABC/images/456/ABC.100.200.jpg');

		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern($pattern, 'Foo');
		$route = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $route);
	}

	/**
	 * Tests that patterns without placeholders are checked first before those with placeholders.
	 */
	public function testStaticPatternsAreCheckedBeforeDynamicPatterns()
	{
		$router = new Interspire_RequestRouter_Pattern();
		$router->addPattern('/foo/{:my_segment', 'DynamicController');
		$router->addPattern('/foo/bar', 'StaticController');

		$request = $this->getMockRequest('/foo/bar');

		$route = $router->getRouteForRequest($request);

		$this->assertInstanceOf('Interspire_RequestRoute', $route);
		$this->assertEquals('StaticController', $route->getControllerName());
	}
}
