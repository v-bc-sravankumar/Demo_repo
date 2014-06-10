<?php

class Unit_Lib_Store_Api_Version_2_Resource_Mobile_Orders_Count extends Interspire_IntegrationTest
{
  public static function setUpBeforeClass()
  {
    parent::setUpBeforeClass();
    Interspire_DataFixtures::getInstance()->loadData('orders-some-deleted');
  }

  public static function tearDownAfterClass()
  {
    Interspire_DataFixtures::getInstance()->removeData('orders-some-deleted');
  }

  public function testCount()
  {
    $resource = new Store_Api_Version_2_Resource_Mobile_Orders_Count();
    $request = new Interspire_Request();
    $wrapper = $resource->getAction($request);

    $data = $wrapper->getData();

    $this->assertArrayIsNotEmpty($data);

    $expected = array(
      'orders' => 4,
      'statuses' => array(
        (object) array(
         'name' => 'Incomplete',
         'id' => 0,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Pending',
         'id' => 1,
         'count' => 2,
        ),
        (object) array(
         'name' => 'Awaiting Payment',
         'id' => 7,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Awaiting Fulfillment',
         'id' => 11,
         'count' => 1,
        ),
        (object) array(
         'name' => 'Awaiting Shipment',
         'id' => 9,
         'count' => 1,
        ),
        (object) array(
         'name' => 'Awaiting Pickup',
         'id' => 8,
         'count' => 0,
      ),
        (object) array(
         'name' => 'Partially Shipped',
         'id' => 3,
         'count' => 0,
      ),
        (object) array(
         'name' => 'Completed',
         'id' => 10,
         'count' => 0,
      ),
        (object) array(
         'name' => 'Shipped',
         'id' => 2,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Cancelled',
         'id' => 5,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Declined',
         'id' => 6,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Refunded',
         'id' => 4,
         'count' => 0,
        ),
        (object) array(
         'name' => 'Manual Verification Required',
         'id' => 12,
         'count' => 0,
        ),
      ),
    );
    $this->assertEquals($expected, $data);
  }
}
