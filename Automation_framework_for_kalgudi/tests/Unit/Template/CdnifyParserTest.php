<?php

namespace Tests\Unit\Template;

use \PHPUnit_Framework_TestCase;

if (!defined('PRODUCT_ID')) {
    // shut up, template class, I want to run you without init.php
    define('PRODUCT_ID', 'ISC');
}

class CdnifyParserTest extends PHPUnit_Framework_TestCase
{
    private function mockTemplate()
    {
        // disable constructor because it calls on GetConfig
        $mock = $this->getMockBuilder('\TEMPLATE')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function mockCustomThemeAsset($path)
    {
        $mock = $this->getMock('\Store\Theme\Asset', array('getCustomThemeURL'));
        $mock->expects($this->any())
            ->method('getCustomThemeURL')
            ->with($this->equalTo($path))
            ->will($this->returnValue("http://cdn-host/templates/__custom/{$path}"));

        return $mock;
    }

    private function mockCurrentThemeAsset($path)
    {
        $mock = $this->getMock('\Store\Theme\Asset', array('getCurrentThemeURL'));
        $mock->expects($this->any())
            ->method('getCurrentThemeURL')
            ->with($this->equalTo($path))
            ->will($this->returnValue("http://cdn-host/themes/{$path}"));

        return $mock;
    }

    public function globalBaseImagePathProvider() {
        return array(
            array(
                "<img src=\"%%GLOBAL_IMG_PATH%%/abc.jpg\" />",
                "images",
                "<img src=\"http://cdn-host/themes/images/abc.jpg\" />",
            ),
            array(
                "background:url(%%GLOBAL_IMG_PATH%%/abc.jpg)",
                "images",
                "background:url(http://cdn-host/themes/images/abc.jpg)",
            ),
            array(
                "<img src=\"%%GLOBAL_IMG_PATH%%/my/abc.jpg\" />",
                "images",
                "<img src=\"http://cdn-host/themes/images/my/abc.jpg\" />",
            ),
        );
    }

    /**
     * @dataProvider globalBaseImagePathProvider
     */
    public function testParseImagePathWithBaseTheme($html, $path, $expected)
    {
        $template = $this->mockTemplate();
        $template->hasCustomImages = false;
        $template->themeAsset = $this->mockCurrentThemeAsset($path);
        $this->assertEquals($expected, $template->ParseImagePath($html));
    }

    public function globalCustomImagePathProvider()
    {
        return array(
            array(
                "<img src=\"%%GLOBAL_IMG_PATH%%/abc.jpg\" />",
                "images/abc.jpg",
                "<img src=\"http://cdn-host/templates/__custom/images/abc.jpg\" />",
            ),
            array(
                "background:url(%%GLOBAL_IMG_PATH%%/abc.jpg)",
                "images/abc.jpg",
                "background:url(http://cdn-host/templates/__custom/images/abc.jpg)",
            ),
            array(
                "<img src=\"%%GLOBAL_IMG_PATH%%/my/abc.jpg\" />",
                "images/my/abc.jpg",
                "<img src=\"http://cdn-host/templates/__custom/images/my/abc.jpg\" />",
            ),
            // not supported image type
            array(
                "<img src=\"%%GLOBAL_IMG_PATH%%/abc.tiff\" />",
                "images",
                "<img src=\"%%GLOBAL_IMG_PATH%%/abc.tiff\" />",
            ),
        );
    }

    /**
     * @dataProvider globalCustomImagePathProvider
     */
    public function testParseImagePathWithCustomTheme($html, $path, $expected)
    {
        $template = $this->mockTemplate();
        $template->hasCustomImages = true;
        $template->themeAsset = $this->mockCustomThemeAsset($path);
        $this->assertEquals($expected, $template->ParseImagePath($html));

    }

    public function testParseUploadedImagesWhenCdnDisabled()
    {
        \Store_Config::override('Feature_CdnStoreAsset', false);

        $template = $this->mockTemplate();
        $html = "<img src=\"product_images/uploaded_images/abc.jpg\" />";
        $this->assertEquals($html, $template->ParseUploadedImages($html));

        \Store_Config::revert('Feature_CdnStoreAsset');
    }

    public function uploadedImagesPathProvider()
    {
        return array(
            // fully qualified uploaded image path
            array(
                "<img src=\"http://store-host/product_images/uploaded_images/abc.jpg\" />",
                "/product_images/uploaded_images/abc.jpg",
                "<img src=\"http://cdn-host/product_images/uploaded_images/abc.jpg\" />"
            ),
            array(
                "<img src='http://store-host/product_images/uploaded_images/abc.jpg' />",
                "/product_images/uploaded_images/abc.jpg",
                "<img src='http://cdn-host/product_images/uploaded_images/abc.jpg' />"
            ),
            // relative uploaded image path
            array(
                "<img src=\"/product_images/uploaded_images/abc.jpg\" />",
                "/product_images/uploaded_images/abc.jpg",
                "<img src=\"http://cdn-host/product_images/uploaded_images/abc.jpg\" />"
            ),
            array(
                "<img src='/product_images/uploaded_images/abc.jpg' />",
                "/product_images/uploaded_images/abc.jpg",
                "<img src='http://cdn-host/product_images/uploaded_images/abc.jpg' />"
            ),
            // non-uploaded image path, should have no effect
            array(
                "<img src=\"/product_images/abc.jpg\" />",
                "/product_images/abc.jpg",
                "<img src=\"/product_images/abc.jpg\" />",
            ),
            // not our store host, should have no effect
            array(
                "<img src=\"http://somewhereelse.com/product_images/abc.jpg\" />",
                "/product_images/abc.jpg",
                "<img src=\"http://somewhereelse.com/product_images/abc.jpg\" />",
            ),
        );
    }

    private function mockFingerprint($path)
    {
        $mock = $this->getMockBuilder('\Store\Theme\Fingerprint')
            ->disableOriginalConstructor()
            ->setMethods(array('generateUrl'))
            ->getMock();

        $mock->expects($this->any())
            ->method('generateUrl')
            ->with($this->equalTo($path))
            ->will($this->returnValue("http://cdn-host{$path}"));

        return $mock;
    }

    /**
     * @dataProvider uploadedImagesPathProvider
     */
    public function testParseUploadedImage($html, $path, $expected)
    {
        \Store_Config::override('Feature_CdnStoreAsset', true);
        \Store_Config::override('ImageDirectory', "product_images");
        \Store_Config::override('ShopPath', "http://store-host");

        $template = $this->mockTemplate();
        $template->fingerprint = $this->mockFingerprint($path);
        $this->assertEquals($expected, $template->ParseUploadedImages($html));

        \Store_Config::revert('ShopPath');
        \Store_Config::revert('ImageDirectory');
        \Store_Config::revert('Feature_CdnStoreAsset');
    }

}
