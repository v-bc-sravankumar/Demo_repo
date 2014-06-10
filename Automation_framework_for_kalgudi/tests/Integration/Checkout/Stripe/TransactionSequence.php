<?php
use Services\Payments\Stripe\BCStripe;

require_once('Common.php');

abstract class Unit_Checkout_Stripe_TransactionSequence extends Unit_Checkout_Stripe_Common
{
	/**
	 * @var BCStripe
	 */
	private $gateway;

	public function setUp()
	{
		if (!defined('STRIPE_TEST_API_TOKEN')) {
			$this->markTestSkipped();
		}

		$this->gateway = new BCStripe('US', 'USD');
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
		if(!empty($txn['exception'])) {
			$this->setExpectedException($txn['exception']);
		}

		if(!empty($txn['api_key'])) {
			$this->gateway->setApiToken($txn['api_key']);
		}

		/** @var Stripe_Object $response */
		if(isset($txn['originalTxnId'])) {
			$transactionId = $this->getTransactionId($txn['originalTxnId']);
			if(is_array($txn['params']) && isset($txn['params']['id'])) {
				$params['id'] = $transactionId;
			} else {
				$params = $transactionId;
			}
		} else {
			$params = $txn['params'];
		}

		$response = $this->gateway->{$txn['process']}($params);

		//var_dump($response);

		if(!empty($txn['expected'])) {
			$self = $this;
			$checkExpected = function($expected, &$response) use (&$checkExpected, $self) {
				foreach($expected as $key => $value) {
					if(is_array($value)) {
						$checkExpected($value, $response->$key);
					} else {
						$actualValue = $response->$key;
						if($value === null) {
							$self->assertNull($actualValue);
						} else {
							$self->assertEquals($actualValue, $value, $key . ' : ('.gettype($actualValue) .') '. $actualValue . ' != expected ('.gettype($value) .') '. $value);
						}
					}
				}
			};

			$checkExpected($txn['expected'], $response);
		}

		if(isset($txn['id'])) {
			self::recordTransactionId($txn['id'], $response->id);
		}

		$this->printTransactionLine($txn, $response->id);
	}

	/**
	 * Spit out the relevant information to stdout in order to collect and send to HPS
	 */
	private function printTransactionLine($txn, $transactionId)
	{
		echo '[' . implode(':', array($txn['card']['number'], $txn['process'], $txn['expected'])) . '][' . $txn['id'] . ']:' . $transactionId . PHP_EOL;
	}

}