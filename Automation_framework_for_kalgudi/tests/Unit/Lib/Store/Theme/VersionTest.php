<?php

namespace Unit\Lib\Store\Theme;

use \Store_Config;
use \Store\Theme\Context as Context;
use \Store\Theme\Version as Version;
use \PHPUnit_Framework_TestCase;

class VersionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Store_Config::override('Feature_ThemeVersioning', true);
        Store_Config::override('ThemeVersionHistoryLength', 2);
    }

    public function tearDown()
    {
        Store_Config::revert('Feature_ThemeVersioning');
        Store_Config::revert('ThemeVersionHistoryLength');
        Store_Config::revert('ThemeVersionDesktop');
        Store_Config::revert('ThemeVersionMobile');
        Store_Config::revert('ThemeVersionFacebook');
    }

    public function testLoadEmpty()
    {
        Store_Config::override('ThemeVersionDesktop', false);
        $version = new Version();
        $this->assertEquals(0, $version->count());
    }

    public function testLoadNonArray()
    {
        Store_Config::override('ThemeVersionDesktop', 'test-version');
        $version = new Version();
        $this->assertEquals(1, $version->count());
    }

    public function testLoadArray()
    {
        Store_Config::override('ThemeVersionDesktop', array('v2', 'v1'));
        $version = new Version();
        $this->assertEquals(2, $version->count());
    }

    public function testEmptyCurrentVersion()
    {
        Store_Config::override('ThemeVersionDesktop', array());
        $version = new Version();
        $this->assertFalse($version->current());
    }

    public function testCurrentVersionOnDesktop()
    {
        Store_Config::override('ThemeVersionDesktop', array('v2', 'v1'));
        $version = new Version();
        $this->assertEquals('v2', $version->current());
    }

    public function testCurrentVersionOnMobile()
    {
        Store_Config::override('ThemeVersionMobile', array('m2', 'm1'));
        $version = new Version(new Context\Mobile());
        $this->assertEquals('m2', $version->current());
    }

    public function testCurrentVersionOnFacebook()
    {
        Store_Config::override('ThemeVersionFacebook', array('f2', 'f1'));
        $version = new Version(new Context\Facebook());
        $this->assertEquals('f2', $version->current());
    }

    public function testEmptyLastVersion()
    {
        Store_Config::override('ThemeVersionDesktop', array('v1'));
        $version = new Version();
        $this->assertFalse($version->last());
    }

    public function testLastVersionOnDesktop()
    {
        Store_Config::override('ThemeVersionDesktop', array('v2', 'v1'));
        $version = new Version();
        $this->assertEquals('v1', $version->last());
    }

    public function testLastVersionOnMobile()
    {
        Store_Config::override('ThemeVersionMobile', array('m2', 'm1'));
        $version = new Version(new Context\Mobile());
        $this->assertEquals('m1', $version->last());
    }

    public function testLastVersionOnFacebook()
    {
        Store_Config::override('ThemeVersionFacebook', array('f2', 'f1'));
        $version = new Version(new Context\Facebook());
        $this->assertEquals('f1', $version->last());
    }

    public function testLastVersionWithLongHistory()
    {
        Store_Config::override('ThemeVersionDesktop', array('v2', 'v1', 'v0'));
        $version = new Version();
        $this->assertEquals('v1', $version->last());
    }

    public function testHasUpgradeWhenVersionNotSet()
    {
        Store_Config::override('ThemeVersionDesktop', '');
        $version = new Version();
        $version->setAsset($this->getMockAsset());
        $this->assertFalse($version->hasUpgrade());
    }

    public function testHasUpgradeWithVersionSetToHead()
    {
        Store_Config::override('ThemeVersionDesktop', 'head');
        $version = new Version();
        $version->setAsset($this->getMockAsset());
        $this->assertFalse($version->hasUpgrade());
    }

    public function testHasUpgradeWhenVersionSetToOld()
    {
        Store_Config::override('ThemeVersionDesktop', array('old'));
        $version = new Version();
        $version->setAsset($this->getMockAsset());
        $this->assertTrue($version->hasUpgrade());
    }

    public function testUpgradeWhenNotAvailable()
    {
        Store_Config::override('ThemeVersionDesktop', array());
        $version = new Version();
        $this->assertFalse($version->hasUpgrade());
        $this->assertFalse($version->upgrade());
    }

    public function testUpgradeSuccess()
    {
        Store_Config::override('ThemeVersionDesktop', array('v0'));
        $version = new Version();

        $version->setAsset($this->getMockAsset('v1'));
        $version->setStoreConfig($this->getMockStoreConfig(array('v1', 'v0')));
        $this->assertTrue($version->upgrade());
        $this->assertEquals('v1', $version->current());
        $this->assertEquals('v0', $version->last());
        $this->assertEquals(2, $version->count());

        $version->setAsset($this->getMockAsset('v2'));
        $version->setStoreConfig($this->getMockStoreConfig(array('v2', 'v1')));
        $this->assertTrue($version->upgrade());
        $this->assertEquals('v2', $version->current());
        $this->assertEquals('v1', $version->last());
        $this->assertEquals(2, $version->count());
    }

    public function testStartTrackingWhenAlreadyTracked()
    {
        Store_Config::override('ThemeVersionDesktop', array('v1'));
        $version = new Version();
        $this->assertFalse($version->startTracking());
    }

    public function testStartTrackingSuccess()
    {
        Store_Config::override('ThemeVersionDesktop', array());
        $version = new Version();

        $version->setAsset($this->getMockAsset('v1'));
        $version->setStoreConfig($this->getMockStoreConfig(array('v1')));
        $this->assertTrue($version->startTracking());
        $this->assertEquals('v1', $version->current());
        $this->assertFalse($version->last());
        $this->assertEquals(1, $version->count());
    }

    private function getMockAsset($head = 'head')
    {
        $asset = $this->getMock('Asset', array('getHeadVersion'));
        $asset->expects($this->any())
            ->method('getHeadVersion')
            ->will($this->returnValue($head));

        return $asset;
    }

    public function testCanDowngradeWithoutHistory()
    {
        Store_Config::override('ThemeVersionDesktop', array());
        $version = new Version();
        $this->assertFalse($version->canDowngrade());

        Store_Config::override('ThemeVersionDesktop', array('v1'));
        $version = new Version();
        $this->assertFalse($version->canDowngrade());
    }

    public function testCanDowngradeWithHistory()
    {
        Store_Config::override('ThemeVersionDesktop', array('v2', 'v1'));
        $version = new Version();
        $this->assertTrue($version->canDowngrade());
    }

    public function testDowngradeWithNotEnoughHistory()
    {
        Store_Config::override('ThemeVersionDesktop', array('v1'));
        $version = new Version();
        $this->assertFalse($version->canDowngrade());
        $this->assertFalse($version->downgrade());
    }

    public function testDowngradeSuccess()
    {
        Store_Config::override('ThemeVersionDesktop', array('v3', 'v2', 'v1'));
        $version = new Version();

        $version->setStoreConfig($this->getMockStoreConfig(array('v2', 'v1')));
        $this->assertTrue($version->downgrade());
        $this->assertEquals('v2', $version->current());
        $this->assertEquals('v1', $version->last());

        $version->setStoreConfig($this->getMockStoreConfig(array('v1')));
        $this->assertTrue($version->downgrade());
        $this->assertEquals('v1', $version->current());
        $this->assertFalse($version->last());
    }

    public function testClear()
    {
        Store_Config::override('ThemeVersionDesktop', array('v1'));
        $version = new Version();

        $version->setStoreConfig($this->getMockStoreConfig(array()));
        $this->assertTrue($version->clear());
        $this->assertEquals(0, $version->count());
    }

    private function getMockStoreConfig($expectedVersion)
    {
        $config = $this->getMock('\Store_Settings', array('schedule', 'commit'));
        $config->expects($this->once())
            ->method('schedule')
            ->with(
                $this->equalTo('ThemeVersionDesktop'),
                $this->equalTo($expectedVersion)
            )
            ->will($this->returnValue($config));
        $config->expects($this->once())
            ->method('commit')
            ->will($this->returnValue(true));

        return $config;
    }
}
