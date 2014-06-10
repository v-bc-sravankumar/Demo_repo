<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Exact extends Unit_Checkout_Online
{
	public $moduleName = 'exact';

	public $vars = array(
		'username' => 'A00990-01',
		'password' => 'cadsmwp',
		'transtype' => 'SALE',
	);
}
