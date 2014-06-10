<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Directone extends Unit_Checkout_Online
{
	public $moduleName = 'directone';

	public $vars = array(
		'testmode' => 'YES',
		'password' => 'te$t_vend0r',
		'merchantid' => 'test_vendor',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form['creditcard_ccno'] = '4444333322221111';
	}
}
