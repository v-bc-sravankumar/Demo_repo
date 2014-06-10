<?php

class Unit_Modules_Checkout_Nmi extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!isset($GLOBALS['ShopPath'])) {
            $GLOBALS['ShopPath'] = '';
        }
    }

    public function testLoadClass()
    {
        $gateway = new CHECKOUT_NMI();
    }

    public function testClassNameIsCorrectlySet()
    {
        $gateway = new CHECKOUT_NMI();
        $this->assertEquals('NMI', $gateway->namespace);
    }

    public function testGetName()
    {
        $gateway = new CHECKOUT_NMI();
        $this->assertEquals('NMI', $gateway->GetName());
    }
}
