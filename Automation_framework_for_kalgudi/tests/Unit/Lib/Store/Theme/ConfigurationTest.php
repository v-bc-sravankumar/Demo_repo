<?php

namespace Unit\Lib\Store\Theme;

use \Store_Config;
use \Store\Theme\Configuration as Configuration;
use \org\bovigo\vfs\vfsStream;
use \PHPUnit_Framework_TestCase;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Configuration();

        // point tenant home directory to vfs
        $this->config->getAsset()
            ->setContext(null);

        // set default versioning configs
        Store_Config::override('template', 'test-theme');
        Store_Config::override('Feature_ThemeVersioning', true);
        Store_Config::override('ThemeVersionDesktop', array());

        // intialize the virtual file system
        vfsStream::setup('test_theme_root');

        // default theme globals to compare as base line
        $GLOBALS['TPL_CFG']['ConfigDefaults']['width'] = 10;
        $GLOBALS['TPL_CFG']['ConfigDefaults']['height'] = 10;
    }

    public function tearDown()
    {
        Store_Config::revert('template');
        Store_Config::revert('Feature_ThemeVersioning');
        Store_Config::revert('ThemeVersionDesktop');
    }

    private function deployThemeWithConfig($theme, $configName, $configContent)
    {
        $root = vfsStream::url('test_theme_root');

        mkdir($root . "/{$theme}");
        mkdir($root . "/{$theme}/current");
        mkdir($root . "/{$theme}/current/config");

        file_put_contents("{$root}/{$theme}/{$configName}", $configContent);
    }

    public function configProvider()
    {
        return array (
            array (
                'current/config.json',
                '{"ConfigDefaults": {"width": 100, "height": 200}}',
            ),
            array (
                'current/config.php',
                "<?php \$GLOBALS['TPL_CFG']['ConfigDefaults']['width'] = 100;
                \$GLOBALS['TPL_CFG']['ConfigDefaults']['height'] = 200;",
            ),
        );
    }

    public function testLoadNotFound()
    {
        $this->assertNull($this->config->load('test-theme'));
    }

    /**
     * @dataProvider configProvider
     */
    public function testLoad($file, $data)
    {
        $this->deployThemeWithConfig('test-theme', $file, $data);

        $config = $this->config->load('test-theme');

        $this->assertEquals(100, $config['ConfigDefaults']['width']);
        $this->assertEquals(200, $config['ConfigDefaults']['height']);
        $this->assertEquals(100, $GLOBALS['TPL_CFG']['ConfigDefaults']['width']);
        $this->assertEquals(200, $GLOBALS['TPL_CFG']['ConfigDefaults']['height']);
    }

    /**
     * @dataProvider configProvider
     */
    public function testRead($file, $data)
    {
        $this->deployThemeWithConfig('test-theme', $file, $data);

        $config = $this->config->read('test-theme');

        $this->assertEquals(100, $config['ConfigDefaults']['width']);
        $this->assertEquals(200, $config['ConfigDefaults']['height']);
        $this->assertEquals(10, $GLOBALS['TPL_CFG']['ConfigDefaults']['width']);
        $this->assertEquals(10, $GLOBALS['TPL_CFG']['ConfigDefaults']['height']);
    }

    /**
     * @dataProvider configProvider
     */
    public function testApplyCurrentThemeDefaults($file, $data)
    {
        $config = $this->getMock('\Store_Settings', array('schedule', 'commit'));
        $config->expects($this->at(0))
            ->method('schedule')
            ->with($this->equalTo('width'), $this->equalTo('100'))
            ->will($this->returnValue($config));
        $config->expects($this->at(1))
            ->method('schedule')
            ->with($this->equalTo('height'), $this->equalTo('200'))
            ->will($this->returnValue($config));
        $config->expects($this->at(2))
            ->method('schedule')
            ->with($this->equalTo('depth'), $this->equalTo('30'))
            ->will($this->returnValue($config));
        $config->expects($this->once())
            ->method('commit')
            ->will($this->returnValue(true));
        $this->config->setStoreConfig($config);

        $this->deployThemeWithConfig('test-theme', $file, $data);
        $this->deployThemeWithConfig('__master', 'current/config.json',
            '{"ConfigDefaults": {"width": 10, "height": 20, "depth": 30}}');

        $this->config->applyCurrentThemeDefaults();
    }

    private function deployThemeWithPreviews($preview)
    {
        $root = vfsStream::url('test_theme_root');

        mkdir("{$root}/test-theme");
        mkdir("{$root}/test-theme/current");
        mkdir("{$root}/test-theme/current/Previews");

        file_put_contents("{$root}/test-theme/current/Previews/not-this", "img");
        file_put_contents("{$root}/test-theme/current/Previews/{$preview}", "img");
    }

    public function previewImageProvider()
    {
        return array(
            array('blue', 'blue.jpg'),
            array('green', 'green.png'),
            array('yellow', 'yellow.gif'),
            array('', 'responsive.png'),
        );
    }

    /**
     * @dataProvider previewImageProvider
     */
    public function testGetPreviewImage($color, $preview)
    {
        $this->deployThemeWithPreviews($preview);
        $result = $this->config->getPreviewImage('test-theme', $color);
        $this->assertEquals($preview, $result);
    }
}
