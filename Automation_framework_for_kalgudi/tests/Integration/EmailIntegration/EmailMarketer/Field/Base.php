<?php

abstract class Unit_EmailIntegration_EmailMarketer_Field_Base extends Interspire_IntegrationTest
{
	/**
	* @dataProvider dataProviderFromSubscriptionToProvider
	* @param Interspire_EmailIntegration_Field $subscriptionField
	* @param mixed $input
	* @param mixed $expected
	*/
	public function testFromSubscriptionToProvider ($subscriptionField, $input, $expected)
	{
		$providerFieldClassName = $this->providerFieldClassName;
		$providerField = new $providerFieldClassName;
		$result = $providerField->fromSubscriptionToProvider($subscriptionField, $input);
		$this->assertEquals($expected, $result);
	}
}
