<?php

namespace Unit\Modules\Checkout;

use CHECKOUT_STRIPE;
use PHPUnit_Framework_TestCase;

class Stripe extends PHPUnit_Framework_TestCase {

	public function clearTokenProvider()
	{
		return array(
			array('LIVE', 'TEST', 'USER', true,  array('LIVE', 'TEST', 'USER')),
			array('LIVE', 'TEST', 'USER', false, array( null,   null,   null )),
			array( null,   null,   null , false, array( null,   null,   null )),
		);
	}

	/**
	 * @dataProvider clearTokenProvider
	 */
	public function testTokensClearedIfNotAuthorized($live, $test, $userId, $authorized, $expected)
	{
		$mock = $this
			->getMockBuilder('Services\Payments\Stripe\BCStripe')
			->disableOriginalConstructor()
			->setMethods(array('isTokenAuthorized'))
			->getMock();

		if (empty($live) && empty($test)) {
			$mock->expects($this->never())->method('isTokenAuthorized');
		} else {
			$mock->expects($this->once())->method('isTokenAuthorized')->will($this->returnValue($authorized));
		}

		$stripe = new StripeWrapper($mock);
		$stripe->setTokensAndUserId($live, $test, $userId);

		$stripe->callRevokeTokens();

		$this->assertEquals($expected, $stripe->getTokensAndUserId());
	}

}

class StripeWrapper extends CHECKOUT_STRIPE {

	public function __construct($bcStripe)
	{
		$this->gateway = $bcStripe;
	}

	public function callRevokeTokens()
	{
		$this->revokeTokensIfDeauthorized();
	}

	public function setTokensAndUserId($live, $test, $userId)
	{
		$this->setLiveAccessToken($live);
		$this->setTestAccessToken($test);
		$this->setStripeUserId($userId);
	}

	public function getTokensAndUserId()
	{
		return array(
			$this->getLiveAccessToken(),
			$this->getTestAccessToken(),
			$this->getStripeUserId(),
		);
	}

}