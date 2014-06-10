<?php

namespace Unit\Controllers;

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStream;
use Store_Config;

class StaticAssetControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $filesystem = array(
            'root' => array(
                'index.php' => 'hello',
                'ftp_root' => array(
                    'content' => array(
                        'foo.jpg' => 'hello',
                    ),
                ),
                'product_images' => array(
                    'foo.jpg' => 'hello',
                 ),
                'templates' => array(
                    '__custom' => array(
                        'foo.jpg' => 'hello',
                        'bar.css' => 'hello',
                    ),
                ),
            ),
        );

        vfsStream::setup('/', 0755, $filesystem);
    }

    public function testIndexPhpIsNotStreamable()
    {
        $mock = $this->mockController();
        $this->assertFalse($mock->isStreamableAsset('index.php'));
    }

    public function testProductImageIsStreamable()
    {
        $mock = $this->mockController();
        $this->assertTrue($mock->isStreamableAsset('product_images/foo.jpg'));
    }

    public function testCustomAssetIsStreamable()
    {
        $mock = $this->mockController();
        $this->assertTrue($mock->isStreamableAsset('/templates/__custom/foo.jpg'));
        $this->assertTrue($mock->isStreamableAsset('templates/__custom/foo.jpg'));
    }

    public function testStreamExistingFile()
    {
        $params = array('asset' => 'product_images/foo.jpg');

        $mock = $this->mockController();
        $mock->expects($this->atLeastOnce())
             ->method('streamAsset')
             ->with(vfsStream::url('root/product_images/foo.jpg'), $this->arrayHasKey('Last-Modified'));

        $this->assertTrue($mock->streamAction($params));
    }

    public function testStreamNonExistentFile()
    {
        $params = array('asset' => 'totally-not-an-asset.jpg');

        $mock = $this->mockController();
        $mock->expects($this->never())
             ->method('streamAsset');

        $this->assertFalse($mock->streamAction($params));
    }

    public function provideSymlinkedAssets()
    {
        $data = array();

        // paths which should be adjusted
        $data[] = array("/content/foo.jpg", "/ftp_root/content/foo.jpg");
        $data[] = array("/template/foo.jpg", "/templates/__custom/foo.jpg");

        // for sanity, paths which should not be adjusted
        $data[] = array("/templates/__custom/foo.jpg", "/templates/__custom/foo.jpg");
        $data[] = array("/product_images/foo.jpg", "/product_images/foo.jpg");

        return $data;
    }

    /**
     * @dataProvider provideSymlinkedAssets
     */
    public function testStreamSymlinkedAssets($request, $asset)
    {
        $controller = $this->mockController();

        $controller->expects($this->once())
                   ->method('streamAsset')
                   ->with(vfsStream::url("root/$asset"));

        $controller->streamAction(array('asset' => $request));
    }

    public function testStreamNonCssAsset()
    {
        $controller = $this->mockController();
        $controller->expects($this->once())
            ->method('streamAsset');

        $controller->streamAction(array('asset' => 'templates/__custom/foo.jpg'));
    }

    public function testStreamCssAsset()
    {
        $controller = $this->mockController();
        $controller->expects($this->once())
            ->method('streamRewriteCss');

        Store_Config::override('Feature_CdnStoreAsset', true);
        Store_Config::override('Feature_CdnStoreCss', true);

        $controller->streamAction(array('asset' => '/templates/__custom/bar.css'));

        Store_Config::revert('Feature_CdnStoreAsset');
        Store_Config::revert('Feature_CdnStoreCss');
    }

    public function testGetBasicHeaders()
    {
        $controller = new \Store\Controllers\StaticAssetController();
        $headers = $controller->getBasicHeaders("vfs://root/templates/__custom/bar.css");

        $this->assertArrayHasKey('Last-Modified', $headers);
        $this->assertArrayHasKey('Access-Control-Max-Age', $headers);
        $this->assertEquals('*', $headers['Access-Control-Allow-Origin']);
    }

    protected function mockController()
    {
        $mock = $this->getMock(
            'Store\Controllers\StaticAssetController',
            array(
                'generatePath',
                'streamAsset',
                'streamRewriteCss',
            )
        );

        $mock->expects($this->any())
             ->method('generatePath')
             ->will($this->returnCallback(function($path){
                 return vfsStream::url("root/$path");
             }));

        return $mock;
    }
}
