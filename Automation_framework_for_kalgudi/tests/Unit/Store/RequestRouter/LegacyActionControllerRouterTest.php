<?php

namespace Unit\Store\RequestRouter;

use Store\RequestRouter\LegacyActionControllerRouter;

class LegacyActionControllerRouterTest extends \PHPUnit_Framework_TestCase
{
    private function getRequestForAction($action)
    {
        return new \Interspire_Request(
            array(),
            array(),
            array('action' => $action),
            array('REQUEST_URI' => '/foobar.php?action=' . $action)
        );
    }

    public function testGetRouteForRequestForMatchingRoute()
    {
        $controllerName = __NAMESPACE__ . '\TestController';

        $router = new LegacyActionControllerRouter();
        $router->addRoute('/foobar.php', $controllerName);

        $request = $this->getRequestForAction('foo');
        $route = $router->getRouteForRequest($request);

        $this->assertInstanceOf('\Store\RequestRoute\FrontControllerActionRoute', $route);
        $this->assertEquals($controllerName, $route->getControllerName());
        $this->assertEquals('foo', $route->getActionName());
    }

    public function testGetRouteForRequestForUnknownActionReturnsFalse()
    {
        $controllerName = __NAMESPACE__ . '\TestController';

        $router = new LegacyActionControllerRouter();
        $router->addRoute('/foobar.php', $controllerName);

        $request = $this->getRequestForAction('hello');
        $route = $router->getRouteForRequest($request);

        $this->assertFalse($route);
    }

    public function testGetRouteForRequestForUnknownControllerReturnsFalse()
    {
        $controllerName = __NAMESPACE__ . '\MissingController';

        $router = new LegacyActionControllerRouter();
        $router->addRoute('/foobar.php', $controllerName);

        $request = $this->getRequestForAction('foo');
        $route = $router->getRouteForRequest($request);

        $this->assertFalse($route);
    }
}

class TestController
{
    public function fooAction()
    {

    }

    public function barAction()
    {

    }
}
