<?php

namespace Unit\Lib\Store\Theme\Custom\Hooks;

use \PHPUnit_Framework_TestCase;
use \Store_Config;
use \Store\Theme\Custom\Hooks\StartVersioning as Hook;

class StartVersioningTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Store_Config::override('Feature_ThemeVersioning', true);
    }

    public function tearDown()
    {
        Store_Config::revert('Feature_ThemeVersioning');
    }

    private function mockVersion($hasCustomization, $versionCount, $startTracking)
    {
        $assetMock = $this->getMock('\Store\Theme\Asset', array('hasCustomization'));
        $assetMock->expects($this->any())
            ->method('hasCustomization')
            ->will($this->returnValue($hasCustomization));

        $versionMock = $this->getMock('\Store\Theme\Version',
            array('count', 'getAsset', 'setContext', 'startTracking'));

        $versionMock->expects($this->any())
            ->method('count')
            ->will($this->returnValue($versionCount));
        $versionMock->expects($this->any())
            ->method('getAsset')
            ->will($this->returnValue($assetMock));
        $versionMock->expects($this->any())
            ->method('setContext');
        $versionMock->expects($this->exactly($startTracking))
            ->method('startTracking');

        return $versionMock;
    }

    public function noCustomDataProvider()
    {
        return array(
            array('content'),
            array('product_images'),
            array('product_downloads'),
        );
    }

    public function customDataProvider()
    {
        return array(
            array('template/'),
            array('templates/__custom'),
            array('mobile_template/'),
            array('templates/__custommobile'),
        );
    }

    /**
     * @dataProvider noCustomDataProvider
     */
    public function testOnNonCustomPath($path)
    {
        $hook = new Hook($this->mockVersion(true, 0, 0));
        $hook->run($path);
    }

    /**
     * @dataProvider customDataProvider
     */
    public function testOnCustomPathWhenFeatureOff($path)
    {
        Store_Config::override('Feature_ThemeVersioning', false);
        $hook = new Hook($this->mockVersion(true, 0, 0));
        $hook->run($path);
    }

    /**
     * @dataProvider customDataProvider
     */
    public function testOnCustomPathWithoutCustomizationFirstTime($path)
    {
        $hook = new Hook($this->mockVersion(false, 0, 0));
        $hook->run($path);
    }

    /**
     * @dataProvider customDataProvider
     */
    public function testOnCustomPathWithCustomizationFirstTime($path)
    {
        $hook = new Hook($this->mockVersion(true, 0, 1));
        $hook->run($path);
    }

    /**
     * @dataProvider customDataProvider
     */
    public function testOnCustomPathWithCustomizationAlreadyVersioned($path)
    {
        $hook = new Hook($this->mockVersion(true, 1, 0));
        $hook->run($path);
    }
}
