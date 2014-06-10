<?php

class Unit_Lib_Store_Api_Version_2_Resource_Mobile_Dashboard extends Interspire_IntegrationTest
{
  private function _getDashboardResource()
  {
    return new Store_Api_Version_2_Resource_Mobile_Dashboard();
  }

  public static function setUpBeforeClass()
  {
    parent::setUpBeforeClass();
    Interspire_DataFixtures::getInstance()->loadData('orders-some-deleted');
  }

  public static function tearDownAfterClass()
  {
    Interspire_DataFixtures::getInstance()->removeData('orders-some-deleted');
  }

  public function testDashboard()
  {
    $request = new Interspire_Request();
    $dashboard = $this->_getDashboardResource();
    $result = $dashboard->getAction($request)->getData(true);

    $this->assertEquals(4, $result['Orders']);
    $this->assertEquals(1, $result['Fulfil']);
    $this->assertEquals(1, $result['Ship']);
  }
}
