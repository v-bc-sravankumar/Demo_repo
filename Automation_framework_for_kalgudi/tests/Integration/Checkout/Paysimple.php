<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Paysimple extends Unit_Checkout_Online
{
	public $moduleName = 'paysimple';

	public $vars = array(
		'merchantkey' => 'Jx7TUWftItYEWFlnGTdVu1DEFaS4soTWGdTMhqLI5pfIMA4xduBGvrTtS6TKa0RuJjrPmlbBtUObHnD1ESrtP4zAXBH0Lc3NG5hqMB1FcfQwhZuZq6Yl1HKZgTDMDuz0',
		'testmode' => 'YES',
	);

	public function setUp ()
	{
		parent::setUp();

		// check http://www.paysimple.com/small-business-wiki/Test_Payment_Information if these need updating
		$this->form[$this->formPrefix . 'ccno'] = '4111111111111111';
		$this->form[$this->formPrefix . 'ccexpm'] = '12';
		$this->form[$this->formPrefix . 'ccexpy'] = '10';
		$this->form[$this->formPrefix . 'cccvd'] = '111';

		// Use a US based address for PaySimple
		$this->order['orders'][$this->orderId]['ordbillsuburb'] = 'Beverly Hills';
		$this->order['orders'][$this->orderId]['ordbillzip'] = 90210;
		$this->order['orders'][$this->orderId]['ordbillstate'] = 'California';
		$this->order['orders'][$this->orderId]['ordbillcountry'] = 'United States';
		$this->order['orders'][$this->orderId]['ordbillcountryid'] = 226;
		$this->order['orders'][$this->orderId]['ordbillcountrycode'] = 'US';
	}

	public function testProcessPaymentForm()
	{
		$this->markTestIncomplete('This test has been disabled pending a full review of the PaySimple module.');
	}
}
