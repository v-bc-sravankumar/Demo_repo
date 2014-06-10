<?php

use \Store\Mobile\EventHandler;

class Unit_Store_Mobile_EventHandler extends PHPUnit_Framework_TestCase
{

  public function setUp()
  {
    \Config\Environment::override(new \Config\Properties(array(
      'mobile' => array(
          'api_key' => 'test_api_key',
          'push_service' => array(
            'url' => 'test_url',
          ),
      ),
    )));
  }

  public function tearDown()
  {
    \Config\Environment::restore();
  }

  public function testDispatchNewManualOrder()
  {
    $event = new Interspire_Event(Store_Event::ORDERS_COMPLETE_ORDER);

    $event->data = array(
      'orderId' => 1,
    );

    $handler = $this->getMock(
      '\Store\Mobile\EventHandler',
      array('dispatchToService')
    );

    $handler->expects($this->once())
            ->method('dispatchToService')
            ->with(
              $this->equalTo('test_url'),
              $this->equalTo(array(
                'api_key' => 'test_api_key',
                'store_hash' => \BigCommerce_Account::getInstance()->getStoreHash(),
                'store_hosting_id' => \BigCommerce_Account::getInstance()->getHostingId(),
                'order_id' => 1,
              ))
            );

    $handler->handleEvent($event);
  }
}