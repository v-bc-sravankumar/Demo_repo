<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Plugnpay extends Unit_Checkout_Online
{
	public $moduleName = 'plugnpay';

	public $vars = array(
		'cardcode' => 'NO',
		'accountpassword' => 'pnpdemo',
		'accountname' => 'pnpdemo',
	);
}
