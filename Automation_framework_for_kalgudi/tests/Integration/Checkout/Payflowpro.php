<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Payflowpro extends Unit_Checkout_Online
{
	public $moduleName = 'payflowpro';
	public $formPrefix = 'PayflowPro_';

	public $vars = array(
		'vendorid' => 'yukichen',
		'userid' => 'yukichen',
		'password' => 'Pass1234',
		'partnerid' => 'VSA',
		'requirecvv2' => 'YES',
		'transactiontype' => 'S',
		'testmode' => 'YES',
	);
}
