<?php

namespace Unit\Lib\Store\Theme\Custom\Hooks;

use PHPUnit_Framework_TestCase;
use Store\Theme\Custom\Hooks\MonitorAssetUrl as Hook;
use org\bovigo\vfs\vfsStream;
use Store_Config;

class MonitorAssetUrlTest extends PHPUnit_Framework_TestCase
{
    private function setupCustomCss($content)
    {
        $structure = array(
            '__custom' => array(
                'Styles' => array(
                    'foo.css' => $content,
                ),
            ),
        );
        vfsStream::setup('templates', null, $structure);
    }

    private function mockHook($config, $statsd)
    {
        $mock = $this->getMockBuilder('\Store\Theme\Custom\Hooks\MonitorAssetUrl')
            ->setMethods(array(
                'getStylesheetPath',
                'backupDirtyFiles',
            ))
            ->setConstructorArgs(array($config, $statsd))
            ->getMock();

        $mock->expects($this->any())
            ->method('getStylesheetPath')
            ->will($this->returnCallback(function($filename) {
                return "vfs://templates/__custom/Styles/{$filename}";
            }));

        return $mock;
    }

    private function mockUnusedStatsd()
    {
        $mock = $this->getMock('\Store_Statsd', array('increment', 'count'));
        $mock->expects($this->never())
            ->method('increment');
        $mock->expects($this->never())
            ->method('count');

        return $mock;
    }

    private function mockUsedStatsd($count)
    {
        $mock = $this->getMock('\Store_Statsd', array('increment', 'count'));
        $mock->expects($this->once())
            ->method('increment');
        $mock->expects($this->once())
            ->method('count')
            ->with($this->anything(), $count);

        return $mock;
    }

    private function mockNeverSaveConfig()
    {
        $mock = $this->getMock('\Store_Settings', array('get', 'schedule'));
        $mock->expects($this->never())
            ->method('schedule');

        return $mock;
    }

    private function mockSaveConfig()
    {
        $mock = $this->getMock('\Store_Settings', array('get', 'schedule', 'commit'));
        $mock->expects($this->once())
            ->method('commit');

        return $mock;
    }

    private function mockGetOnlyConfig($value)
    {
        $mock = $this->mockNeverSaveConfig();
        $mock->expects($this->atLeastOnce())
            ->method('get')
            ->with('CustomCssCdnOffenders')
            ->will($this->returnValue($value));

        return $mock;
    }

    private function mockMonitor($currentOffenders, $expectedOffenders, $statsd)
    {
        $config = $this->mockSaveConfig();
        $config->expects($this->atLeastOnce())
            ->method('get')
            ->with('CustomCssCdnOffenders')
            ->will($this->returnValue($currentOffenders));
        $config->expects($this->once())
            ->method('schedule')
            ->with('CustomCssCdnOffenders', $expectedOffenders);

        return $this->mockHook($config, $statsd);
    }

    public function testEnsureOffendersAlreadyExists()
    {
        $config = $this->mockGetOnlyConfig(array());
        $hook = $this->mockHook($config, $this->mockUnusedStatsd());
        $hook->ensureOffenders();
    }

    public function testEnsureOffendersInitialScanNoOffender()
    {
        $this->assertEnsureOffendersInitialScan("url(%%ASSET_/Styles/foo.css%%);", array());
    }

    public function testEnsureOffendersInitialScanFoundOffender()
    {
        $this->assertEnsureOffendersInitialScan("url(/content/foo.bar);",
            array('foo.css' => 'scan'));
    }

    private function assertEnsureOffendersInitialScan($content, $expectedOffenders)
    {
        $this->setupCustomCss($content);
        $hook = $this->mockMonitor(false, $expectedOffenders, $this->mockUnusedStatsd());
        $hook->ensureOffenders();
    }

    public function notMonitoredPathProvider()
    {
        return array(
            array('content/foo.css'),
            array('template/foo.css'),
            array('template/Styles/foo.bar'),
            array('template/Styles/my/foo.css'),
        );
    }

    /**
     * @dataProvider notMonitoredPathProvider
     */
    public function testSkipMonitor($path)
    {
        $hook = $this->mockHook($this->mockNeverSaveConfig(), $this->mockUnusedStatsd());
        $hook->monitorFileSaved($path);
    }

    public function offenderUrlProvider()
    {
        return array(
            array("url(/content/images/foo.bar);"),
            array("url(/product_images/images/foo.bar);"),
            array("url(../../../../images/foo.bar);"),
            array("url(/templates/Classic/images/foo.bar);"),
            array("url(../../templates/__custom/images/foo.bar);"),
            array("url(../../template/images/foo.bar);"),
            array("url(/template/images/foo.jpg)"),
            array("url(\"/template/images/foo.jpg\")"),
            array("url('/template/images/foo.jpg')"),
            array("url(/templates/__custom/my/images/bar.jpg)"),
            array("url(/templates/__custom/Styles/bar.ttf)"),
            array("url(../../../templates/__custom/Styles/bar.ttf)"),
            array("url(../../../template/font/bar.ttf)"),
            array("url(../../../../../template/font/bar.ttf)"),
            array("url(../images/foo.jpg)"),
            array("url(foo.jpg)"),
            array("url(./foo.jpg)"),
            array("url(images/foo.jpg)"),
            array("url(./images/foo.jpg)"),
        );
    }

    /**
     * @dataProvider offenderUrlProvider
     */
    public function testMonitorFoundNewOffender($content)
    {
        $this->assertMonitorFileSaved($content, array(), array('foo.css' => 'monitor'), 1);
    }

    /**
     * @dataProvider offenderUrlProvider
     */
    public function testMonitorFoundExistingOffender($content)
    {
        $this->assertMonitorFileSaved($content, array('foo.css' => 'scan'),
            array('foo.css' => 'monitor'), 1);
    }

    public function cdnCompatibleUrlProvider()
    {
        return array(
            array('background: url(%%GLOBAL_TPL_PATH%%/images/foo.jpg%%)'),
            array('background: url(%%ASSET_images/foo.jpg%%)'),
            array('background: url(%%ASSET_Styles/bar.font%%)'),
            array('background: url(../../Classic/images/foo.jpg)'),
            array('background: url(../../__mobilev2/Styles/bar.css)'),
            array('background: url(http://example.com/images/foo.jpg)'),
            array('background: url(https://example.com/Styles/bar.css)'),
            array('background: url(//example.com/Styles/bar.css)'),
        );
    }

    /**
     * @dataProvider cdnCompatibleUrlProvider
     */
    public function testMonitorRemoveExistingOffenderOnSaved($content)
    {
        $this->assertMonitorFileSaved($content, array('foo.css' => 'scan'), array(), 0);
    }

    /**
     * @dataProvider cdnCompatibleUrlProvider
     */
    public function testMonitorRemoveNonOffenderOnSaved($content)
    {
        $this->assertMonitorFileSaved($content, array(), array(), 0);
    }

    public function testMonitorRemoveOffenderOnDeleted()
    {
        $hook = $this->mockMonitor(array("foo.css" => "scna"), array(), $this->mockUsedStatsd(0));
        $hook->monitorFileDeleted("template/Styles/foo.css");
    }

    private function assertMonitorFileSaved($content, $currentOffenders,
        $expectedOffenders, $count)
    {
        $this->setupCustomCss($content);
        $hook = $this->mockMonitor($currentOffenders, $expectedOffenders,
            $this->mockUsedStatsd($count));
        $hook->monitorFileSaved("template/Styles/foo.css");
    }

}
