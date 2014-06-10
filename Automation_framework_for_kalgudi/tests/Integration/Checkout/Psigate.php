<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Psigate extends Unit_Checkout_Online
{
	public $moduleName = 'psigate';

	public $vars = array(
		'storeid' => 'teststore',
		'passphrase' => 'psigate1234',
		'transactiontype' => '0',
		'cardcode' => 'YES',
		'testmode' => 'YES',
	);
}
