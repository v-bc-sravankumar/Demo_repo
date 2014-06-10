<?php

class Unit_Modules_Checkout_Authorizenet extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!isset($GLOBALS['ShopPath'])) {
            $GLOBALS['ShopPath'] = '';
        }
    }

    public function testLoadClass()
    {
        $gateway = new CHECKOUT_AUTHORIZENET();
    }

    public function testNamespace()
    {
        $gateway = new CHECKOUT_AUTHORIZENET();
        $this->assertEquals('AuthorizeNet', $gateway->namespace);
    }

    public function testLanguageName()
    {
        $gateway = new CHECKOUT_AUTHORIZENET();
        $this->assertEquals('Authorize.net', $gateway->GetName());
    }
}
