<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Cynergydata extends Unit_Checkout_Online
{
	public $moduleName = 'cynergydata';

	public $vars = array(
		'transtype' => 'SALE',
		'authcode' => '',
		'password' => '3375',
		'username' => 'interspirePayTest',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form['creditcard_ccno'] = '4030000010001234';
	}

	/*
	public function testOrderMarkedAsPaid ()
	{
		// duplicate transaction checking prevents this from being easily tested right now
		$this->markTestIncomplete();
	}
	*/
}
