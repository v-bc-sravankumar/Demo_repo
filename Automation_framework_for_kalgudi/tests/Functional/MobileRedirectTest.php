<?php

class MobileRedirectTest extends Interspire_FunctionalTest
{
    public function testRedirectIPhone()
    {
        $this->setHeader('User-Agent', 'iPhone AppleWebKit');
        Store_Feature::enable('MobileRedirect');
        $this->get(TEST_APPLICATION_URL . '/admin');
        $this->assertStatus(302);
        $this->assertEquals('http://m.bigcommerce.com/admin', $this->getHeader('Location'));
    }
    public function testRedirectIPhoneSpecificOrder()
    {
      $this->setHeader('User-Agent', 'iPhone AppleWebKit');
      Store_Feature::enable('MobileRedirect');
      $this->get(TEST_APPLICATION_URL . '/admin/index.php?ToDo=viewOrders&orderId=100');
      $this->assertStatus(302);
      $this->assertRegExp(
        '_^http://m.bigcommerce.com/admin\?redirectToken=([\da-f]{8}-?(?:[\da-f]{4}-?){3}[\da-f]{12})$_',
        $this->getHeader('Location')
      );
    }
}

