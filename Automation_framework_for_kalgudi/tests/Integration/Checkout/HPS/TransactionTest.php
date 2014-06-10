<?php
use Services\Payments\HPS\Hps;
use Services\Payments\HPS\Transaction;
use Services\Payments\Gateway\TransactionError, Services\Payments\Gateway\TransactionFault;

require_once('Common.php');
/**
 * @group remote
 */
class Unit_Checkout_HPS_TransactionTest extends Unit_Checkout_HPS_Common
{

	/**
	 * @expectedException Services\Payments\Gateway\TransactionFault
	 */
	public function testTransactionFaultWithBadRequest()
	{
		$gateway = new Hps();
		$gateway->setUserId('badid');
		$gateway->setPassword('#key');
		$gateway->authorize();
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionFault
	 */
	public function testTransactionFaultWithBadResponse()
	{
		new Transaction("#response");
	}

	public function testAuthorizeTransactionApproved()
	{
		$gateway = new Hps();
		$gateway->setAuthorizeOnly();
		$this->loadTestCredentials($gateway);
		$gateway->setTestMode(true);
		$gateway->useLiveGateway(false);
		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('4444333322221111');
		$gateway->setCardExpiry('04', '13');
		$gateway->setCardCvvc('999');
		//$gateway->setRandomOrderId();

		$transaction = $gateway->processTransaction();
		$this->assertTrue($transaction->isApproved());
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionError
	 */
	public function testAuthorizeTransactionDeclinedWithBadCardExpiry()
	{
		$gateway = new Hps();
		$gateway->setAuthorizeOnly();
		$this->loadTestCredentials($gateway);
		$gateway->setTestMode(true);
		$gateway->useLiveGateway(false);
		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('4444333322221111');
		$gateway->setCardExpiry('04', '11');
		$gateway->setCardCvvc('999');
		//$gateway->setRandomOrderId();

		$gateway->processTransaction();
	}

	/**
	 * @expectedException Services\Payments\Gateway\TransactionError
	 */
	public function testAuthorizeTransactionDeclinedWithBadCardNumber()
	{
		$gateway = new Hps();
		$gateway->setAuthorizeOnly();
		$this->loadTestCredentials($gateway);
		$gateway->setTestMode(true);
		$gateway->useLiveGateway(false);

		$gateway->setAmount(29.99);
		$gateway->setCustomerZip('90210');
		$gateway->setCardNumber('1111111111111111');
		$gateway->setCardExpiry('04', '11');
		$gateway->setCardCvvc('999');
		//$gateway->setRandomOrderId();

		$gateway->processTransaction();
	}
}