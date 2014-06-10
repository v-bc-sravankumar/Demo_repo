<?php

class Unit_Orders_Mapping extends PHPUnit_Framework_TestCase
{

	static $orderId = 1042;

	protected static function getOriginalOrderData()
	{

		$actual['orderid'] = self::$orderId;
		$actual['ordtoken'] = "69cb66674abc74452ef9e3b5bb5df1ff";
		$actual['ordcustid'] = "226";
		$actual['orddate'] = "1355108990";
		$actual['ordlastmodified'] = "1355111093";
		$actual['ebay_order_id'] = "0";
		$actual['subtotal_ex_tax'] = "100.0000";
		$actual['subtotal_inc_tax'] = "100.0000";
		$actual['subtotal_tax'] = "0.0000";
		$actual['total_tax'] = "0.0000";
		$actual['base_shipping_cost'] = "0.0000";
		$actual['shipping_cost_ex_tax'] = "0.0000";
		$actual['shipping_cost_inc_tax'] = "0.0000";
		$actual['shipping_cost_tax'] = "0.0000";
		$actual['shipping_cost_tax_class_id'] = "2.0000";
		$actual['base_handling_cost'] = "0.0000";
		$actual['handling_cost_ex_tax'] = "0.0000";
		$actual['handling_cost_inc_tax'] = "0.0000";
		$actual['handling_cost_tax'] = "0.0000";
		$actual['handling_cost_tax_class_id'] = "2.0000";
		$actual['base_wrapping_cost'] = "0.0000";
		$actual['wrapping_cost_inc_tax'] = "0.0000";
		$actual['wrapping_cost_ex_tax'] = "0.0000";
		$actual['wrapping_cost_tax'] = "0.0000";
		$actual['wrapping_cost_tax_class_id'] = "3.0000";
		$actual['total_ex_tax'] = "100.0000";
		$actual['total_inc_tax'] = "100.0000";
		$actual['ordstatus'] = "10";
		$actual['ordtotalqty'] = "1";
		$actual['ordtotalshipped'] = "0";
		$actual['orderpaymentmethod'] = "Cheque";
		$actual['orderpaymentmodule'] = "checkout_cheque";
		$actual['ordpayproviderid'] = "";
		$actual['ordpaymentstatus'] = "";
		$actual['ordrefundedamount'] = "0.0000";
		$actual['ordbillfirstname'] = "Tim";
		$actual['ordbilllastname'] = "Massey";
		$actual['ordbillcompany'] = "";
		$actual['ordbillstreet1'] = "12 Address Street";
		$actual['ordbillstreet2'] = "";
		$actual['ordbillsuburb'] = "Sydney";
		$actual['ordbillstate'] = "New South Wales";
		$actual['ordbillzip'] = "2043";
		$actual['ordbillcountry'] = "Australia";
		$actual['ordbillcountrycode'] = "AU";
		$actual['ordbillcountryid'] = "13";
		$actual['ordbillstateid'] = "209";
		$actual['ordbillphone'] = "0430190772";
		$actual['ordbillemail'] = "tim.massey@basecode.net";
		$actual['ordisdigital'] = "1";
		$actual['orddateshipped'] = "1355109034";
		$actual['ordstorecreditamount'] = "0.0000";
		$actual['ordgiftcertificateamount'] = "0.0000";
		$actual['ordinventoryupdated'] = "1";
		$actual['ordonlygiftcerts'] = "1";
		$actual['extrainfo'] = "N;";
		$actual['ordipaddress'] = "127.0.0.1";
		$actual['ordgeoipcountry'] = "";
		$actual['ordgeoipcountrycode'] = "";
		$actual['ordcurrencyid'] = "1";
		$actual['orddefaultcurrencyid'] = "1";
		$actual['ordcurrencyexchangerate'] = "1.0000000000";
		$actual['ordnotes'] = "I am a staff note";
		$actual['ordcustmessage'] = "I am a customers message";
		$actual['ordvendorid'] = "0";
		$actual['ordformsessionid'] = "151";
		$actual['orddiscountamount'] = "0.0000";
		$actual['shipping_address_count'] = "0";
		$actual['coupon_discount'] = "0.0000";
		$actual['deleted'] = "0";
		$actual['order_source'] = "www";
		return $actual;

	}

