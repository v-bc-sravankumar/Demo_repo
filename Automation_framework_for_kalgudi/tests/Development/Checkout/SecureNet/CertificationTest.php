<?php
use Services\Payments\SecureNet\SecureNet;

/**
 * A messy hack to run the SecureNet gateway certification test script.
 */
class Checkout_SecureNet_CertificationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var SecureNet
	 */
	private $gateway;

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

	public function setUp()
	{
		if (!defined('SECURENET_CERTIFICATION_ID') || !defined('SECURENET_CERTIFICATION_KEY')) {
			$this->markTestSkipped();
		}

		$this->gateway = new SecureNet();
		$this->gateway->setSecureNetId(SECURENET_CERTIFICATION_ID);
		$this->gateway->setSecureKey(SECURENET_CERTIFICATION_KEY);
		$this->gateway->setRandomOrderId();
		$this->gateway->setTestMode(false);
	}

	public function predefinedTransactionSequence()
	{
		return array(
			array(
				array(
					'id'         => 1,
					'cardType'   => 'visa',
					'cardNumber' => '4444333322221111',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 2,
					'cardType'   => 'visa',
					'cardNumber' => '4005519200000004',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 3,
					'cardType'   => 'visa',
					'cardNumber' => '4275330012345675',
					'amount'     => 12.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 4,
					'cardType'   => 'visa',
					'cardNumber' => '4012000033330026',
					'amount'     => 21.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 5,
					'cardType'   => 'visa',
					'cardNumber' => '4444333322221111',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 1,
				),
			),
			array(
				array(
					'id'         => 6,
					'cardType'   => 'visa',
					'cardNumber' => '4005519200000004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 2,
				),
			),
			array(
				array(
					'id'         => 7,
					'cardType'   => 'visa',
					'cardNumber' => '4275330012345675',
					'amount'     => 12.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 3,
				),
			),
			array(
				array(
					'id'         => 8,
					'cardType'   => 'visa',
					'cardNumber' => '4012000033330026',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 4,
				),
			),
			array(
				array(
					'id'         => 9,
					'cardType'   => 'visa',
					'cardNumber' => '4444333322221111',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 10,
					'cardType'   => 'visa',
					'cardNumber' => '4005519200000004',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 11,
					'cardType'   => 'visa',
					'cardNumber' => '4275330012345675',
					'amount'     => 12.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 12,
					'cardType'   => 'visa',
					'cardNumber' => '4012000033330026',
					'amount'     => 21.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 13,
					'cardType'   => 'visa',
					'cardNumber' => '4012000033330026',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'refund',
					'expected'   => 'isApproved',
					'txnId'      => 12,
				),
			),
			array(
				array(
					'id'         => 14,
					'cardType'   => 'visa',
					'cardNumber' => '4005519200000004',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 15,
					'cardType'   => 'visa',
					'cardNumber' => '4005519200000004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'void',
					'expected'   => 'isApproved',
					'txnId'      => 14,
				),
			),
			array(
				array(
					'id'         => 16,
					'cardType'   => 'visa',
					'cardNumber' => '4012888888881881',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isDeclined',
				),
			),
			array(
				array(
					'id'         => 17,
					'cardType'   => 'visa',
					'cardNumber' => '4012888888881881',
					'amount'     => 11.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isDeclined',
				),
			),
			array(
				array(
					'id'         => 18,
					'cardType'   => 'mastercard',
					'cardNumber' => '5424180279791732',
					'amount'     => 11.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 19,
					'cardType'   => 'mastercard',
					'cardNumber' => '5405010100000016',
					'amount'     => 11.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 20,
					'cardType'   => 'mastercard',
					'cardNumber' => '5149612222222229',
					'amount'     => 12.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 21,
					'cardType'   => 'mastercard',
					'cardNumber' => '5526399000648568',
					'amount'     => 21.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 22,
					'cardType'   => 'mastercard',
					'cardNumber' => '5424180279791732',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 18,
				),
			),
			array(
				array(
					'id'         => 23,
					'cardType'   => 'mastercard',
					'cardNumber' => '5405010100000016',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 19,
				),
			),
			array(
				array(
					'id'         => 24,
					'cardType'   => 'mastercard',
					'cardNumber' => '5149612222222229',
					'amount'     => 12.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 20,
				),
			),
			array(
				array(
					'id'         => 25,
					'cardType'   => 'mastercard',
					'cardNumber' => '5526399000648568',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 21,
				),
			),
			array(
				array(
					'id'         => 26,
					'cardType'   => 'mastercard',
					'cardNumber' => '5424180279791732',
					'amount'     => 11.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 27,
					'cardType'   => 'mastercard',
					'cardNumber' => '5405010100000016',
					'amount'     => 11.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 28,
					'cardType'   => 'mastercard',
					'cardNumber' => '5149612222222229',
					'amount'     => 12.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 29,
					'cardType'   => 'mastercard',
					'cardNumber' => '5526399000648568',
					'amount'     => 21.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 30,
					'cardType'   => 'mastercard',
					'cardNumber' => '5526399000648568',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'refund',
					'expected'   => 'isApproved',
					'txnId'      => 29,
				),
			),
			array(
				array(
					'id'         => 31,
					'cardType'   => 'mastercard',
					'cardNumber' => '5405010100000016',
					'amount'     => 11.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 32,
					'cardType'   => 'mastercard',
					'cardNumber' => '5405010100000016',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'void',
					'expected'   => 'isApproved',
					'txnId'      => 31,
				),
			),
			array(
				array(
					'id'         => 33,
					'cardType'   => 'mastercard',
					'cardNumber' => '5567064000000000',
					'amount'     => 31.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isDeclined',
				),
			),
			array(
				array(
					'id'         => 34,
					'cardType'   => 'mastercard',
					'cardNumber' => '5567064000000000',
					'amount'     => 31.00,
					'cvv2'       => '998',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isDeclined',
				),
			),

			array(
				array(
					'id'         => 35,
					'cardType'   => 'amex',
					'cardNumber' => '373953192351004',
					'amount'     => 11.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 36,
					'cardType'   => 'amex',
					'cardNumber' => '375987654321004',
					'amount'     => 11.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 37,
					'cardType'   => 'amex',
					'cardNumber' => '371449635392376',
					'amount'     => 12.00,
					'cvv2'       => '9999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 38,
					'cardType'   => 'amex',
					'cardNumber' => '341111597241002',
					'amount'     => 21.00,
					'cvv2'       => '9999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 39,
					'cardType'   => 'amex',
					'cardNumber' => '373953192351004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 35,
				),
			),
			array(
				array(
					'id'         => 40,
					'cardType'   => 'amex',
					'cardNumber' => '375987654321004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 36,
				),
			),
			array(
				array(
					'id'         => 41,
					'cardType'   => 'amex',
					'cardNumber' => '371449635392376',
					'amount'     => 12.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 37,
				),
			),
			array(
				array(
					'id'         => 42,
					'cardType'   => 'amex',
					'cardNumber' => '341111597241002',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 38,
				),
			),
			array(
				array(
					'id'         => 43,
					'cardType'   => 'amex',
					'cardNumber' => '373953192351004',
					'amount'     => 11.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 44,
					'cardType'   => 'amex',
					'cardNumber' => '373953192351004',
					'amount'     => 11.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 45,
					'cardType'   => 'amex',
					'cardNumber' => '371449635392376',
					'amount'     => 12.00,
					'cvv2'       => '9999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 46,
					'cardType'   => 'amex',
					'cardNumber' => '341111597241002',
					'amount'     => 21.00,
					'cvv2'       => '9999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 47,
					'cardType'   => 'amex',
					'cardNumber' => '341111597241002',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'refund',
					'expected'   => 'isApproved',
					'txnId'      => 46,
				),
			),
			array(
				array(
					'id'         => 48,
					'cardType'   => 'amex',
					'cardNumber' => '375987654321004',
					'amount'     => 11.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 49,
					'cardType'   => 'amex',
					'cardNumber' => '375987654321004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'void',
					'expected'   => 'isApproved',
					'txnId'      => 48,
				),
			),
			array(
				array(
					'id'         => 50,
					'cardType'   => 'amex',
					'cardNumber' => '371449635398431',
					'amount'     => 31.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isDeclined',
				),
			),
			array(
				array(
					'id'         => 51,
					'cardType'   => 'amex',
					'cardNumber' => '371449635398431',
					'amount'     => 31.00,
					'cvv2'       => '9997',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isDeclined',
				),
			),

			array(
				array(
					'id'         => 52,
					'cardType'   => 'discover',
					'cardNumber' => '6011000000000012',
					'amount'     => 11.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 53,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 11.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 54,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991001201',
					'amount'     => 12.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 55,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 21.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 56,
					'cardType'   => 'discover',
					'cardNumber' => '6011000000000012',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 52,
				),
			),
			array(
				array(
					'id'         => 57,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 11.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 53,
				),
			),
			array(
				array(
					'id'         => 58,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991001201',
					'amount'     => 12.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 54,
				),
			),
			array(
				array(
					'id'         => 59,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'capture',
					'expected'   => 'isApproved',
					'txnId'      => 55,
				),
			),
			array(
				array(
					'id'         => 60,
					'cardType'   => 'discover',
					'cardNumber' => '6011000000000012',
					'amount'     => 11.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 61,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 11.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 62,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991001201',
					'amount'     => 12.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 63,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 21.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 64,
					'cardType'   => 'discover',
					'cardNumber' => '6011905000000004',
					'amount'     => 21.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'refund',
					'expected'   => 'isApproved',
					'txnId'      => 63,
				),
			),
			array(
				array(
					'id'         => 65,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991001201',
					'amount'     => 12.00,
					'cvv2'       => '999',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20850',
					'process'    => 'authorize',
					'expected'   => 'isApproved',
				),
			),
			array(
				array(
					'id'         => 66,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991001201',
					'amount'     => 12.00,
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'process'    => 'void',
					'expected'   => 'isApproved',
					'txnId'      => 65,
				),
			),
			array(
				array(
					'id'         => 67,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991300009',
					'amount'     => 31.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorizeCapture',
					'expected'   => 'isDeclined',
				),
			),
			array(
				array(
					'id'         => 68,
					'cardType'   => 'discover',
					'cardNumber' => '6011000991300009',
					'amount'     => 31.00,
					'cvv2'       => '996',
					'industry'   => 'P',
					'expiryMM'   => '04',
					'expiryYY'   => '14',
					'zip'        => '20704',
					'process'    => 'authorize',
					'expected'   => 'isDeclined',
				),
			),
		);
	}

	/**
	 * @dataProvider predefinedTransactionSequence
	 */
	public function testPredefinedTransaction($txn)
	{
		$this->gateway->setAmount($txn['amount']);
		$this->gateway->setCardNumber($txn['cardNumber']);
		$this->gateway->setCardExpiry($txn['expiryMM'], $txn['expiryYY']);
		if (isset($txn['cvv2'])) $this->gateway->setCardCvvc($txn['cvv2']);
		$this->gateway->setIndustryIndicator($txn['industry']);
		if (isset($txn['zip'])) $this->gateway->setCustomerZip($txn['zip']);

		if (isset($txn['txnId'])) {
			$transaction = $this->gateway->{$txn['process']}(self::getTransactionId($txn['txnId']));
		} else {
			$transaction = $this->gateway->{$txn['process']}();
		}

		$this->assertTrue($transaction->{$txn['expected']}());

		self::recordTransactionId($txn['id'], $transaction->getTransactionId());

		$this->printTransactionLine($txn, $transaction->getTransactionId());
	}

	/**
	 * Spit out the relevant information to stdout in order to collect and send to SecureNet
	 */
	private function printTransactionLine($txn, $transactionId)
	{
		echo '[' . implode(':', array($txn['cardType'], $txn['cardNumber'], $txn['process'], $txn['expected'])) . '][' . $txn['id'] . ']:' . $transactionId . PHP_EOL;
	}

}