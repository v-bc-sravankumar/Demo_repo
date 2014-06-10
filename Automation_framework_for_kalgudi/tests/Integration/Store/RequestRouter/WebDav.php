<?php
class Unit_Lib_Store_RequestRouter_Store_WebDav extends Interspire_UnitTest
{
	public function setUp()
	{
		Store_Config::override('Feature_WebDav', true);
	}

	public function tearDown()
	{
		Store_Config::override('Feature_WebDav', false);
	}

	private function _getMockRequest($url)
	{
		/** @var Interspire_Request */
		$request = $this->getMock('Interspire_Request', array('getAppUrl'));

		$request->expects($this->any())
			->method('getAppUrl')
			->will($this->returnValue($url));

		return $request;
	}

	public function testGetUrlForRoute()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$this->assertFalse($webdav->getUrlForRoute(new Store_RequestRoute_WebDav()));
	}

	public function testGetRouteForRequestWebdavDisabled()
	{
		Store_Config::override('Feature_WebDav', false);
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/dav');
		$this->assertFalse($webdav->getRouteForRequest($request));
	}

	public function testGetRouteForRequestFail()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/blah');
		$this->assertFalse($webdav->getRouteForRequest($request));
	}

	public function testGetRouteForRequest()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/dav');
		$this->assertNotNull($webdav->getRouteForRequest($request));
	}

	public function testGetRouteForRequestWithTrailingSlash()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/dav/');
		$this->assertNotNull($webdav->getRouteForRequest($request));
	}

	public function testGetRouteForDavResourceRequest()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/dav/content');
		$this->assertNotNull($webdav->getRouteForRequest($request));
	}

	/**
	 * Added this test along with the fix for ISC-4870.
	 */
	public function testGetRouteForPrefixSubstringFails()
	{
		$webdav = new Store_RequestRouter_WebDav();
		$request = $this->_getMockRequest('/david');
		$this->assertFalse($webdav->getRouteForRequest($request));
	}

}