	protected static function getMappedOrderData()
	{
		$expected['id'] = '';
		$expected['customer_id'] = '';
		$expected['date_created'] = '';
		$expected['date_modified'] = '';
		$expected['date_shipped'] = '';
		$expected['status_id'] = '';
		$expected['subtotal_ex_tax'] = '';
		$expected['subtotal_inc_tax'] = '';
		$expected['subtotal_tax'] = '';
		$expected['base_shipping_cost'] = '';
		$expected['shipping_cost_ex_tax'] = '';
		$expected['shipping_cost_inc_tax'] = '';
		$expected['shipping_cost_tax'] = '';
		$expected['shipping_cost_tax_class_id'] = '';
		$expected['base_handling_cost'] = '';
		$expected['handling_cost_ex_tax'] = '';
		$expected['handling_cost_inc_tax'] = '';
		$expected['handling_cost_tax'] = '';
		$expected['handling_cost_tax_class_id'] = '';
		$expected['base_wrapping_cost'] = '';
		$expected['wrapping_cost_ex_tax'] = '';
		$expected['wrapping_cost_inc_tax'] = '';
		$expected['wrapping_cost_tax'] = '';
		$expected['wrapping_cost_tax_class_id'] = '';
		$expected['total_ex_tax'] = '';
		$expected['total_inc_tax'] = '';
		$expected['total_tax'] = '';
		$expected['items_total'] = '';
		$expected['items_shipped'] = '';
		$expected['payment_method'] = '';
		$expected['payment_provider_id'] = '';
		$expected['payment_status'] = '';
		$expected['refunded_amount'] = '';
		$expected['order_is_digital'] = '';
		$expected['store_credit_amount'] = '';
		$expected['gift_certificate_amount'] = '';
		$expected['ip_address'] = '';
		$expected['geoip_country'] = '';
		$expected['geoip_country_iso2'] = '';
		$expected['currency_id'] = '';
		$expected['currency_exchange_rate'] = '';
		$expected['default_currency_id'] = '';
		$expected['staff_notes'] = '';
		$expected['customer_message'] = '';
		$expected['discount_amount'] = '';
		$expected['coupon_discount'] = '';
		$expected['shipping_address_count'] = '';
		$expected['is_deleted'] = '';
		$expected['order_source'] = '';
		$expected['order_date'] = '';

		return $expected;

	}

	protected function getTestOrder()
	{
		$order = new Orders\Order(self::getOriginalOrderData());
		return $order;
	}

	protected function performMapping($mapingClass)
	{
		$mapper = new \DataModel\Mapper\Aggregate(array(
			new $mapingClass()
		));
		return $this->mapWith($mapper);
	}

	protected function mapWith($mapper)
	{
		$mapper->setObject($this->getTestOrder());
		$mapper->decorate();

		$order = array_pop($mapper->getObjects());
		return $order;
	}

	public function testSameObjectReturnedAfterMapping()
	{
		$order = $this->getTestOrder();
		$messages = $order->getMessages();
		$this->assertTrue(empty($messages), 'Message array should be empty initially.'); // Change to assertEmpty when PHPUnit 3.5 is in use

		$mapper = new \DataModel\Mapper\Aggregate(array(
			new \Orders\Mappers\OrderMapper()
		));
		$mapper->setObject($order);
		$mapper->decorate();

		$o = $mapper->getObjects();
		$this->assertSame($order, $o[0], 'Objects should be identical');

	}

	public function testOrderOfObjectsPreservedAfterMapping()
	{
		$orders[] = $this->getTestOrder();
		$orders[] = $this->getTestOrder();

		$mapper = new \DataModel\Mapper\Aggregate(array(
				new \Orders\Mappers\OrderMapper()
		));
		$mapper->setObjects($orders);
		$mapper->decorate();

		$this->assertEquals($mapper->getObjects(), $orders, 'Same objects should be returned in order');

	}

