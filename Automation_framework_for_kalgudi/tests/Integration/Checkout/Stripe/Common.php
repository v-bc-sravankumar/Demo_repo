<?php
use Services\Payments\Stripe\BCStripe;

abstract class Unit_Checkout_Stripe_Common extends PHPUnit_Framework_TestCase
{
	/**
	 * Load the internal BC Stripe Account credentials
	 * @param BCStripe $gateway
	 */
	public function loadTestCredentials(&$gateway)
	{
		$gateway->setApiToken(STRIPE_TEST_API_TOKEN);
	}
}
