<?php

class Unit_Store_MobileTest extends PHPUnit_Framework_TestCase
{
  const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A405 Safari/7534.48.3';
  const IPAD = 'Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25';
  const IPOD = 'Mozila/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Geckto) Version/3.0 Mobile/3A101a Safari/419.3';
  const PRE = 'Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0';
  const GALAXY_S3 = 'Mozilla/5.0 (Linux; U; Android 4.0.4; en-gb; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';

  private $backupSettings;

  public function setUp()
  {
    $this->backupSettings = Store_Config::getInstance();

    $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
        'Feature_ModernUI'          => true,
        'Feature_RebrandingSwitch'  => true,
    )));

    $settings->load();

    Store_Config::setInstance($settings);
  }

  public function tearDown()
  {
    Store_Config::setInstance($this->backupSettings);
  }

  public function testLegacy()
  {
    Store_Feature::disable('ModernUI');
    Store_Feature::disable('RebrandingSwitch');

    Store_Config::override('enableMobileTemplate', false);
    Store_Config::override('enableMobileTemplateDevices', array(
      'iphone',
      'ipod',
      'pre',
      'android',
      'ipad',
    ));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplate', true);

    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array('iphone'));

    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array('ipod'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array('pre'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array('android'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array('ipad'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevices', array());

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));
  }

  public function testModernUI()
  {
    Store_Feature::enable('ModernUI');
    Store_Feature::enable('RebrandingSwitch');

    Store_Config::override('enableMobileTemplate', false);

    Store_Config::override('enableMobileTemplateDevicesMUI', array('mobile', 'tablet'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplate', true);

    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevicesMUI', array());

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevicesMUI', array('mobile'));

    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));

    Store_Config::override('enableMobileTemplateDevicesMUI', array('tablet'));

    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPHONE));
    $this->assertTrue(Store_Mobile::canViewMobileStoreFront(self::IPAD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::IPOD));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::PRE));
    $this->assertFalse(Store_Mobile::canViewMobileStoreFront(self::GALAXY_S3));
  }
}