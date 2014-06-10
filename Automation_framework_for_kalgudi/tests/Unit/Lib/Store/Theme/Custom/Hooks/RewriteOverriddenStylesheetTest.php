<?php

namespace Unit\Lib\Store\Theme\Custom\Hooks;

use \PHPUnit_Framework_TestCase;
use \Store\Theme\Custom\Hooks\RewriteOverriddenStylesheets as Hook;
use \org\bovigo\vfs\vfsStream;

class RewriteOverriddenStylesheetsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $structure = array(
            'current' => array(
                'Styles' => array(
                    'foo.css' => 'background:url(../images/foo.jpg);',
                    'abs.css' => 'background:url(/template/images/abs.jpg);',
                ),
            ),
            'master' => array(
                'Styles' => array(
                    'bar.css' => 'background:url("../images/bar.png");',
                    'full.css' => 'background:url("http://store/template/images/abs.jpg");',
                ),
            ),
            '__custom' => array(
                'Styles' => array(
                    'foo.css' => 'background:url(../images/foo.jpg);',
                    'abs.css' => 'background:url(/template/images/abs.jpg);',
                    'bar.css' => 'background:url("../images/bar.png");',
                    'full.css' => 'background:url("http://store/template/images/abs.jpg");',
                ),
            ),
        );

        vfsStream::setup('templates', null, $structure);
    }

    public function nonStylePathProvider()
    {
        return array(
            array('template/Panels/foo.html'),
            array('template/Snippets/foo.html'),
            array('template/images/foo.jpg'),
            array('template/Styles/foo.notcss'),
            array('template/foo.css'),
        );
    }

    public function newStylePathProvider()
    {
        return array(
            array(
                'template/Styles/new.css',
                'Styles/new.css',
            ),
            array(
                'templates/__custom/Styles/new.css',
                'Styles/new.css',
            ),
        );
    }

    public function overriddenStylePathProvider()
    {
        return array(
            // override current theme
            array(
                'templates/__custom/Styles/foo.css',
                'Styles/foo.css',
                'background:url(%%GLOBAL_TPL_PATH%%/images/foo.jpg);',
            ),
            array(
                'templates/__custom/Styles/abs.css',
                'Styles/abs.css',
                'background:url(/template/images/abs.jpg);',
            ),
            // override master theme
            array(
                'templates/__custom/Styles/bar.css',
                'Styles/bar.css',
                'background:url("%%GLOBAL_TPL_PATH%%/images/bar.png");',
            ),
            array(
                'templates/__custom/Styles/full.css',
                'Styles/full.css',
                'background:url("http://store/template/images/abs.jpg");',
            ),
        );
    }

    /**
     * @dataProvider nonStylePathProvider
     */
    public function testDontRewriteNonStylePath($path)
    {
        $hook = new Hook(null, $this->mockUnusedAsset());
        $hook->run($path, true);
    }

    /**
     * @dataProvider overriddenStylePathProvider
     */
    public function testDontRewriteOnUpdate($path)
    {
        $hook = new Hook(null, $this->mockUnusedAsset());
        $hook->run($path, false);
    }

    /**
     * @dataProvider newStylePathProvider
     */
    public function testDontRewriteNotOverriddenStyle($path, $relativePath)
    {
        $hook = new Hook($this->mockUnusedConfig(), $this->mockAsset($relativePath));
        $hook->run($path, true);
    }

    /**
     * @dataProvider overriddenStylePathProvider
     */
    public function testRewriteOverriddenStyle($path, $relativePath, $expectedContent)
    {
        $hook = $this->mockhook($path, $relativePath);
        $hook->run($path, true);
        $this->assertEquals($expectedContent, file_get_contents("vfs://{$path}"));
    }

    private function mockUnusedConfig()
    {
        $config = $this->getMock('\Store_Settings', array('schedule', 'commit'));
        $config->expects($this->never())
            ->method('schedule');
        $config->expects($this->never())
            ->method('commit');

        return $config;
    }

    private function mockConfig()
    {
        $config = $this->getMock('\Store_Settings', array('schedule', 'commit'));
        $config->expects($this->once())
            ->method('schedule')
            ->with($this->equalTo('StreamCustomCss'), $this->equalTo(true));
        $config->expects($this->once())
            ->method('commit');

        return $config;
    }

    private function mockUnusedAsset()
    {
        $asset = $this->getMock('Asset', array('getCurrentThemePath'));
        $asset->expects($this->never())
            ->method('getCurrentThemePath');

        return $asset;
    }

    private function mockAsset($path)
    {
        $asset = $this->getMock('Asset', array('getCurrentThemePath', 'getMasterThemePath'));
        $asset->expects($this->once())
            ->method('getCurrentThemePath')
            ->with($this->equalTo($path))
            ->will($this->returnValue("vfs://templates/current/{$path}"));
        $asset->expects($this->any())
            ->method('getMasterThemePath')
            ->with($this->equalTo($path))
            ->will($this->returnValue("vfs://templates/master/{$path}"));

        return $asset;
    }

    private function mockHook($path, $relativePath)
    {
        $hook = $this->getMockBuilder('\Store\Theme\Custom\Hooks\RewriteOverriddenStylesheets')
            ->setConstructorArgs(array($this->mockConfig(), $this->mockAsset($relativePath)))
            ->setMethods(array('generateAssetPath'))
            ->getMock();

        $hook->expects($this->once())
            ->method('generateAssetPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue("vfs://{$path}"));

        return $hook;
    }
}
