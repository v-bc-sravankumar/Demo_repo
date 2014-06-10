<?php
use Store_Feature as Feature;
use Interspire_Response as Response;
use Store_Config as Config;
use Playbook\PlayState;
class Unit_Controllers_PlayStateControllerTest extends PHPUnit_Framework_TestCase
{

    protected $controller;

    const FEATURE_RUNWAY = 'Runway';

    public function testPlayEndpointNotFoundWhenRunwayDisabled() {

        // setup
        Feature::disable(self::FEATURE_RUNWAY);
        $this->assertEquals(false, Feature::isEnabled('Runway'));

        $controller = new PlayStateController();

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $body = "";
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'GET',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->getAction();

        // verify
        $this->assertEquals(Response::STATUS_NOT_FOUND, $request->getResponse()->getStatus());

    }

    public function testExistingPlayReturnsCorrectPlay() {

        $playState = array (
            'play_id' => 'play1',
            'playbook_id' => 'playbook1',
            'seen' => false,
            'status' => 'todo'
        );

        $p = new PlayState();
        $p->populateFromArray($playState);
        $playStateObject = $p;

        $repository = $this->getMock('Repository\PlayStates', array (
            'findOneByPlayIdAndPlaybookId'
        ));
        $repository->expects($this->any())->method('findOneByPlayIdAndPlaybookId')->will($this->returnValue($playStateObject));
        $this->assertEquals($playStateObject, $repository->findOneByPlayIdAndPlaybookId($playState['play_id'], $playState['playbook_id']));

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $request = new Interspire_Request(null, null, null, array (
            'REQUEST_METHOD' => 'GET',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        // verify
        $this->assertEquals(Response::STATUS_OK, $request->getResponse()->getStatus());

        $this->assertEquals($playState, $response);

    }

    public function testPostingAnExistingRecordFails() {

        $playState = array (
            'play_id' => 'play1',
            'playbook_id' => 'playbook1',
            'seen' => false,
            'status' => 'todo'
        );

        $p = new PlayState();
        $p->populateFromArray($playState);
        $playStateObject = $p;

        $repository = $this->getMock('Repository\PlayStates', array (
            'findOneByPlayIdAndPlaybookId'
        ));
        $repository->expects($this->any())->method('findOneByPlayIdAndPlaybookId')->will($this->returnValue($playStateObject));
        $this->assertEquals($playStateObject, $repository->findOneByPlayIdAndPlaybookId($playState['play_id'], $playState['playbook_id']));

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $body = json_encode($playState);
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        $this->assertEquals(Response::STATUS_NOT_ACCEPTABLE, $request->getResponse()->getStatus());

    }

    public function testPuttingNewRecordFails() {

        $repository = $this->getMock('Repository\PlayStates', array (
            'findOneByPlayIdAndPlaybookId'
        ));
        $repository->expects($this->any())->method('findOneByPlayIdAndPlaybookId')->will($this->returnValue(false));
        $this->assertEquals(false, $repository->findOneByPlayIdAndPlaybookId($playState['play_id'], $playState['playbook_id']));

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $newPlay = array (
            'play_id' => 'play2',
            'playbook_id' => 'playbook1',
            'seen' => false,
            'status' => 'todo'
        );

        $body = json_encode($newPlay);
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'PUT',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        $this->assertEquals(Response::STATUS_NOT_ACCEPTABLE, $request->getResponse()->getStatus());

    }

    public function testPostingMismatchingPlaybookIdCaught() {

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $newPlay = array (
            'play_id' => 'play1',
            'playbook_id' => 'wrong',
            'seen' => false,
            'status' => 'todo'
        );

        $body = json_encode($newPlay);
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        $this->assertEquals(Response::STATUS_NOT_ACCEPTABLE, $request->getResponse()->getStatus());

    }

    public function testPostingMismatchingPlayIdCaught() {

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $newPlay = array (
            'play_id' => 1,
            'playbook_id' => 'playbook1',
            'seen' => false,
            'status' => 'todo'
        );

        $body = json_encode($newPlay);
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        $this->assertEquals(Response::STATUS_NOT_ACCEPTABLE, $request->getResponse()->getStatus());

    }

    public function testPostingInvalidStatusFails() {

        $controller = new PlayStateController($repository);

        Feature::override(self::FEATURE_RUNWAY, true);

        $get = array (
            'version' => 1,
            'playbook_id' => 'playbook1',
            'id' => 'play1'
        );

        $newPlay = array (
            'play_id' => '1',
            'playbook_id' => 'playbook1',
            'seen' => false,
            'status' => 'wrong'
        );

        $body = json_encode($newPlay);
        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        // exercise
        $response = $controller->playAction();

        $this->assertEquals(Response::STATUS_NOT_ACCEPTABLE, $request->getResponse()->getStatus());

    }
}
