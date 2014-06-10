<?php

class Unit_Lib_Store_Api_Version_2_Resource_Mobile_Orders extends Interspire_IntegrationTest
{
  public static function setUpBeforeClass()
  {
    parent::setUpBeforeClass();
    Interspire_DataFixtures::getInstance()->loadData('big-4574');
  }

  public static function tearDownAfterClass()
  {
    Interspire_DataFixtures::getInstance()->removeData('big-4574');
  }

  public function testGetAction()
  {
    $resource = new Store_Api_Version_2_Resource_Mobile_Orders();
    $request = new Interspire_Request();
    $request->setUserParam('customers', 1);
    $wrapper = $resource->getAction($request);

    $data = $wrapper->getData();

    $this->assertArrayIsNotEmpty($data);
    $data = $data[0];

    $expected = array(
      'order_address_id' => array(
        1,
        2,
      ),
      'id' => 100,
      'product_count' => 3,
      'customer_id' => 0,
      'date_created' => 'Fri, 24 May 2013 01:56:22 +0000',
      'date_modified' => 'Fri, 24 May 2013 01:56:22 +0000',
      'date_shipped' => '',
      'status_id' => 11,
      'status' => 'Awaiting Fulfillment',
      'subtotal_ex_tax' => '208.0000',
      'subtotal_inc_tax' => '208.0000',
      'subtotal_tax' => '0.0000',
      'base_shipping_cost' => '0.0000',
      'shipping_cost_ex_tax' => '0.0000',
      'shipping_cost_inc_tax' => '0.0000',
      'shipping_cost_tax' => '0.0000',
      'shipping_cost_tax_class_id' => 2,
      'base_handling_cost' => '0.0000',
      'handling_cost_ex_tax' => '0.0000',
      'handling_cost_inc_tax' => '0.0000',
      'handling_cost_tax' => '0.0000',
      'handling_cost_tax_class_id' => 2,
      'base_wrapping_cost' => '0.0000',
      'wrapping_cost_ex_tax' => '0.0000',
      'wrapping_cost_inc_tax' => '0.0000',
      'wrapping_cost_tax' => '0.0000',
      'wrapping_cost_tax_class_id' => 3,
      'total_ex_tax' => '208.0000',
      'total_inc_tax' => '208.0000',
      'total_tax' => '0.0000',
      'items_total' => 3,
      'items_shipped' => 0,
      'payment_method' => 'cash',
      'payment_provider_id' => null,
      'payment_status' => '',
      'refunded_amount' => '0.0000',
      'order_is_digital' => false,
      'store_credit_amount' => '0.0000',
      'gift_certificate_amount' => '0.0000',
      'ip_address' => '10.1.3.101',
      'geoip_country' => '',
      'geoip_country_iso2' => '',
      'currency_id' => 1,
      'currency_code' => 'USD',
      'currency_exchange_rate' => '1.0000000000',
      'default_currency_id' => 1,
      'default_currency_code' => 'USD',
      'staff_notes' => '',
      'customer_message' => '',
      'discount_amount' => '0.0000',
      'coupon_discount' => '0.0000',
      'shipping_address_count' => 2,
      'is_deleted' => false,
      'billing_address' => array(
        'first_name' => '',
        'last_name' => '',
        'company' => '',
        'street_1' => '',
        'street_2' => '',
        'city' => '',
        'state' => 'New South Wales',
        'zip' => '2000',
        'country' => 'Australia',
        'country_iso2' => 'AU',
        'phone' => '',
        'email' => '',
      ),
      'subtotal_ex_tax_formatted' => '$208.00',
      'subtotal_inc_tax_formatted' => '$208.00',
      'subtotal_tax_formatted' => '$0.00',
      'base_shipping_cost_formatted' => '$0.00',
      'shipping_cost_ex_tax_formatted' => '$0.00',
      'shipping_cost_inc_tax_formatted' => '$0.00',
      'shipping_cost_tax_formatted' => '$0.00',
      'base_handling_cost_formatted' => '$0.00',
      'handling_cost_ex_tax_formatted' => '$0.00',
      'handling_cost_inc_tax_formatted' => '$0.00',
      'handling_cost_tax_formatted' => '$0.00',
      'base_wrapping_cost_formatted' => '$0.00',
      'wrapping_cost_ex_tax_formatted' => '$0.00',
      'wrapping_cost_inc_tax_formatted' => '$0.00',
      'wrapping_cost_tax_formatted' => '$0.00',
      'total_ex_tax_formatted' => '$208.00',
      'total_inc_tax_formatted' => '$208.00',
      'total_tax_formatted' => '$0.00',
      'refunded_amount_formatted' => '$0.00',
      'store_credit_amount_formatted' => '$0.00',
      'gift_certificate_amount_formatted' => '$0.00',
      'discount_amount_formatted' => '$0.00',
      'coupon_discount_formatted' => '$0.00',
      'customer' => array(
        'first_name' => null,
        'last_name' => null,
        'email' => null,
      ),
      'gravatar' => 'http://www.gravatar.com/avatar/d41d8cd98f00b204e9800998ecf8427e?d=identicon&s=70',
    );

    unset($data['shipping_addresses']);
    unset($data['coupons']);

    $this->assertEquals($expected, $data);
  }
}
