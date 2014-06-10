<?php

class Unit_Lib_Store_RequestRouter_AdminLegacy extends Interspire_UnitTest
{

    private $router;

    public function setUp()
    {
        $this->router = new Store_RequestRouter_AdminLegacy();
    }

    private function getMockRequest($expectedAppUrl, $expectedAppPath, $expectedResponse = null)
    {
        $request = $this->getMock('Interspire_Request', array('getAppUrl', 'getAppPath', 'getResponse'));

        $request->expects($this->any())
            ->method('getAppUrl')
            ->will($this->returnValue($expectedAppUrl));

        $request->expects($this->any())
            ->method('getAppPath')
            ->will($this->returnValue($expectedAppPath));

        $request->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($expectedResponse));

        return $request;
    }

    private function getMockResponse($redirectUrl)
    {
        $response = $this->getMock('Interspire_Response', array('redirect'));
        $response->expects($this->any())
            ->method('redirect')
            ->with($this->equalTo($redirectUrl));

        return $response;
    }

    public function testGetRouteFromFlatUrl()
    {
        $request = $this->getMockRequest('/admin', '/admin');
        $result = $this->router->getRouteForRequest($request);
        $this->assertInstanceOf('Store_RequestRoute_AdminLegacy', $result);

        $request = $this->getMockRequest('/admin/index.php?ToDo=action', '/admin/index.php');
        $result = $this->router->getRouteForRequest($request);
        $this->assertInstanceOf('Store_RequestRoute_AdminLegacy', $result);
    }

    public function testGetRouteFromInvalidUrl()
    {
        $request = $this->getMockRequest('/admin-why-this', '/admin-why-this');
        $result  = $this->router->getRouteForRequest($request);
        $this->assertFalse($result);
    }
}
