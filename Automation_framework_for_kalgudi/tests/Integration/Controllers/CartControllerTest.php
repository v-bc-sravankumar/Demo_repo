<?php

namespace Integration\Controllers;

use ISC_CART;

class CartControllerTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage quantity
   */
  public function testAddSimpleProductToCartWithZeroQuantityFails()
  {
    $cartController = new ISC_CART();
    $cartController->AddSimpleProductToCart(1, 0);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage quantity
   */
  public function testAddSimpleProductToCartWithNegativeQuantityFails()
  {
    $cartController = new ISC_CART();
    $cartController->AddSimpleProductToCart(1, -1);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage quantity
   */
  public function testAddSimpleProductToCartWithNonNumericQuantityFails()
  {
    $cartController = new ISC_CART();
    $cartController->AddSimpleProductToCart(1, "foo");
  }
}
