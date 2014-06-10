<?php

namespace Unit\Lib\Store\Theme;

use \Store_Config;
use \Store\Theme\Context as Context;
use \PHPUnit_Framework_TestCase;

class ContextTest extends PHPUnit_Framework_TestCase
{

    public function nameDataProvider()
    {
        return array(
            array(Context::DESKTOP, '\Store\Theme\Context\Desktop'),
            array(Context::MOBILE, '\Store\Theme\Context\Mobile'),
            array(Context::FACEBOOK, '\Store\Theme\Context\Facebook'),
        );
    }

    /**
     * @dataProvider nameDataProvider
     */
    public function testCreateByName($name, $class)
    {
        $this->assertInstanceOf($class, Context::create($name));
    }

    public function testGetNameOnDesktop()
    {
        $context = Context::get();
        $this->assertEquals(Context::DESKTOP, $context->getName());
    }

    public function testGetNameOnMobile()
    {
        $mobileRequest = $this->getMock('\Interspire_Request',
            array('get','cookie'));
        $mobileRequest->expects($this->any())
            ->method('get')
            ->with($this->equalTo('fullSite'))
            ->will($this->returnValue(null));
        $mobileRequest->expects($this->any())
            ->method('cookie')
            ->with($this->equalTo('mobileViewFullSite'))
            ->will($this->returnValue(null));

        $mobileClass = $this->getMockClass('\Store_Mobile',
            array('canViewMobileStorefront'));
        $mobileClass::staticExpects($this->any())
            ->method('canViewMobileStorefront')
            ->will($this->returnValue(true));

        $context = Context::get($mobileRequest, $mobileClass);

        $this->assertEquals(Context::MOBILE, $context->getName());
    }

    public function testGetNameOnFacebook()
    {
        Store_Config::override('DisplayMode', 'socialshop');
        $context = Context::get();
        $this->assertEquals(Context::FACEBOOK, $context->getName());
        Store_Config::revert('DisplayMode');
    }

    public function testGetThemeToApplyOnDesktop()
    {
        Store_Config::override('template', 'test-theme');
        $context = new Context\Desktop();
        $this->assertEquals('test-theme', $context->getThemeToApply());
        Store_Config::revert('template');
    }

    public function testGetThemeToApplyOnMobile()
    {
        $mobileClass = $this->getMockClass('\Store_Mobile',
            array('getMobileTheme',));
        $mobileClass::staticExpects($this->any())
            ->method('getMobileTheme')
            ->will($this->returnValue('__mobile'));

        $context = new Context\Mobile();
        $context->setMobileClass($mobileClass);
        $this->assertEquals('__mobile', $context->getThemeToApply());
    }

    public function testGetThemeToApplyOnFacebook()
    {
        $context = new Context\Facebook();
        $this->assertEquals('__SocialShop', $context->getThemeToApply());
    }

    public function testGetCustomBasePathOnDesktop()
    {
        $context = new Context\Desktop();
        $this->assertEquals('__custom', $context->getCustomBasePath());
    }

    public function testGetCustomBasePathOnMobile()
    {
        $context = new Context\Mobile();
        $this->assertEquals('__custommobile', $context->getCustomBasePath());
    }

    public function testGetCustomBasePathOnFacebook()
    {
        $context = new Context\Facebook();
        $this->assertEquals('', $context->getCustomBasePath());
    }
}
