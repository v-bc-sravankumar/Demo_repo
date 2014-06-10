<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * Disabled due to invalid credentials
 *
 * @group disabled
 * @group remote
 */
class Unit_Checkout_Quantumgateway extends Unit_Checkout_Online
{
	public $moduleName = 'quantumgateway';

	public $vars = array(
		'cardcode' => 'YES',
		'accountname' => 'interspireshoppingcart',
		'accountpassword' => 'Ast3UpSHzb4YTMq',
	);
}
