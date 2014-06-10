<?php

use Orders\PendingOrderVerification;

class Unit_Orders_PendingOrderVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNewOrderStatusEqualsNewOrderStatusConstructorArgument()
    {
        $newOrderStatus = 1;
        $verification = new PendingOrderVerification(true, null, $newOrderStatus);
        $this->assertEquals($newOrderStatus, $verification->getNewOrderStatus());
    }

    public function testInheritedPropertiesArePassedToParentConstructor()
    {
        $message = 'Everything is okay';
        $verification = new PendingOrderVerification(true, 'Everything is okay', 1);
        $this->assertTrue($verification->isValid());
        $this->assertEquals($message, $verification->getMessage());
    }
}