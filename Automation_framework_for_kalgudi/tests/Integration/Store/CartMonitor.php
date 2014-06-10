<?php

// supress the error raised by trigger_error in CartMonitor
function silentErrorHandler($errno, $errstr, $errfile, $errline)
{
    return true;
}

class Unit_Store_CartMonitor extends PHPUnit_Framework_TestCase
{

    private $cm = null;
    private $db = null;

    public function setUp()
    {
        $this->cm = new Store_CartMonitor();

        \Store_Cart::find()->deleteAll();
        \Store_Cart_Statistics::find()->deleteAll();
        set_error_handler("silentErrorHandler");
    }

    public function tearDown()
    {
        \Store_Cart::find()->deleteAll();
        \Store_Cart_Statistics::find()->deleteAll();
        restore_error_handler();
    }

    private function makeQuote($itemCount, $grandTotal)
    {
        $quote = $this->getMock('ISC_QUOTE', array('getTotalItemCount', 'getGrandTotal', 'getCustomerId'));

        $quote->expects($this->any())
            ->method('getTotalItemCount')
            ->will($this->returnValue($itemCount));

        $quote->expects($this->any())
            ->method('getGrandTotal')
            ->will($this->returnValue($grandTotal));

        $quote->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        return $quote;
    }

    private function makeCartEvent($eventName, $quoteItemCount, $quoteGrandTotal, $cartId = 0)
    {
        $event = new Interspire_Event($eventName);
        $quote = $this->makeQuote($quoteItemCount, $quoteGrandTotal);
        $quote->setCartId($cartId);
        $event->data['cart'] = $quote;
        return $event;
    }

    public function testCreateCart_Sucess()
    {
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CHECKOUT_CUSTOMER_LOGGED_IN, 5, 100));

        $this->assertEquals(1, Store_Cart::find()->count());

        $cart = Store_Cart::find()->first();
        $quote = $cart->getCart();
        $this->assertFalse($cart->isAbandoned());
        $this->assertFalse(empty($quote));

        $stats = $cart->getCartStatistics();
        $this->assertFalse(empty($stats));
        $this->assertFalse($stats->isAbandoned());
        $this->assertEquals(5, $stats->getNumberOfProducts());
        $this->assertEquals(100, $stats->getTotalPrice());
    }

    public function testUpdateCart_Sucess()
    {
        // create a cart for test
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CHECKOUT_CUSTOMER_LOGGED_IN, 5, 100));
        $cart = Store_Cart::find()->first();
        $cartId = Store_Cart::find()->first()->getId();

        // update the previously created cart
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CART_ADDED_PRODUCT, 10, 200, $cartId));

        $this->assertEquals(1, Store_Cart::find()->count());

        // cart stats should be updated
        $stats = Store_Cart_Statistics::findByCartId($cartId)->first();
        $this->assertFalse(empty($stats));
        $this->assertEquals(10, $stats->getNumberOfProducts());
        $this->assertEquals(200, $stats->getTotalPrice());
    }

    public function testCreateCart_CartSaveFailed()
    {
        $this->cm->setCart($this->makeSaveDisabledCart());
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CART_ADDED_PRODUCT, 5, 100));

        $this->assertEquals(0, Store_Cart::find()->count());
        $this->assertEquals(0, Store_Cart_Statistics::find()->count());
    }

    public function testCreateCart_CartStatsSaveFailed()
    {
        $this->cm->setCartStatistics($this->makeSaveDisabledCartStatistics());
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CART_ADDED_PRODUCT, 5, 100));

        $this->assertEquals(0, Store_Cart::find()->count());
        $this->assertEquals(0, Store_Cart_Statistics::find()->count());
    }

    public function testUpdateCart_CartUpdateFail()
    {
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CHECKOUT_CUSTOMER_LOGGED_IN, 5, 100));
        $cartId = Store_Cart::find()->first()->getId();

        $this->cm->setCart($this->makeSaveDisabledCart());
        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CART_ADDED_PRODUCT, 10, 200, $cartId));

        // cart stats shouldn't be updated
        $stats = Store_Cart_Statistics::findByCartId($cartId)->first();
        $this->assertFalse(empty($stats));
        $this->assertEquals(5, $stats->getNumberOfProducts());
        $this->assertEquals(100, $stats->getTotalPrice());
    }

/*
 *    public function testUpdateCart_CartStatsUpdateFail()
 *    {
 *        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CHECKOUT_CUSTOMER_LOGGED_IN, 5, 100));
 *        $cartId = Store_Cart::find()->first()->getId();
 *
 *        $this->cm->setCartStatistics($this->makeSaveDisabledCartStatistics());
 *        $this->cm->_handleCartOperation($this->makeCartEvent(Store_Event::EVENT_CART_ADDED_PRODUCT, 10, 200, $cartId));
 *
 *        // cart stats shouldn't be updated
 *        $stats = Store_Cart_Statistics::findByCartId($cartId)->first();
 *        $this->assertFalse(empty($stats));
 *        $this->assertEquals(5, $stats->getNumberOfProducts());
 *        $this->assertEquals(100, $stats->getTotalPrice());
 *    }
 */

    private function makeSaveDisabledCart()
    {
        return $this->makeSaveDisabledModel('Store_Cart');
    }

    private function makeSaveDisabledCartStatistics()
    {
        return $this->makeSaveDisabledModel('Store_Cart_Statistics');
    }

    private function makeSaveDisabledModel($modelClass)
    {
        $model = $this->getMock($modelClass, array('save'));
        $model->expects($this->any())
            ->method('save')
            ->will($this->returnValue(false));

        return $model;
    }

}
