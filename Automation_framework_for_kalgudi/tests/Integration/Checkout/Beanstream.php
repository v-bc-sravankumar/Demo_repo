<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Beanstream extends Unit_Checkout_Online
{
	public $moduleName = 'beanstream';

	public $vars = array(
		'cardcode' => 'NO',
		'merchantid' => '172560000',
	);

	public function setUp ()
	{
		parent::setUp();

		// beanstream specific cc
		$this->form['creditcard_ccno'] = '4030000010001234';

		// beanstream supports up to $99 on testing
		$this->order['orders'][$this->orderId]['total_inc_tax'] = rand(1, 99) . '.00';
	}

	/*
	public function testOrderMarkedAsPaid ()
	{
		// duplicate transaction checking prevents this from being easily tested right now
		$this->markTestIncomplete();
	}
	*/
}
