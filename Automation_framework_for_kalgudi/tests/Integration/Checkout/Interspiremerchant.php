<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Interspiremerchant extends Unit_Checkout_Online
{
	public $moduleName = 'interspiremerchant';

	public $vars = array(
		'securenet_id' => '7001174',
		'securenet_secure_key' => 'ybMkAvWwHWxA',
		'gatewaymode' => 'CERTIFICATION',
		'testmode' => 'NO',
		'securenet_transaction_type' => 'AUTH_CAPTURE',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form['creditcard_ccno'] = '4444333322221111';
	}

}
