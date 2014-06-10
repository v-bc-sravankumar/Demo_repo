<?php
require_once('TransactionSequence.php');
/**
 * @group remote
 */
class Unit_Checkout_HPS_VerificationTest extends Unit_Checkout_HPS_TransactionSequence
{
	public function predefinedTransactionSequence()
	{
		$expiryYear = substr(date('Y'), 0, -2) + 1;

		return array(
			array(
				array(
					'id'            => 1,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.01,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'B',         //  Address match, postal code not verified due to incompatible formats (international address)
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),

			array(
				array(
					'id'            => 2,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.02,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'C',         //  Address and postal code not verified due to incompatible formats (international address)
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 3,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.03,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'D',         //  Street address and postal code match (international address)
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 4,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.05,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'I',         //  Address information not verified for International transaction
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 5,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.06,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'M',         //  Street address and postal code matches
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 6,
					'cardType'      => 'visa',
					'cardNumber'    => '4444333322221111',
					'amount'        => 91.07,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'P',         //  Postal code match. Street address not verified due to incompatible formats (international address)
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),

			array(
				array(
					'id'            => 7,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.01,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'A',         //  Address matches, zip code does not
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),


			array(
				array(
					'id'            => 8,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.02,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'N',         //  Neither address or zip code match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 9,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.03,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'R',         //  Retry - system unable to respond
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 10,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.04,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'S',         //  ABS not supported
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 11,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.05,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'U',         //  No data from Issuer/auth system
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 12,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.06,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'W',         //  9 digit zip code match, address does not match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 13,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.07,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'X',         //  9 digit zip and address match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 14,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.08,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Y',         //  5 digit zip and address match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 15,
					'cardType'      => 'mastercard',
					'cardNumber'    => '5149612222222229',
					'amount'        => 90.09,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Z',         //  5 digit zip code match, address does not match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),


			array(
				array(
					'id'            => 16,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.01,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'A',         //  Address matches, zip code does not
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 17,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.02,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'N',         //  Neither address or zip code match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 18,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.03,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'R',         //  Retry - system unable to respond
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 19,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.04,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'S',         //  ABS not supported
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 20,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.05,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'U',         //  No data from Issuer/auth system
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 21,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.06,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'W',         //  9 digit zip code match, address does not match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 22,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.07,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'X',         //  9 digit zip and address match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 23,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.08,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Y',         //  5 digit zip and address match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 24,
					'cardType'   	=> 'amex',
					'cardNumber' 	=> '373953192351004',
					'amount'        => 90.09,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Z',         //  5 digit zip code match, address does not match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),


			array(
				array(
					'id'            => 25,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.01,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'A', //  Address matches, zip code does not
					'avsVerified'   => array(
						'IssuerApproved'     => true,
						'ZipMatch'           => false,
						'AddressMatch'       => true,
						'AddressOrZipMatch'  => true,
						'AddressAndZipMatch' => false,
						'Disable'            => true,
					),
				),
			),

			array(
				array(
					'id'            => 26,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.02,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'N',         //  Neither address or zip code match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 27,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.03,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'R',         //  Retry - system unable to respond
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 28,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.05,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'U',         //  ABS not supported | No data from Issuer/auth system
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => false,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => false,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 29,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.06,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Y',         //  5 or 9 digit zip and address match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => true,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => true,
						'Disable'               => true,
					),
				),
			),
			array(
				array(
					'id'            => 30,
					'cardType'   	=> 'discover',
					'cardNumber' 	=> '6011000000000012',
					'amount'        => 91.07,
					'cvv2'          => '999',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'avsCode'       => 'Z',         //  5 digit zip code match, address does not match
					'avsVerified'    => array(
						'IssuerApproved'        => true,
						'ZipMatch'              => true,
						'AddressMatch'          => false,
						'AddressOrZipMatch'     => true,
						'AddressAndZipMatch'    => false,
						'Disable'               => true,
					),
				),
			),
			// CVV checks
			array(
				array(
					'id'            => 31,
					'cardType'   	=> 'visa',
					'cardNumber' 	=> '4444333322221111',
					'amount'        => 96.01,
					'cvv2'          => '123',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'cvvCode'       => 'M',         // CVV Match
					'cvvVerified'   => array(
						'NO'        => true,
						'YES'       => true,
					),
				),
			),
			array(
				array(
					'id'            => 32,
					'cardType'   	=> 'visa',
					'cardNumber' 	=> '4444333322221111',
					'amount'        => 96.02,
					'cvv2'          => '123',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'cvvCode'       => 'N',         // CVV No Match
					'cvvVerified'   => array(
						'NO'        => true,
						'YES'       => false,
					),
				),
			),
			array(
				array(
					'id'            => 33,
					'cardType'   	=> 'visa',
					'cardNumber' 	=> '4444333322221111',
					'amount'        => 96.03,
					'cvv2'          => '123',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'cvvCode'       => 'P',         // Not Processed
					'cvvVerified'   => array(
						'NO'        => true,
						'YES'       => true,
					),
				),
			),
			array(
				array(
					'id'            => 34,
					'cardType'   	=> 'visa',
					'cardNumber' 	=> '4444333322221111',
					'amount'        => 96.04,
					'cvv2'          => '123',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'cvvCode'       => 'S',         // Should Have Been Present
					'cvvVerified'   => array(
						'NO'        => true,
						'YES'       => false,
					),
				),
			),
			array(
				array(
					'id'            => 35,
					'cardType'   	=> 'visa',
					'cardNumber' 	=> '4444333322221111',
					'amount'        => 96.05,
					'cvv2'          => '123',
					'expiryMM'      => '04',
					'expiryYY'      => $expiryYear,
					'zip'           => '20850',
					'process'       => 'setAuthorizeOnly',
					'expected'      => 'isApproved',
					'cvvCode'       => 'U',         // Issuer is not certified and/or has not provided VISA encryption keys.
					'cvvVerified'   => array(
						'NO'        => true,
						'YES'       => false,
					),
				),
			),
		);
	}
}