<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Qbms extends Unit_Checkout_Online
{
	public $moduleName = 'qbms';

	public $vars = array(
		'ApplicationLogin' => 'qbms-test.interspire.mybigcommerce.com',
		'AppID' => '110334758',
		'ConnectionTicket' => 'TGT-39-Tzgf2tXa6JxLSWV6hJgYxQ',
		'testmode' => 'YES',
	);
}