	public function testOrderApiFieldNamesAssignedAfterMapping()
	{

		$order = $this->performMapping('\Orders\Mappers\OrderMapper');

		$actual = self::getOriginalOrderData();

		/*
		 * Test some of the API mappings
		 */
		$this->assertEquals($order->getId(), $actual['orderid'], "Order ID was not mapped correctly");
		$this->assertEquals($order->customer_id, $actual['ordcustid'], "The customerId was not mapped correctly");
		$this->assertEquals($order->status_id, $actual['ordstatus'], "The Order Status ID was not mapped correctly");
		$this->assertEquals($order->staff_notes, $actual['ordnotes'], "The staff notes were not mapped correctly");
		$this->assertEquals($order->customer_message, $actual['ordcustmessage'], "The customer message was not mapped correctly");

	}

	public function testOrderBillingAddressMapping()
	{
		$settings = $this->getSettings(array('CompanyCountry' => 'Australia'));
		$mapper = new \Orders\Mappers\BillingAddress(false, false, $settings);
		$aggregate = new \DataModel\Mapper\Aggregate(array(
			$mapper,
		));
		$order = $this->mapWith($aggregate);

		$actual = self::getOriginalOrderData();

		$this->assertEquals($order->billing_address->first_name, $actual['ordbillfirstname'], "The billing address first name was not mapped correctly"); //
		$this->assertEquals($order->billing_address->street_1, $actual['ordbillstreet1'], "The billing address street1 was not mapped correctly"); //
		$this->assertEquals($order->billing_address->zip, $actual['ordbillzip'], "The billing address zip was not mapped correctly"); //
		$this->assertEquals($order->billing_address->country, $actual['ordbillcountry'], "The billing address country was not mapped correctly");
		$this->assertEquals($order->billing_address->city, $actual['ordbillsuburb'], "The billing address suburb was not mapped correctly"); //
		$this->assertTrue($order->billing_address->is_in_company_country, "The billing address suburb was not mapped correctly"); //
	}

	public function testOrderMessageMapping()
	{

		$stub = $this->getMock(
			'\Orders\Mappers\Messages', array('getData')
		);

		$stub->expects($this->any())
			->method('getData')
			->will($this->returnCallback('getMessageData'));

		$order = $this->getTestOrder();
		$messages = $order->getMessages();
		$this->assertTrue(empty($messages), 'Message array should be empty initially.'); // Change to assertEmpty when PHPUnit 3.5 is in use

		$mapper = new \DataModel\Mapper\Aggregate(array(
			$stub
		));
		$mapper->setObject($this->getTestOrder());
		$mapper->decorate();

		$o = array_pop($mapper->getObjects());

		$this->assertEquals(2, $o->getMessageCount('read'), "The order should have 2 read messages.");
		$this->assertEquals(4, $o->getMessageCount('unread'), "The order should have 4 unread messages.");
	}

	public function testOrderPaymentDetailsMapping()
	{
		$order = $this->performMapping('\Orders\Mappers\PaymentDetails');

		$actual = self::getOriginalOrderData();

		$this->assertEquals($order->getId(), $actual['orderid']);
		$this->assertEquals($order->subtotal_ex_tax, $actual['subtotal_ex_tax']);
		$this->assertEquals($order->subtotal_inc_tax, $actual['subtotal_inc_tax']);
		$this->assertEquals($order->subtotal_tax, $actual['subtotal_tax']);
		$this->assertEquals($order->total_tax, $actual['total_tax']);
		$this->assertEquals($order->total_ex_tax, $actual['total_ex_tax']);
		$this->assertEquals($order->total_inc_tax, $actual['total_inc_tax']);

	}

	public function testPaymentDetailsTotalRowsMapperAssignsMainAmounts()
	{
		$order = $this->performMapping('\Orders\Mappers\PaymentDetails');
		$this->assertEquals($order->totalRows['subtotal']['value'], "100.0000", "Subtotal not correct");
		$this->assertEquals($order->totalRows['total']['value'], "100.0000", "Total not correct");
	}

