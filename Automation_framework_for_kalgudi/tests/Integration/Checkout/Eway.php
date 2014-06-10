<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Eway extends Unit_Checkout_Online
{
	public $moduleName = 'eway';
	public $formPrefix = 'eway_';

	public $vars = array(
		'customerid' => '',
		'testmode' => 'YES',
		'requirecvn' => 'YES',
	);
}
