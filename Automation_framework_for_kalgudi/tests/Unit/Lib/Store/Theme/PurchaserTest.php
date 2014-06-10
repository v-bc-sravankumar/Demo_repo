<?php

namespace Unit\Lib\Store\Theme;

use \Store\Theme\Purchaser as Purchaser;
use \Store_Config;
use \Stripe_CardError;
use \org\bovigo\vfs\vfsStream;
use \PHPUnit_Framework_TestCase;

class PurchaserTest extends PHPUnit_Framework_TestCase
{
    /** @var  Purchaser */
    private $purchaser;
    private $themeBase;

    public function setUp()
    {
        $this->themeBase = $this->setupTestTheme();
        $this->purchaser = new Purchaser();
        $this->purchaser->getAsset()->setRepoBasePath('vfs://test_theme_root');
    }

    public function tearDown()
    {
        Store_Config::revert('PurchasedThemes');
        $this->purchaser->getAsset()->setRepoBasePath(null);
    }

    private function setupTestTheme()
    {
        // initialize vfs
        vfsStream::setup('test_theme_root');
        $root = vfsStream::url('test_theme_root');

        // setup basic theme folder structure
        mkdir($root . "/Test");
        mkdir($root . "/Test/Styles");

        // add a color scheme to the theme
        file_put_contents($root . "/Test/Styles/green.css", "css");

        // setup purchasable theme
        mkdir($root . "/PurchasableThemeTest");
        $premium = "<?php \$GLOBALS['TPL_CFG']['Premium'] = array(
            'price' => 100,
            'currency' => 'usd',
        );";
        file_put_contents($root . "/PurchasableThemeTest/config.php", $premium);

        return $root;
    }

    public function testGetPurchaseInfoWithInvalidThemeColor()
    {
        $this->assertFalse($this->purchaser->getPurchaseInfo('Test', 'red'));
    }

    public function testGetPurchaseInfoFromNotPurchasableTheme()
    {
        $this->assertFalse($this->purchaser->getPurchaseInfo('Test', 'green'));
    }

    public function testGetPurchaseInfoSuccess()
    {
        $result = $this->purchaser->getPurchaseInfo('PurchasableThemeTest', 'green');

        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('currency', $result);
    }

    public function testIsNotPurchasable()
    {
        $this->assertFalse($this->purchaser->isPurchasable('Test', 'green'));
    }

    public function testIsPurchasable()
    {
        $this->assertTrue($this->purchaser->isPurchasable('PurchasableThemeTest', 'green'));
    }

    public function testHasNotPurchase()
    {
        Store_Config::override('PurchasedThemes', false);
        $this->assertFalse($this->purchaser->hasPurchased('Test', 'green'));
    }

    public function testHasPurchase()
    {
        Store_Config::override('PurchasedThemes', array(
            'PurchasableThemeTest-green' => 'stripe charge id'
        ));
        $this->assertTrue($this->purchaser->hasPurchased('PurchasableThemeTest', 'green'));
    }

    /**
     * @expectedException \Stripe_CardError
     */
    public function testPurchaseFailed()
    {
        $paymentClass = $this->getMockClass('\Stripe_Charge', array('create'));
        $paymentClass::staticExpects($this->once())
            ->method('create')
            ->will($this->throwException(
                new Stripe_CardError("invalid card", null, 500)
            ));

        $this->purchaser->setPaymentClass($paymentClass);
        $this->purchaser->purchase('Test', 'green', 'invalidtoken');
    }

    public function testPurchaseSuccessful()
    {
        $charge = $this->getMock('Stripe_Charge', array('__get'));
        $charge->expects($this->atLeastOnce())
            ->method('__get')
            ->will($this->returnValue('valid charge id'));

        $paymentClass = $this->getMockClass('Stripe_Charge', array('create'));
        $paymentClass::staticExpects($this->once())
            ->method('create')
            ->will($this->returnValue($charge));

        $this->purchaser->setPaymentClass($paymentClass);

        $config = $this->getMock('Store_Settings', array('schedule', 'commit'));
        $config->expects($this->once())
            ->method('schedule')
            ->with($this->equalTo('PurchasedThemes'),
                $this->equalTo(array('Test-green' => 'valid charge id')));
        $config->expects($this->once())->method('commit');

        $this->purchaser->setStoreConfig($config);

        $this->assertTrue($this->purchaser->purchase('Test', 'green', 'token'));
    }

}
