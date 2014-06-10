<?php

class Unit_Store_CheckoutTest extends PHPUnit_Framework_TestCase
{
  public function expressCheckoutDataProvider()
  {
    $mobileUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3';
    $desktopUA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.152 Safari/537.2';

    // $userAgent, $mobileThemeVersion, $single, $expected
    return array(
      array($desktopUA, 0, true, true),
      array($desktopUA, 0, false, false),

      array($mobileUA, 0, true, false),
      array($mobileUA, 0, false, false),

      array($desktopUA, 1, true, true),
      array($desktopUA, 1, false, false),

      array($mobileUA, 1, true, true),
      array($mobileUA, 1, false, false),

      array($desktopUA, 2, true, true),
      array($desktopUA, 2, false, false),

      array($mobileUA, 2, true, true),
      array($mobileUA, 2, false, false),
    );
  }
  /**
   * @dataProvider expressCheckoutDataProvider
   */
  public function testIsExpressCheckout($userAgent, $mobileThemeVersion, $single, $expected)
  {
    $checkout = new ISC_CHECKOUT();

    Store_Config::override('CheckoutType', $single ? 'single' : 'multi');
    Store_Config::override('MobileThemeVersion', $mobileThemeVersion);

    $isExpress = $checkout->isExpressCheckout($userAgent);

    $this->assertEquals($expected, $isExpress);
  }
}
