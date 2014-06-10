<?php

use Services\Payments\Stripe\BCStripe;

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Stripe extends Unit_Checkout_Online
{
	public $moduleName = 'stripe';

	public $vars = array(
		'test_access_token' => 'sk_test_rP1hu9UVBgrHhPbhd1vhymbk',
		'testmode' => 'YES',
		'stripe_transaction_type' => 'AUTH_CAPTURE',
		'cardcode' => 'YES',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form['creditcard_ccno'] = '4242424242424242';
	}
}
