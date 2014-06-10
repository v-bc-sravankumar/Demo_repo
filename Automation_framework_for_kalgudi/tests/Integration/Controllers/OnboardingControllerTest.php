<?php

class Unit_Controllers_OnboardingControllerTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var OnboardingController
	 */
	protected $controller;

	public function setUp()
	{
		$this->controller = new OnboardingController();
	}

	public function testPostValidEventTriggersEvent()
	{
		// setup
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'POST'),
			'{"event":"' . \Store_Event::EVENT_WIZARD_LAUNCH_ACTION_CLICKED_FAILED . '"}');
		$this->controller->setRequest($request);
		$this->controller->setResponse($request->getResponse());
		$wasCalled = false;
		Store_Event::bind(\Store_Event::EVENT_WIZARD_LAUNCH_ACTION_CLICKED_FAILED, function () use (&$wasCalled) {
			$wasCalled = true;
		});

		// exercise
		$this->controller->eventAction();

		// verify
		$this->assertTrue($wasCalled);
		$this->assertEquals(204, $request->getResponse()->getStatus());
	}

	public function testEventDataProvidedToEventCallback()
	{
		$body = new stdClass();
		$body->event = \Store_Event::EVENT_WIZARD_LAUNCH_ACTION_CLICKED_FAILED;
		$expectedData = array(
			'foo' => 1,
		);
		$body->data = $expectedData;


		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'POST'),
			json_encode($body));
		$this->controller->setRequest($request);
		$this->controller->setResponse($request->getResponse());

		$actualData = null;
		Store_Event::bind(\Store_Event::EVENT_WIZARD_LAUNCH_ACTION_CLICKED_FAILED, function (\Interspire_Event $event) use (&$actualData) {
			$actualData = $event->data;
		});

		$this->controller->eventAction();

		$this->assertInternalType('array',$actualData);
		$this->assertArrayHasKey('foo', $actualData);
		$this->assertEquals($expectedData['foo'], $actualData['foo']);
	}

}
