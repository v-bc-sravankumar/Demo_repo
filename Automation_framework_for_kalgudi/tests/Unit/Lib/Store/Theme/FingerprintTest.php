<?php

namespace Unit\Lib\Store\Theme;

use \Store\Theme\Fingerprint as Fingerprint;
use \PHPUnit_Framework_TestCase;

class FingerprintTest extends PHPUnit_Framework_TestCase
{
    private $storeConfig;
    private $fingerprint;

    public function setUp()
    {
        $this->storeConfig = $this->getMock('Store_Settings', array(
            'get',
            'schedule',
            'commit',
        ));
        $this->fingerprint = new Fingerprint();
        $this->fingerprint->setStoreConfig($this->storeConfig);
    }

    public function nonCustomAssetProvider()
    {
        return array(
            array('template/header.html'),
            array('templates/__custom/header.html'),
            array('product_download/styles.css'),
        );
    }

    /**
     * @dataProvider nonCustomAssetProvider
     */
    public function testSetNonCustomAsset($path)
    {
        $this->storeConfig
            ->expects($this->never())
            ->method('schedule');
        $this->storeConfig
            ->expects($this->never())
            ->method('commit');

        $this->assertTrue($this->fingerprint->set($path));
    }

    public function customAssetProvider()
    {
        return array(
            array('template/styles.css', 'templates/__custom/styles.css'),
            array('template/common.js', 'templates/__custom/common.js'),
            array('template/image.png', 'templates/__custom/image.png'),
            array('template/image.gif', 'templates/__custom/image.gif'),
            array('mobile_template/image.jpeg', 'templates/__custommobile/image.jpeg'),
            array('templates/__custommobile/image.jpg', 'templates/__custommobile/image.jpg'),
            array('templates/__custom/styles.css', 'templates/__custom/styles.css'),
            array('templates/__custommobile/styles.css', 'templates/__custommobile/styles.css'),
        );
    }

    /**
     * @dataProvider customAssetProvider
     */
    public function testSetCustomAsset($path, $expected)
    {
        $this->storeConfig
            ->expects($this->once())
            ->method('schedule')
            ->with('CdnStoreThemeAssetFingerprint');
        $this->storeConfig
            ->expects($this->once())
            ->method('commit')
            ->will($this->returnValue(true));

        $this->assertTrue($this->fingerprint->set($path));
    }

    /**
     * @dataProvider nonCustomAssetProvider
     */
    public function testGetNonCustomAsset($path)
    {
        $this->assertFalse($this->fingerprint->get($path));
    }

    /**
     * @dataProvider customAssetProvider
     */
    public function testGetCustomAsset($path, $expected)
    {
        $this->storeConfig
            ->expects($this->once())
            ->method('get')
            ->with('CdnStoreThemeAssetFingerprint')
            ->will($this->returnValue(12345));

        $this->assertEquals(12345, $this->fingerprint->get($path));
    }

    public function testNoMinimumTimestamp()
    {
        $this->storeConfig
             ->expects($this->once())
             ->method('get')
             ->with('CdnStoreThemeAssetFingerprint')
             ->will($this->returnValue(12345));

        $redis  = $this->getMock('Predis\Client');
        $statsd = $this->getMock('Store_Statsd');

        $fingerprint = new Fingerprint(
            $redis,
            $statsd,
            'foo',
            null
        );

        $fingerprint->setStoreConfig($this->storeConfig);

        $this->assertEquals(12345, $fingerprint->get('templates/__custom/styles.css'));
    }

    public function testMinimumTimestamp()
    {
        $this->storeConfig
             ->expects($this->once())
             ->method('get')
             ->with('CdnStoreThemeAssetFingerprint')
             ->will($this->returnValue(12345));

        $redis  = $this->getMock('Predis\Client');
        $statsd = $this->getMock('Store_Statsd');

        $fingerprint = new Fingerprint(
            $redis,
            $statsd,
            'foo',
            23456
        );

        $fingerprint->setStoreConfig($this->storeConfig);

        $this->assertEquals(23456, $fingerprint->get('templates/__custom/styles.css'));
    }

    public function testFingerprintExceedsMinimumTimestamp()
    {
        $this->storeConfig
             ->expects($this->once())
             ->method('get')
             ->with('CdnStoreThemeAssetFingerprint')
             ->will($this->returnValue(34567));

        $redis  = $this->getMock('Predis\Client');
        $statsd = $this->getMock('Store_Statsd');

        $fingerprint = new Fingerprint(
            $redis,
            $statsd,
            'foo',
            23456
        );

        $fingerprint->setStoreConfig($this->storeConfig);

        $this->assertEquals(34567, $fingerprint->get('templates/__custom/styles.css'));
    }
}