	public function testOrderProductsMapperAssignsCorrectNumberOfProducts()
	{
		$stub = $this->getMock(
			'\Orders\Mappers\OrderProducts', array('getData')
		);

		$stub->expects($this->any())
			->method('getData')
			->will($this->returnCallback('getProductsData'));

		$mapper = new \DataModel\Mapper\Aggregate(array(
			$stub
		));
		$mapper->setObject($this->getTestOrder());
		$mapper->decorate();

		$order = array_pop($mapper->getObjects());

		$this->assertEquals(2, (int)count($order->getProductsByType('digital')), 'Expecting some digital products to be assigned but there were only '.count($order->getProductsByType('digital')));
		$this->assertEquals(3, (int)count($order->getProductsByType('physical')), 'Expecting some physical products to be assigned');
		$this->assertEquals(1, (int)count($order->getProductsByType('giftcertificate')), 'Expecting some giftcertificates products to be assigned');
	}

	public function testShippingAddressMapperAssignsCorrectNumberOfAddresses()
	{
		$stub = $this->getMock(
			'\Orders\Mappers\ShippingAddresses', array('getData')
		);

		$stub->expects($this->any())
			->method('getData')
			->will($this->returnCallback('getShippingAddressData'));

		$mapper = new \DataModel\Mapper\Aggregate(array(
			$stub
		));
		$mapper->setObject($this->getTestOrder());
		$mapper->decorate();

		$order = array_pop($mapper->getObjects());
		$this->assertEquals(2, count($order->getShippingAddresses()), "There should be two shipping addresses");

		$this->assertEquals(1, count($order->getShippingAddressById(1)), "There should be oneshipping addresses");
		$this->assertEquals('FirstName1', $order->getShippingAddressById(1)->getFirstName(), "First name not mapped correctly");
		$this->assertEquals('LastName1', $order->getShippingAddressById(1)->getLastName(), "Last name not mapped correctly");

	}

	public function testGetProductsByShippingAddressIdAfterMapping()
	{
		$stub = $this->getMock(
			'\Orders\Mappers\OrderProducts', array('getData')
		);

		$stub->expects($this->any())
			->method('getData')
			->will($this->returnCallback('getProductsData'));

		$mapper = new \DataModel\Mapper\Aggregate(array(
			$stub
		));
		$mapper->setObject($this->getTestOrder());
		$mapper->decorate();

		$order = array_pop($mapper->getObjects());

		$this->assertEquals(1, count($order->getProductsByShippingAddressId(1)), 'ShippingAddressID 1 should have one products');
		$this->assertEquals(2, count($order->getProductsByShippingAddressId(2)), 'ShippingAddressID 2 should have two products');



	}

	protected function getSettings($overrides = array())
	{
		$driver = new \Store_Settings_Driver_Dummy();

		$driver->config = array_merge($driver->config, $overrides);

		$settings = new Store_Settings();
		$settings->setDriver($driver);
		$settings->load();
		return $settings;
	}


}

function getMessageData()
{
	$data[Unit_Orders_Mapping::$orderId] = array(
		'read' => array(1,2),
		'unread' => array(3,4,5,6),
	);
	return $data;
}

/**
 * Setup some sample products data to meet the following conditions
 * all product types (digital, gift certificate and physical)
 * physical: 3 products, 2 addresses (one to addressId=1; two to addressId=2).
 * digital: 2 products.
 * giftcertificate: 1 product.
 */
