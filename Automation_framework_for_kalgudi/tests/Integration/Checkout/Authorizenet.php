<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Authorizenet extends Unit_Checkout_Online
{
	public $moduleName = 'authorizenet';
	public $formPrefix = 'AuthorizeNet_';

	public $vars = array(
		'testmode' => 'YES',
		'merchantid' => '28k2DqPYc',
		'transactionkey' => '3B5S29Q8fT2wpb4q',
		'transactiontype' => 'AUTH_CAPTURE',
		'requirecvv2' => 'YES',
	);
}
