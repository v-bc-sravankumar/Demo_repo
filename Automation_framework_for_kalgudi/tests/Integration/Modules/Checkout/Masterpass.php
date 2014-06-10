<?php

class Integration_Modules_Checkout_MasterPass extends PHPUnit_Framework_TestCase {

    public function testLoadClass()
    {
        $masterpassObj = new CHECKOUT_MASTERPASS();

        $this->assertNotNull($masterpassObj);

        $this->assertEquals('MasterPass',$masterpassObj->namespace);

        $this->assertEquals(true,$masterpassObj->isDigitalWallet());

        $this->assertEquals(true,$masterpassObj->updatePaymentProvider());

        $this->assertNotNull($masterpassObj->getGateway());

        $this->assertNotNull($masterpassObj->getPaymentProvider());
    }
}