<?php
require_once('TransactionSequence.php');
/**
 * @group disabled
 */
class Unit_Checkout_HPS_CertificationTest extends Unit_Checkout_HPS_TransactionSequence
{
	public function predefinedTransactionSequence()
	{
		$testCards = array(
			'visa' => array(
				'cardNumber' => '4012002000060016',
				'expiryMM' => '12',
				'expiryYY' => '25',
				'cvv2' => '123',
			),
			'mastercard' => array(
				'cardNumber' => '5473500000000014',
				'expiryMM' => '12',
				'expiryYY' => '25',
				'cvv2' => '123',
			),
			'discover' => array(
				'cardNumber' => '6011000990156527',
				'expiryMM' => '12',
				'expiryYY' => '25',
				'cvv2' => '123',
			),
			'amex' => array(
				'cardNumber' => '372700699251018',
				'expiryMM' => '12',
				'expiryYY' => '25',
				'cvv2' => '1234',
			),
			'jcb' => array(
				'cardNumber' => '3566007770007321',
				'expiryMM' => '12',
				'expiryYY' => '25',
				'cvv2' => '123',
			),
		);

		$date = new DateTime();
		// Time must match HPS servers for settling transactions which uses US Central Time Zone (CDT)
		$date->setTimezone(new DateTimeZone('America/Chicago'));
		$shippingDate = $date->format('Y-m-d');

		$transactions = array(
			/*
			array(
				'process' => 'setCloseBatch',
			),
			*/

			array(
				'id' => 1,
				'cardType' => 'visa',
				'amount' => 17.01,
				'address' => '6860 Dallas Pkwy',
				'zip' => '75024',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeCapture',
			),
			array(
				'id' => 2,
				'cardType' => 'mastercard',
				'amount' => 17.02,
				'address' => '6860',
				'zip' => '75024',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeCapture',
			),
			array(
				'id' => 3,
				'cardType' => 'discover',
				'amount' => 17.03,
				'address' => '6860',
				'zip' => '750241234',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeCapture',
			),
			array(
				'id' => 4,
				'cardType' => 'amex',
				'amount' => 17.04,
				'address' => '6860 Dallas Pkwy',
				'zip' => '75024',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeCapture',
			),
			array(
				'id' => 5,
				'cardType' => 'jcb',
				'amount' => 17.05,
				'address' => '6860 Dallas Pkwy',
				'zip' => '750241234',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeCapture',
			),

			array(
				'id' => 6,
				'cardType' => 'visa',
				'amount' => 17.06,
				'address' => '6860 Dallas Pkwy',
				'zip' => '75024',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeOnly',
			),
			array(
				'id' => 7,
				'cardType' => 'mastercard',
				'amount' => 17.07,
				'address' => '6860 Dallas Pkwy',
				'zip' => '750241234',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeOnly',
			),
			array(
				'id' => 8,
				'cardType' => 'discover',
				'amount' => 17.08,
				'address' => '6860',
				'zip' => '75024',
				'shippingDate' => $shippingDate,
				'process' => 'setAuthorizeOnly',
			),

			array(
				'process' => 'setCapturePriorPayment',
				'txnId' => 6,
			),
			array(
				'process' => 'setCapturePriorPayment',
				'txnId' => 7,
			),

			array(
				'id' => 9,
				'amount' => 15.15,
				'cardType' => 'mastercard',
				'shippingDate' => $shippingDate,
				'process' => 'setRefundTransaction',
			),

			array(
				'amount' => 17.01,
				'process' => 'setReverseTransaction',
				'txnId' => 1,
			),
			array(
				'process' => 'setCloseBatch',
			),
		);

		foreach($transactions as &$transaction) {
			if(isset($transaction['cardType'])) {
				$transaction += $testCards[$transaction['cardType']];
			}

			$transaction = array(
				$transaction,
			);
		}

		return $transactions;
	}
}