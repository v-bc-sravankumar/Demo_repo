<?php

class Unit_EmailIntegration_Subscription_Newsletter_routeSubscription extends Interspire_IntegrationTest
{
	const TEST_EMAIL = 'gwilym.evans@interspire.com';

	public $sub;

	public function setUp ()
	{
		parent::setUp();
		$this->sub = new Interspire_EmailIntegration_Subscription_Newsletter(self::TEST_EMAIL, 'Gwilym Evans');
		Interspire_EmailIntegration_Rule::deleteAllRules();
	}

	public function testToInternalSubscribersTable ()
	{
		$results = $this->sub->routeSubscription();
		$this->assertEquals(1, count($results));

		$results = $results[0];
		$this->assertEquals('emailintegration_exportonly', $results->moduleId);
		$this->assertTrue($results->success);
		$this->assertFalse($results->pending);
	}
}
