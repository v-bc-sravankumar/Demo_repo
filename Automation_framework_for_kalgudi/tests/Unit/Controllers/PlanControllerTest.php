<?php
namespace Unit\Controllers;

class PlanControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var PlanController
	 */
	protected $controller;
	public function setUp()
	{
		$this->controller = new \PlanController();
	}

	/**
	 * @param $body
	 * @param $expected
	 * @dataProvider eventIsFiredData
	 */
	public function testReportUpgradePath($from, $event)
	{
		$getData = array('from' => $from);
		$request = new \Interspire_Request($getData);
		$this->controller->setRequest($request);
		$this->controller->setResponse($request->getResponse());

		// Event setup
		$wasCalled = false;
		\Store_Event::bind($event, function() use (&$wasCalled) {
			$wasCalled = true;
		});

		// Exercise
		$this->assertNull(
			$this->controller->upgradeAction($getData)
		);

		// Verify
		$this->assertTrue($wasCalled);
		$this->assertEquals(200, $request->getResponse()->getStatus());
	}

	public function testIgnoreMissingPath()
	{
		$getData = array('from' => 'nope-nope-nope');
		$request = new \Interspire_Request($getData);
		$this->controller->setRequest($request);
		$this->controller->setResponse($request->getResponse());

		// Exercise
		$this->assertNull($this->controller->upgradeAction($getData));

		// Verify (It doesn't care what happened.)
		$this->assertEquals(200, $request->getResponse()->getStatus());
	}

	public function eventIsFiredData()
	{
		return array(
			array(
				'orders', \Store_Event::EVENT_PLAN_UPGRADE_ABANDONED_CART_VIEW_ORDERS
			),
			array(
				'notifications', \Store_Event::EVENT_PLAN_UPGRADE_ABANDONED_CART_NOTIFICATION_SETTINGS,
			),
			array(
				'stats', \Store_Event::EVENT_PLAN_UPGRADE_ABANDONED_CART_STATS,
			),
		);
	}
}
