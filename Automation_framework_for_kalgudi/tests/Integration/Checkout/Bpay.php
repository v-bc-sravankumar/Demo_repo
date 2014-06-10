<?php
/**
 * Interspire Shopping Cart.
 * Copyright � 2009 Interspire Pty. Ltd., All Rights Reserved
 *
 * Interspire Shopping Cart is NOT free software.
 * This file may not be redistributed in whole or significant part.
 *
 * Website: http://www.interspire.com/shoppingcart/
 *
 * $Id$
 */

class Unit_Checkout_Bpay extends Interspire_IntegrationTest
{

	// Test check digit calculation for bpay customer reference number.
	public function testGenerateReferenceNumber ()
	{
		require_once BUILD_ROOT.'/modules/checkout/bpay/module.bpay.php';
		$bpay = new CHECKOUT_BPAY();

		// Test padding with prefix.
		$res = $bpay->GenerateReferenceNumber(7, 10, 5);
		$this->assertEquals(5000000074, $res);

		$res = $bpay->GenerateReferenceNumber(2951);
		$this->assertEquals(29512, $res);

		// Example from BPAY Q&A..
		$res = $bpay->GenerateReferenceNumber(272573);
		$this->assertEquals(2725737, $res);

		// Test alphabet prefix, should ignore.
		$res = $bpay->GenerateReferenceNumber(272573, '', 'a');
		$this->assertEquals(2725737, $res);

		// Test leading zeros.
		$res = (string) $bpay->GenerateReferenceNumber(272573, 10);
		$this->assertEquals('0002725737', $res);
	}
}
