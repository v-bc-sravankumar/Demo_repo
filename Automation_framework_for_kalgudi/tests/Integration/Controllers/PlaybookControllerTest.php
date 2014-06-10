<?php
use Store_Feature as Feature;
use Store_Config as Config;
use Playbook\PlayState;;

class Unit_Controllers_PlaybookControllerTest extends PHPUnit_Framework_TestCase
{

	protected $controller;

	const FEATURE_RUNWAY = 'Runway';

	public function setUp()
	{
		$this->controller = new PlaybookController();
	}

	public function testPlaybookNotFoundWhenRunwayDisabled()
	{

		// setup
		Feature::override(self::FEATURE_RUNWAY, false);

		$this->assertEquals(false, Feature::isEnabled('Runway'));

		$body = "";
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'GET', 'HTTP_ACCEPT' => 'application/json'), $body);
		$this->controller->setRequest($request);
		$this->controller->setResponse($request->getResponse());

		// exercise
		$this->controller->getAction();

		// verify
		$this->assertEquals(404, $request->getResponse()->getStatus());

	}

	public function testPlaybookReturnsCorrectPlays()
	{
		$playStates = array(
			array(
				'play_id' => 1,
				'playbook_id' => 'playbook1',
				'seen' => false,
				'status' => 'todo'
			),
			array(
				'play_id' => 'play2',
				'playbook_id' => 'playbook1',
				'seen' => false,
				'status' => 'skipped'
			),
			array(
				'play_id' => 'play3',
				'playbook_id' => 'playbook1',
				'seen' => false,
				'status' => 'done'
			),
			array(
				'play_id' => 'play4',
				'playbook_id' => 'playbook1',
				'seen' => true,
				'status' => 'todo'
			),
			array(
				'play_id' => 'play5',
				'playbook_id' => 'playbook1',
				'seen' => true,
				'status' => 'skipped'
			),
			array(
				'play_id' => 'play6',
				'playbook_id' => 'playbook1',
				'seen' => true,
				'status' => 'done'
			),
		);

		foreach($playStates as $playState) {
			$p = new PlayState();
			$p->populateFromArray($playState);
			$playStateObjects[] = $p;
		}

		$repository = $this->getMock('Repository\PlayStates', array('findByPlaybookId'));
		$repository->expects($this->any())->method('findByPlaybookId')->will($this->returnValue($playStateObjects));
		$this->assertEquals($playStateObjects, $repository->findByPlaybookId(1));

		$controller = new PlaybookController($repository);

		Feature::override(self::FEATURE_RUNWAY, true);

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'GET', 'HTTP_ACCEPT' => 'application/json'), $body);
		$controller->setRequest($request);
		$controller->setResponse($request->getResponse());

		// exercise
		$response = $controller->getAction();

		// verify
		$this->assertEquals(200, $request->getResponse()->getStatus());

		$this->assertEquals($playStates, $response);



	}

}
