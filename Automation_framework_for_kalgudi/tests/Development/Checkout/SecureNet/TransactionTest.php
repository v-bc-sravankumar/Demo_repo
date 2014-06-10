<?php
use Services\Payments\SecureNet\SecureNet, Services\Payments\SecureNet\Transaction;
use Services\Payments\Gateway\TransactionError, Services\Payments\Gateway\TransactionFault;

class Checkout_SecureNet_TransactionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException Services\Payments\Gateway\TransactionFault
	 */
	public function testTransactionFaultWithBadRequest()
	{
		$gateway = new SecureNet();
		$gateway->setSecureNetId('badid');
		$gateway->setSecureKey('#key');
		$gateway->authorize();
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionFault
	 */
	public function testTransactionFaultWithBadResponse()
	{
		$transaction = new Transaction("#response");
	}

	public function testAuthorizeTransactionApproved()
	{
		$gateway = new SecureNet();
		$gateway->setSecureNetId('7001174');
		$gateway->setSecureKey('ybMkAvWwHWxA');
		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('4444333322221111');
		$gateway->setCardExpiry('04', '13');
		$gateway->setCardCvvc('999');
		$gateway->setRandomOrderId();
		$gateway->setTestMode(true);

		$transaction = $gateway->authorize();
		$this->assertTrue($transaction->isApproved());
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionError
	 */
	public function testAuthorizeTransactionDeclinedWithBadCardExpiry()
	{
		$gateway = new SecureNet();
		$gateway->setSecureNetId('7001174');
		$gateway->setSecureKey('ybMkAvWwHWxA');
		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('4444333322221111');
		$gateway->setCardExpiry('04', '11');
		$gateway->setCardCvvc('999');
		$gateway->setRandomOrderId();
		$gateway->setTestMode(true);

		$transaction = $gateway->authorize();
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionError
	 */
	public function testAuthorizeTransactionDeclinedWithBadCardNumber()
	{
		$gateway = new SecureNet();
		$gateway->setSecureNetId('7001174');
		$gateway->setSecureKey('ybMkAvWwHWxA');
		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('1111111111111111');
		$gateway->setCardExpiry('04', '11');
		$gateway->setCardCvvc('999');
		$gateway->setRandomOrderId();
		$gateway->setTestMode(true);

		$transaction = $gateway->authorize();
	}
}