<?php

use PHPUnit\TestCases\TransactionalIntegrationTestCase;

class Integration_Lib_Store_Api_Version_2_Resource_Orders_Shipments extends TransactionalIntegrationTestCase {

	const ZIP = '0200';
	const PHONE = '0255500000';
	const TRACKING_NO = '000123456789';
	const SHIPPING_METHOD = 'foo_bar';

	/** @var Store_Api_Version_2_Resource_Orders_Shipments */
	private $ordersShipmentResource;
	/** @var Store_Api_Version_2_Resource_Orders */
	private $ordersResource;

	public function setUp()
	{
		parent::setUp();
		$this->ordersShipmentResource = new Store_Api_Version_2_Resource_Orders_Shipments();
		$this->ordersResource = new Store_Api_Version_2_Resource_Orders();
	}

	protected function createOrder()
	{
		$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<order>
	<customer_id>0</customer_id>
	<billing_address>
		<first_name>Test</first_name>
		<last_name>Customer</last_name>
		<company>Bigcommerce</company>
		<street_1>Level 6, 1-3 Smail Street</street_1>
		<street_2/>
		<city>Ultimo</city>
		<state>Australian Capital Territory</state>
		<zip>%s</zip>
		<country>Australia</country>
		<country_iso2>AU</country_iso2>
		<phone>%s</phone>
		<email>test@bigcommerce.com</email>
	</billing_address>
	<external_source>POS</external_source>
	<status_id>1</status_id>
	<products>
		<product>
			<product_id>27</product_id>
			<quantity>1</quantity>
		</product>
	</products>
</order>
XML;
		$xml = sprintf($xml, self::ZIP, self::PHONE);
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/xml'), $xml);
		return $this->ordersResource->postAction($request)->getData(true);
	}

	protected function createOrderShipment($orderId, $addressId, $productId, $shippingMethod)
	{
		$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<shipment>
	<tracking_number>%s</tracking_number>
	<order_address_id>%d</order_address_id>
	<shipping_method>%s</shipping_method>
	<items>
		<item>
			<order_product_id>%d</order_product_id>
			<quantity>1</quantity>
		</item>
	</items>
</shipment>
XML;
		$xml = sprintf($xml, self::TRACKING_NO, $addressId, $shippingMethod, $productId);
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/xml'), $xml);
		$request->setUserParam('orders', $orderId);
		return $this->ordersShipmentResource->postAction($request)->getData(true);
	}

	protected function getOrderProducts($orderId)
	{
		$orderProducts = new Store_Api_Version_2_Resource_Orders_Products();
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get'));
		$request->setUserParam('orders', $orderId);
		return $orderProducts->getAction($request)->getData(true);
	}

	/**
	 * BIG-4870
	 * Leading zeros were being stripped from XML POST requests in 'zip' and 'tracking_number' fields
	 * @transactional
	 * {@link https://jira.bigcommerce.com/browse/BIG-4870}
	 */
	public function testLeadingZerosRemainForXMLRequest()
	{
		$orderResponse = $this->createOrder();

		$this->assertNotEmpty($orderResponse);
		$this->assertTrue(is_string($orderResponse['billing_address']['zip']), 'Expected zip to be of type string');
		$this->assertSame(self::ZIP, $orderResponse['billing_address']['zip']);

		$this->assertTrue(is_string($orderResponse['billing_address']['phone']), 'Expected phone to be of type string');
		$this->assertSame(self::PHONE, $orderResponse['billing_address']['phone']);

		$orderProducts = $this->getOrderProducts($orderResponse['id']);
		$this->assertArrayIsNotEmpty($orderProducts);

		$orderShipmentResponse = $this->createOrderShipment(
			$orderResponse['id'],
			$orderProducts[0]['order_address_id'],
			$orderProducts[0]['id'],
			self::SHIPPING_METHOD
		);

		$this->assertNotEmpty($orderShipmentResponse);
		$this->assertTrue(is_string($orderShipmentResponse['tracking_number']), 'Expected tracking number to be of type string');
		$this->assertSame(self::TRACKING_NO, $orderShipmentResponse['tracking_number']);
		$this->assertEquals(self::SHIPPING_METHOD, $orderShipmentResponse['shipping_method']);
	}

}