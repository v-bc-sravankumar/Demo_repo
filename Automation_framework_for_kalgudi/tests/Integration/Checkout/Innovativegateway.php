<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Innovativegateway extends Unit_Checkout_Online
{
	public $moduleName = 'innovativegateway';

	public $vars = array(
		'accountname' => 'gatewaytest',
		'accountpassword' => 'GateTest2002',
		'cardcode' => 'NO',
		'testmode' => 'YES',
	);
}
