<?php

namespace Unit\Utilities;

use \Store_Config as Config;
use \Store_Settings as Settings;
use \Store_Settings_Driver_Dummy as DummyDriver;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    public function testBrandLink_AllBrands()
    {
        $dummy = new DummyDriver(array('ShopPathNormal'   => 'http://foo.com'));
        $settings = $this->getMock('\Store_Settings', array('get'), array($dummy));
        $settings->expects($this->exactly(2))
            ->method('get')
            ->with('ShopPathNormal')
            ->will($this->returnValue('http://foo.com'));

        $request = new \Interspire_Request();

        $links = new \Utilities\Links($settings, $request);
        $this->assertEquals('http://foo.com/brands/', $links->brandLink(''));
        $this->assertEquals('http://foo.com/brands/', $links->brandLink(null));
    }

    public function testBrandLink_SpecificBrand()
    {
        $dummy = new DummyDriver(array('ShopPathNormal'   => 'http://foo.com'));
        $settings = $this->getMock('\Store_Settings', array('get'), array($dummy));
        $settings->expects($this->any())
            ->method('get')
            ->with('ShopPathNormal')
            ->will($this->returnValue('http://foo.com'));

        $request = new \Interspire_Request();

        $links = new \Utilities\Links($settings, $request);
        $this->assertEquals('http://foo.com/brands/I-am-a-brand.html', $links->brandLink('I am a brand'));
        $this->assertEquals('http://foo.com/brands/Machalani-%26-Sons.html', $links->brandLink('Machalani & Sons'));

        $this->assertEquals('http://foo.com/brands/I-am-a-brand.html?a=b&amp;c=d', $links->brandLink('I am a brand', array('a' => 'b', 'c' => 'd')));
        $this->assertEquals('http://foo.com/brands/I-am-a-brand.html?a=b&amp;c=d', $links->brandLink('I am a brand', array('a' => 'b', 'c' => 'd'), true));
        $this->assertEquals('http://foo.com/brands/I-am-a-brand.html?a=b&c=d', $links->brandLink('I am a brand', array('a' => 'b', 'c' => 'd'), false));
    }

    public function testhomePageCanonicalLink()
    {
        $config = array('Feature_CanonicalLink' => false, 'ShopPathNormal' => 'http://foo.com/');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $request = new \Interspire_Request();

        $links = new \Utilities\Links($settings, $request);
        $this->assertNull($links->homePageCanonicalLink());
    }


    public function testhomePageCanonicalLink_Success()
    {
        $config = array('Feature_CanonicalLink' => true, 'ShopPathNormal' => 'http://foo.com/');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $request = new \Interspire_Request();

        $links = new \Utilities\Links($settings, $request);
        $this->assertEquals('http://foo.com/', $links->homePageCanonicalLink());
    }
}