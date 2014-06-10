<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * r7186: disabling the paypalpaymentsprous test because I can't figure out if it's incorrect test details
 * or if paypal are having temporary issues
 *
 * @group disabled
 * @group remote
 */
class Unit_Checkout_Paypalpaymentsprous extends Unit_Checkout_Online
{
	public $moduleName = 'paypalpaymentsprous';

	public $vars = array(
		'username' => 'yuki.c_1207726004_biz_api1.interspire.com',
		'password' => 'L8LXK3ZNA7L357W8',
		'signature' => 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-Aqp5gGMRyCF7vWyx4azR2Aq0iM6D',
		'transactiontype' => 'Sale',
		'cardcode' => 'YES',
		'cardinalprocessorid' => '',
		'cardinalmerchantid' => '',
		'cardinaltransactionpwd' => '',
		'testmode' => 'YES',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form[$this->formPrefix . 'ccno'] = '4595258908900506';
	}

	protected function callProcessPaymentForm ($module, $form)
	{
		return $module->ProcessPaymentForm($form, true);
	}
}
