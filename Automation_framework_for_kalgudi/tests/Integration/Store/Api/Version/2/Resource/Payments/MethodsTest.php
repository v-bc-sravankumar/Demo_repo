<?php

class Store_Api_Version_2_Resource_Payments_MethodsTest extends Interspire_IntegrationTest {

	/**
	 * @var Store_Api_Version_2_Resource_Payments_Methods
	 */
	protected $resource;

	public function setUp()
	{
		$this->resource = new Store_Api_Version_2_Resource_Payments_Methods();
		$this->fixtures->loadData('payment_methods');
	}

	public function tearDown()
	{
		Store_Config::revert('CheckoutMethods');
	}

	public function testGetEnabledPaymentMethods()
	{
		Store_Config::override('CheckoutMethods', 'checkout_cod,checkout_instore');

		$result = $this->resource->getAction(new Interspire_Request())->getData();

		$this->assertCount(2, $result);

		$this->assertContains(array(
			'code' => 'cod',
			'name' => 'Cash on Delivery',
			'test_mode' => false
		), $result);

		$this->assertContains(array(
			'code' => 'instore',
			'name' => 'Pay in Store',
			'test_mode' => false
		), $result);
	}

	public function testGetPaymentMethodWithTestModeEnabled()
	{
		Store_Config::override('CheckoutMethods', 'checkout_securenet');

		$result = $this->resource->getAction(new Interspire_Request())->getData();

		$this->assertCount(1, $result);

		$this->assertContains(array(
			'code' => 'securenet',
			'name' => 'SecureNet',
			'test_mode' => true
		), $result);
	}
}
