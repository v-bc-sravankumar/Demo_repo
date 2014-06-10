<?php

class Unit_Controllers_EventsControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventsController
     */
    protected $controller;

    public function setUp()
    {
        $this->controller = new EventsController();
    }

    public function testPostValidEventTriggersEvent()
    {
        // setup
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'POST'),
            '{"name":"' . \Store_Event::EVENT_WIZARD_STORE_SETTINGS_ACTION_CLICKED . '"}');
        $this->controller->setRequest($request);
        $this->controller->setResponse($request->getResponse());
        $wasCalled = false;
        \Store_Event::bind(\Store_Event::EVENT_WIZARD_STORE_SETTINGS_ACTION_CLICKED, function () use (&$wasCalled) {
            $wasCalled = true;
        });

        // exercise
        $this->controller->indexAction();

        // verify
        $this->assertTrue($wasCalled);
        $this->assertEquals(201, $request->getResponse()->getStatus());
    }

    public function testPostWithEmptyEventNameIsUnprocessable()
    {
        // setup
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'POST'), '{"name":""}');
        $this->controller->setRequest($request);
        $this->controller->setResponse($request->getResponse());

        // exercise
        $this->controller->indexAction();

        // verify
        $this->assertEquals(422, $request->getResponse()->getStatus());
    }

    public function testPostWithNoEventNameIsNotAccepted()
    {
        // setup
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'POST'), '{}');
        $this->controller->setRequest($request);
        $this->controller->setResponse($request->getResponse());

        // exercise
        $this->controller->indexAction();

        // verify
        $this->assertEquals(400, $request->getResponse()->getStatus());
    }
}