<?php
use Services\Payments\HPS\Transaction;
use Services\Payments\HPS\Hps;

require_once('Common.php');

abstract class Unit_Checkout_HPS_TransactionSequence extends Unit_Checkout_HPS_Common
{
	/**
	 * @var Hps
	 */
	private $gateway;

	public function setUp()
	{
		if (!defined('HPS_SITE_ID') || !defined('HPS_DEVICE_ID') || !defined('HPS_LICENSE_ID') || !defined('HPS_USER_ID') || !defined('HPS_PASSWORD')) {
			$this->markTestSkipped();
		}

		$this->gateway = new Hps();
		$this->loadTestCredentials($this->gateway);
	}

	public static function setUpBeforeClass()
	{
		$GLOBALS['TransactionLog'] = array();
	}

	private static function recordTransactionId($id, $txnId)
	{
		$GLOBALS['TransactionLog'][$id] = $txnId;
	}

	private static function getTransactionId($id)
	{
		return $GLOBALS['TransactionLog'][$id];
	}

	public abstract function predefinedTransactionSequence();

	/**
	 * @dataProvider predefinedTransactionSequence
	 */
	public function testPredefinedTransaction($txn)
	{
		if (isset($txn['txnId'])) {
			$this->gateway->{$txn['process']}(self::getTransactionId($txn['txnId']));
		} else {
			$this->gateway->{$txn['process']}();
		}

		if(isset($txn['amount'])) {
			$this->gateway->setAmount($txn['amount']);
		}

		if(isset($txn['cardNumber'])) {
			$this->gateway->setCardNumber($txn['cardNumber']);
		}

		if(isset($txn['expiryMM']) && isset($txn['expiryYY'])) {
			$this->gateway->setCardExpiry($txn['expiryMM'], $txn['expiryYY']);
		}

		if (isset($txn['cvv2'])) {
			$this->gateway->setCardCvvc($txn['cvv2']);
		}

		if (isset($txn['address'])) {
			$this->gateway->setCustomerAddress($txn['address']);
		}

		if (isset($txn['zip'])) {
			$this->gateway->setCustomerZip($txn['zip']);
		}

		// Only set the invoice id if we also have the shipping date, as we will only set either or these properties if we have both set. This is a requirement by Hps
		if(isset($txn['id']) && isset($txn['shippingDate'])) {
			$this->gateway->setShippingDate($txn['shippingDate']);
			$this->gateway->setOrderId($txn['id']);
		}

		$this->gateway->setAllowDuplicate(true);

		$transaction = $this->gateway->processTransaction();

		if(isset($txn['expected'])) {
			$this->assertTrue($transaction->{$txn['expected']}());
		}

		if(!empty($txn['avsCode'])) {
			$avsCodeResponse = $transaction->getAVSCode();
			$this->assertEquals($txn['avsCode'], $avsCodeResponse, "Expecting AVS Code '".$txn['avsCode']."', though received '".$avsCodeResponse."'");
		}

		if(!empty($txn['avsVerified'])) {
			foreach($txn['avsVerified'] as $avsOption => $expected) {
				$this->assertEquals($expected, $transaction->isAVSVerified($avsOption), 'AVS check for \''.$avsOption.'\' option expected to return '.($expected?'true':'false'));
			}
		}

		if(!empty($txn['cvvCode'])) {
			$cvvCodeResponse = $transaction->getCVVCode();
			$this->assertEquals($txn['cvvCode'], $cvvCodeResponse, "Expecting CVV Code '".$txn['cvvCode']."', though received '".$cvvCodeResponse."'");
		}

		if(!empty($txn['cvvVerified'])) {
			foreach($txn['cvvVerified'] as $cvvOption => $expected) {
				$this->assertEquals($expected, $transaction->isCvvVerified($cvvOption), 'CVV check for \''.$cvvOption.'\' option expected to return '.($expected?'true':'false'));
			}
		}

		if(isset($txn['id'])) {
			self::recordTransactionId($txn['id'], $transaction->getTransactionId());
		}

		$this->printTransactionLine($txn, $transaction->getTransactionId());
	}

	/**
	 * Spit out the relevant information to stdout in order to collect and send to HPS
	 */
	private function printTransactionLine($txn, $transactionId)
	{
		echo '[' . implode(':', array($txn['cardType'], $txn['cardNumber'], $txn['process'], $txn['expected'])) . '][' . $txn['id'] . ']:' . $transactionId . PHP_EOL;
	}

}