function getProductsData()
{

	$row = array();
	$row['orderprodid'] = 1000;
	$row['ordprodname'] = 'Digital';
	$row['ordprodtype'] = 'digital';
// 	$digital = new Orders\Mappers\tmp($row['orderprodid'], $row);

	$digital = new Store_Order_Product($row);
// 	$digital->setType('digital');

	$row = array();
	$row['orderprodid'] = 1;
	$row['ordprodname'] = 'ProdName1';
	$row['ordprodtype'] = 'physical';
	$row['order_address_id'] = 1;
// 	$physicalProductAddress1 = new Orders\Mappers\tmp($row['orderprodid'], $row);

	$physicalProductAddress1 = new Store_Order_Product($row);
// 	$physicalProductAddress1->setId(1);
// 	$physicalProductAddress1->setName('ProductName1');
// 	$physicalProductAddress1->setType('physical');
// 	$physicalProductAddress1->setOrderAddressId(1);

	$row = array();
	$row['orderprodid'] = 2;
	$row['ordprodname'] = 'ProdName2';
	$row['ordprodtype'] = 'physical';
	$row['order_address_id'] = 2;
// 	$physicalProductAddress2a = new Orders\Mappers\tmp($row['orderprodid'], $row);

	$physicalProductAddress2a = new Store_Order_Product($row);
// 	$physicalProductAddress2a->setId(2);
// 	$physicalProductAddress2a->setName('ProductName2');
// 	$physicalProductAddress2a->setType('physical');
// 	$physicalProductAddress2a->setOrderAddressId(2);

	$row = array();
	$row['orderprodid'] = 3;
	$row['ordprodname'] = 'ProdName3';
	$row['ordprodtype'] = 'physical';
	$row['order_address_id'] = 2;
// 	$physicalProductAddress2b = new Orders\Mappers\tmp($row['orderprodid'], $row);

	$physicalProductAddress2b = new Store_Order_Product($row);
// 	$physicalProductAddress2b->setId(3);
// 	$physicalProductAddress2b->setName('ProductName3');
// 	$physicalProductAddress2b->setType('physical');
// 	$physicalProductAddress2b->setOrderAddressId(2);

// 	echo "\n WTFa: ".$physicalProductAddress2a->getId();
// 	echo "\n WTFb: ".$physicalProductAddress2b->getId();

	$row = array();
	$row['orderprodid'] = 100000;
	$row['ordprodname'] = 'Gift Cert';
	$row['ordprodtype'] = 'giftcertificate';
// 	$giftcertificate = new Orders\Mappers\tmp($row['orderprodid'], $row);

	$giftcertificate = new Store_Order_Product($row);
// 	$giftcertificate->setType('giftcertificate');

	$data[Unit_Orders_Mapping::$orderId] = array(
		$digital,
		clone($digital),
		$physicalProductAddress1,
		$physicalProductAddress2a,
		$physicalProductAddress2b,
		$giftcertificate,
	);
	return $data;
}

function getShippingAddressData()
{

	$addressData[1] = array(
        'id' => 1,
        'order_id' => Unit_Orders_Mapping::$orderId,
        'first_name' => "FirstName1",
        'last_name' => 'LastName1',
        'company' => 'Company1',
        'address_1' => 'Address11',
        'address_2' => 'Address21',
        'city' => 'City1',
        'zip' => 'Zip1',
        'country' => 'Country1',
        'country_iso2' => 'CountryISO1',
        'country_id' => 1,
        'state' => 'State1',
        'state_id' => 1,
        'email' => 'email@email.com1',
        'phone' => 'phone1',
        'form_session_id' => 0,
        'total_items' => 1,
    );

	$addressData[2] = array(
		'id' => 2,
		'order_id' => Unit_Orders_Mapping::$orderId,
		'first_name' => "FirstName2",
		'last_name' => 'LastName2',
		'company' => 'Company2',
		'address_1' => 'Address22',
		'address_2' => 'Address22',
		'city' => 'City2',
		'zip' => 'Zip2',
		'country' => 'Country2',
		'country_iso2' => 'CountryISO2',
		'country_id' => 2,
		'state' => 'State2',
		'state_id' => 2,
		'email' => 'email@email.com2',
		'phone' => 'phone2',
		'form_session_id' => 0,
		'total_items' => 2,
	);

	$data[Unit_Orders_Mapping::$orderId] = array(
		new \Orders\Address($addressData[1]),
		new \Orders\Address($addressData[2]),
	);
	return $data;
}