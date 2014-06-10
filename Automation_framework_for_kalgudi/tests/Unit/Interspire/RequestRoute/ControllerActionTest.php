<?php

namespace Unit\Interspire\RequestRoute;

use Interspire_RequestRoute_ControllerAction;

class ControllerActionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * This test does a lot. It tests permutations of Accept media-types and
     * controller action responses, feeds in a media-types list, controller
     * class and an action, and then checks the resulting status code and body.
     *
     * @param $acceptMediaTypes
     * @param $controllerClass
     * @param $controllerAction
     * @param $expectedResponseStatus
     * @param $expectedResponseBody
     * @dataProvider contentNegotiationData
     */
    public function testContentNegotiation(
        $acceptMediaTypes,
        $controllerClass,
        $controllerAction,
        $expectedResponseStatus,
        $expectedResponseBody
    ) {
        //// Setup ////
        // Response. We use a spy because we only care about two things: the
        // resulting status code and body that are sent to the client. How
        // it gets there doesn't matter to us (and it's damn complicated to match
        // the convoluted control flow of _follow()).
        $response = new SpyResponse;
        $response->setSendSpy(function($spy) use ($expectedResponseBody, $expectedResponseStatus) {
            \PHPUnit_Framework_TestCase::assertEquals($expectedResponseBody, $spy->getBody());
            \PHPUnit_Framework_TestCase::assertEquals($expectedResponseStatus, $spy->getStatus());
        });

        // Request. This tees up access to the Response, as well as the
        // media-types. Interspire_Request::getAcceptMediaTypes() already
        // has its own tests, so we're stubbing in the results.
        $request = $this->getRequestMock($response, $acceptMediaTypes);

        // The route itself.
        $route = $this->getRouteForControllerAction($controllerClass, $controllerAction);

        //// Exercise ////
        $this->assertTrue($route->processFollow($request));
        $this->assertTrue($response->wasSendCalled());
    }

    public function contentNegotiationData()
    {
        // Each row:
        // array(
        //  Provided Request::getAcceptMediaTypes(),
        //  Provided Controller::getClassName(),
        //  Provided Controller::getActionName(),
        //  Expected Response::getStatus(),
        //  Expected Response::getBody(),
        // )
        return array(
            // Permissive: accepts anything, sends back a JSON response.
            array(
                array('application/json'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyPermissiveController',
                'test',
                200,
                '{"status":"ok"}',
            ),
            array(
                array('*/*'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyPermissiveController',
                'test',
                200,
                '{"status":"ok"}',
            ),

            // Restrictive: accepts application/json-only; rejects everything else with 406.
            array(
                array('application/json', '*/*'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyRestrictiveController',
                'test',
                200,
                '{"status":"ok"}',
            ),
            array(
                array('text/html', '*/*'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyRestrictiveController',
                'test',
                406,
                '',
            ),

            // Explosive: dies with an error.
            array(
                array('application/json'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyExplosiveController',
                'test',
                500,
                '{"error":"Something went wrong"}',
            ),

            // Status Code Fiddler: changes status code to 201; sets a body.
            array(
                array('*/*'),
                '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyStatusCodeController',
                'test',
                201,
                '{"status":"created"}',
            ),
        );
    }


    /**
     * On returning false from a controller action, the request route should immediately
     * return without sending a response. This is how routing "fall-through" is supported.
     */
    public function testFallThroughRouting()
    {
        //// Setup ////
        $response = new SpyResponse();
        $request = $this->getRequestMock($response);
        $route = $this->getRouteForControllerAction(
            '\Unit\Interspire\RequestRoute\ControllerActionTest_FallthroughController',
            'test'
        );

        //// Exercise ////
        $this->assertFalse($route->processFollow($request));
        $this->assertFalse($response->wasSendCalled());
    }

    public function testCreateControllerForControllerService()
    {
        $object = new \stdClass();

        $GLOBALS['app']['controllers.storefront.test'] = $object;

        $action = new TestAction();
        $controller = $action->getController('Storefront\TestController');

        $this->assertEquals($object, $controller);

        unset($GLOBALS['app']['controllers.storefront.test']);
    }

    public function testCreateControllerForControllerName()
    {
        $controllerName = __NAMESPACE__ . '\TestController';

        $action = new TestAction();
        $controller = $action->getController($controllerName);

        $this->assertInstanceOf($controllerName, $controller);
    }

    public function testViewReturnedByControllerIsUsed()
    {
        $view = new \Interspire_Action_View('test data');
        $view->setContentType('text/xml');

        $controller = $this->getMock('Interspire_Action_Controller', array('testAction'));
        $controller
            ->expects($this->once())
            ->method('testAction')
            ->will($this->returnValue($view));

        $response = $this->getMock('Interspire_Response', array('send'));
        $request = $this->getRequestMock($response, array('*/*'));

        $route = $this->getMock('Interspire_RequestRoute_ControllerAction', array('createController'));
        $route
            ->expects($this->once())
            ->method('createController')
            ->with($this->equalTo('TestController'))
            ->will($this->returnValue($controller));

        $route
            ->setControllerName('TestController')
            ->setActionName('test');

        $route->processFollow($request);

        $this->assertEquals('test data', $response->getBody());
        $this->assertEquals('text/xml', $response->getHeader('Content-Type'));
        $this->assertEquals(\Interspire_Response::STATUS_OK, $response->getStatus());
    }


    // Helpers

    protected function getRequestMock($response, array $acceptMediaTypes=array())
    {
        $request = $this->getMock('Interspire_Request');
        $request->expects($this->once())
                ->method('getResponse')
                ->will($this->returnValue($response));
        $request->expects($this->any())
                ->method('getAcceptMediaTypes')
                ->will($this->returnValue($acceptMediaTypes));
        return $request;
    }

    protected function getRouteForControllerAction($controllerClass, $action)
    {
        $route = new \Interspire_RequestRoute_ControllerAction();
        $route->setControllerName($controllerClass);
        $route->setActionName($action);
        return $route;
    }
}

// HACK: It sucks that we have to create dummy classes, but there doesn't
//       appear to be a way around it when using dispatch-by-class-name like
//       Interspire_RequestRoute_ControllerAction does.

// Response Spy
class SpyResponse extends \Interspire_Response {
    protected $sendSpy;
    protected $sendCalled;
    public function setSendSpy($callback) {
        $this->sendSpy = $callback;
        $this->sendCalled = false;
    }
    public function send() {
        $cb = $this->sendSpy;
        if (isset($cb)) {
            $cb($this);
        }
        $this->sendCalled = true;
    }
    public function wasSendCalled() {
        return (bool) $this->sendCalled;
    }
}

// Controllers
class ControllerActionTest_DummyRestrictiveController extends \Interspire_Action_Controller {
    public function accepts($action) {
        return array('application/json' => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView');
    }
    public function testAction() {
        return array('status' => 'ok');
    }
}
class ControllerActionTest_DummyPermissiveController extends \Interspire_Action_Controller {
    public function accepts($action) {
        return array(
           'application/json' => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView',
           '*/*'              => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView',
        );
    }
    public function testAction() {
        return array('status' => 'ok');
    }
}
class ControllerActionTest_DummyExplosiveController extends \Interspire_Action_Controller {
    public function accepts($action) {
        return array('*/*' => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView');
    }
    public function testAction() {
        throw new \Exception("We screwed up.");
    }
}
class ControllerActionTest_DummyStatusCodeController extends \Interspire_Action_Controller {
    public function accepts($action) {
        return array('*/*' => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView');
    }
    public function testAction() {
        $this->response->setStatus(201);
        $this->response->setBody('{"status":"created"}');
    }
}
class ControllerActionTest_FallthroughController extends \Interspire_Action_Controller {
    public function accepts($action) {
        return array('*/*' => '\Unit\Interspire\RequestRoute\ControllerActionTest_DummyDataView');
    }
    public function testAction() {
        // Can't handle this route; return false to signal a fall-through.
        return false;
    }
}

// Views
class ControllerActionTest_DummyDataView extends \Interspire_Action_View {
    protected $contentType = 'application/json';
    public function render() {
        return json_encode($this->viewData);
    }
}

class TestAction extends Interspire_RequestRoute_ControllerAction
{
    public function getController($controllerName)
    {
        return $this->createController($controllerName);
    }
}

class TestController
{

}
