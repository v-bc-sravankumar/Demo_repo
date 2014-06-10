<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Payleap extends Unit_Checkout_Online
{
	public $moduleName = 'payleap';

	public $vars = array(
		'testmode' => 'YES',
		'transtype' => 'SALE',
		'password' => 'Password789',
		'username' => 'interspire123',
	);

	public function setUp ()
	{
		parent::setUp();
		$this->order['orders'][$this->orderId]['ordbillphone']='9999999999';
	}

}
