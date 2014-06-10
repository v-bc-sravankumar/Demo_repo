<?php

namespace Integration\Lib\Store\Api\Version2\Resource\Orders;

class ShippingAddresses extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		\Store::getStoreDb()->Query('TRUNCATE `order_shipping`');
		\Store::getStoreDb()->Query('TRUNCATE `order_addresses`');
	}

	/**
	 * Dummy address to be used by orders
	 *
	 * @return array
	 */
	private function _getDummyAddress()
	{
		return array (
			'first_name' => 'Test',
			'last_name' => 'Engineering',
			'company' => 'BigCommerce',
			'street_1' => 'Level 6, 1-3 Smail St',
			'street_2' => '',
			'city' => 'Ultimo',
			'state' => 'New South Wales',
			'zip' => '2007',
			'country' => 'Australia',
			'country_iso2' => 'AU',
			'phone' => '90066009',
			'email' => 'test@bigcommerce.com',
		);
	}

	/**
	 * @return \Store_Api_Version_2_Resource_Orders_Shippingaddresses
	 */
	private function _getResource()
	{
		return new \Store_Api_Version_2_Resource_Orders_Shippingaddresses();
	}

	/**
	 * Test a simple get
	 */
	public function testGetSimple()
	{

		// Create a new order via the API
		$input = array(
			'customer_id' => 0,
			'date_created' => 'Thu, 04 Oct 2012 03:24:40 +0000',
			'billing_address' => $this->_getDummyAddress(),
			'products' => array (
				array (
					'product_id' => 27,
					'quantity' => 2,
				),
			)
		);

		$resource = new \Store_Api_Version_2_Resource_Orders();

		$body = \Interspire_Json::encode($input);

		$res = $resource->postAction(new \Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json',
		), $body));

		$data = $res->getData(true);

		$this->assertNotEmpty($data['id']);

		/** @var $shippingAddressesRoute Store_RequestRoute_StoreApi */
		$shippingAddressesRoute = $data['shipping_addresses'];

		$request = new \Interspire_Request();
		foreach ($shippingAddressesRoute->getParameters() as $key => $value) {
			// always put parameters store in the route into the request object as user parameters
			$request->setUserParam($key, $value);
		}
		$addressResponse = $this->_getResource()->getAction($request)->getData(true);

		// check correct length
		$this->assertEquals(1, count($addressResponse));

		$firstAddress = current($addressResponse);

		// check has ID
		$this->assertNotEmpty($firstAddress['id']);

		// check address exist
		$address = \Orders\Address::find((int) $firstAddress['id'])->first();
		$this->assertNotEmpty($address);

	}

}
