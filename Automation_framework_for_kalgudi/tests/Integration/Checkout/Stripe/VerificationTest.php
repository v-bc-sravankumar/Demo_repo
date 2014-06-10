<?php
require_once('TransactionSequence.php');
/**
 * @group remote
 */
class Unit_Checkout_Stripe_VerificationTest extends Unit_Checkout_Stripe_TransactionSequence
{
	/**
	 * Stripe testing
	 *
	 * https://stripe.com/docs/testing
	 *
	 * @return array
	 */
	public function predefinedTransactionSequence()
	{
		$expiryYear = date('y');
		// Stripe testing
		return array(
			array(
				array(
					'id'       => 1,
					'params'   => array(
						'card'     => array(
							'number'      => '4242424242424242',
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),

			array(
				array(
					'id'       => 2,
					'params'   => array(
						'card'     => array(
							'number'      => '5555555555554444', // mastercard
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 3,
					'params'   => array(
						'card'     => array(
							'number'      => '378282246310005', // Amex
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 4,
					'params'   => array(
						'card'     => array(
							'number'      => '6011111111111117', // Discover
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 5,
					'params'   => array(
						'card'     => array(
							'number'      => '30569309025904', // Diners
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 6,
					'params'   => array(
						'card'     => array(
							'number'      => '3530111333300000', // JCB
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => null,
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 7,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000010', // address_line1_check and address_zip_check will both fail.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => 'fail',
							'address_zip_check' => 'fail',
						),
					),
				),
			),
			array(
				array(
					'id'       => 8,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000028', // address_line1_check will fail.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => 'fail',
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 9,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000036', // address_zip_check will fail.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'pass',
							'address_line1_check' => 'pass',
							'address_zip_check' => 'fail',
						),
					),
				),
			),
			array(
				array(
					'id'       => 10,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000101', // cvc_check will fail.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
						'card' => array(
							'cvc_check' => 'fail',
							'address_line1_check' => 'pass',
							'address_zip_check' => 'pass',
						),
					),
				),
			),
			array(
				array(
					'id'       => 11,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000002', // Charges with this card will always be declined with a card_declined code.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
					'expected' => array()
				),
			),
			array(
				array(
					'id'       => 12,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000127', // Will be declined with an incorrect_cvc code.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
					'expected' => array(),
				),
			),
			array(
				array(
					'id'       => 13,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000069', // Will be declined with an expired_card code.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
					'expected' => array(),
				),
			),
			array(
				array(
					'id'       => 14,
					'params'   => array(
						'card'     => array(
							'number'      => '4000000000000069', // Will be declined with a processing_error code.
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
					'expected' => array(),
				),
			),
			array(
				array(
					'id'       => 15,
					'params'   => '',
					'process'  => 'capture',
					'originalTxnId' => 1,
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => false,
						'amount'    => 2000,
					),
				),
			),
			array(
				array(
					'id'       => 16,
					'params'   => array(
						'id' => '',
						'amount' => 2000,
					),
					'process'  => 'refund',
					'originalTxnId' => 1,
					'exception' => null,
					'expected' => array(
						'paid'      => true,
						'refunded'  => true,
						'amount'    => 2000,
					),
				),
			),
			array(
				array(
					'id'       => 20,
					'api_key' => 'invalidkey',  // invalid api key
					'params'   => array(
						'card'     => array(
							'number'      => '4242424242424242',
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\GatewayException',
				),
			),

			array(
				array(
					'id'       => 21,
					'params'   => array(
						'card'     => array(
							'number'      => '5555555555554444',
							'exp_month'   => '13',  // invalid expiry year
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
							'address_line1' => '10 Some St',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
				),
			),
			array(
				array(
					'id'       => 22,
					'params'   => array(
						'card'     => array(
							'number'      => '5555555555554444',
							'exp_month'   => '13',
							'exp_year'    => '10',  // expired card
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
				),
			),
			array(
				array(
					'id'       => 23,
					'params'   => array(
						'card'     => array(
							'number'      => '1111111111111111', // invalid credit card number
							'exp_month'   => '12',
							'exp_year'    => $expiryYear,
							'name'        => 'John Doe',
							'cvc'         => '999',
							'address_zip' => '20850',
						),
						'amount'   => 2000,
						'currency' => 'USD',
						'capture'  => false,
					),
					'process'  => 'charge',
					'exception' => 'Services\Payments\Gateway\TransactionError',
				),
			),
		);
	}
}