<?php

namespace Unit\Settings;

use Store_Settings_Driver_Dummy;
use Store_Settings;
use Store_Config;
use PHPUnit_Framework_TestCase;

class SettingsTest extends PHPUnit_Framework_TestCase
{
    /** @var Store_Settings_Driver_Dummy */
    protected $_driver;

    /** @var Store_Settings */
    protected $_settings;

    public function setUp ()
    {
        $this->_driver = new Store_Settings_Driver_Dummy();
        $this->_settings = new Store_Settings();
        $this->_settings->setDriver($this->_driver);
    }

    public function testGetServiceDriverFromEnvironment()
    {
        $settings = $this->getMock('Store_Settings', array('getTenantHome', 'getServiceFlag', 'getEnvStoreId'));

        $settings->expects($this->atLeastOnce())
                 ->method('getEnvStoreId')
                 ->will($this->returnValue('1234'));

        $driver = $settings->getDriver();
        $this->assertInstanceOf('Store_Settings_Driver_Service', $driver);
        $this->assertSame(1234, $driver->getStoreId());
        $this->assertSame('http://127.0.0.1:83', $driver->getHost());
    }

    public function testGetServiceDriverFromEnvironmentWithHostInFlagFile()
    {
        $settings = $this->getMock('Store_Settings', array('getTenantHome', 'getServiceFlag', 'getEnvStoreId'));

        $settings->expects($this->atLeastOnce())
                 ->method('getEnvStoreId')
                 ->will($this->returnValue('1234'));

        $serviceFlag = array(
            'host'    => 'http://config-service',
        );

        $settings->expects($this->atLeastOnce())
                 ->method('getServiceFlag')
                 ->will($this->returnValue($serviceFlag));

        $driver = $settings->getDriver();
        $this->assertInstanceOf('Store_Settings_Driver_Service', $driver);
        $this->assertSame(1234, $driver->getStoreId());
        $this->assertSame('http://config-service', $driver->getHost());
    }

    public function testGetServiceDriverFromFlag()
    {
        $settings = $this->getMock('Store_Settings', array('getTenantHome', 'getServiceFlag', 'getEnvStoreId'));

        $settings->expects($this->atLeastOnce())
                 ->method('getEnvStoreId')
                 ->will($this->returnValue(false));

        $serviceFlag = array(
            'storeId' => '1234',
        );

        $settings->expects($this->atLeastOnce())
                 ->method('getServiceFlag')
                 ->will($this->returnValue($serviceFlag));

        $driver = $settings->getDriver();
        $this->assertInstanceOf('Store_Settings_Driver_Service', $driver);
        $this->assertSame(1234, $driver->getStoreId());
        $this->assertSame('http://127.0.0.1:83', $driver->getHost());
    }

    public function testGetServiceDriverFromFlagWithHost()
    {
        $settings = $this->getMock('Store_Settings', array('getTenantHome', 'getServiceFlag', 'getEnvStoreId'));

        $settings->expects($this->atLeastOnce())
                 ->method('getEnvStoreId')
                 ->will($this->returnValue(false));

        $serviceFlag = array(
            'storeId' => '1234',
            'host'    => 'http://config-service',
        );

        $settings->expects($this->atLeastOnce())
                 ->method('getServiceFlag')
                 ->will($this->returnValue($serviceFlag));

        $driver = $settings->getDriver();
        $this->assertInstanceOf('Store_Settings_Driver_Service', $driver);
        $this->assertSame(1234, $driver->getStoreId());
        $this->assertSame('http://config-service', $driver->getHost());
    }

    public function testGetLocalDriver()
    {
        $settings = $this->getMock('Store_Settings', array('getTenantHome', 'getServiceFlag', 'getEnvStoreId'));
        $driver   = $settings->getDriver();

        $this->assertInstanceOf('Store_Settings_Driver_Local', $driver);
    }

    /**
     * @covers Store_Settings::commit
     * @covers Store_Settings::override
     * @return void
     */
    public function testOverrideDoesNotCommit ()
    {
        $this->_settings->override("foo", "bar");
        $this->assertTrue($this->_settings->commit());
        $this->assertTrue(empty($this->_driver->config), "dummy config not empty after commit");
    }

    /**
     * @covers Store_Settings::schedule
     * @covers Store_Settings::get
     * @return void
     */
    public function testScheduleDoesNotAdjustConfig ()
    {
        $this->_settings->schedule('foo', 'bar');
        $this->assertNull($this->_settings->get('foo'));
    }

    /**
     * @covers Store_Settings::commit
     * @return void
     */
    public function testScheduleDoesCommit ()
    {
        $this->_settings->schedule("foo", "bar");
        $this->assertTrue($this->_settings->commit());
        $this->assertEquals("bar", $this->_driver->config["foo"], "value mismatch after commit");
    }

    /**
     * @covers Store_Settings::commit
     * @return void
     */
    public function testCommitUpdatesConfig ()
    {
        $this->_settings->schedule("foo", "bar");
        $this->assertTrue($this->_settings->commit());
        $this->assertEquals("bar", $this->_settings->get('foo'), "value mismatch after commit");
    }

    /**
     * @covers Store_Settings::commit
     * @return void
     */
    public function testCommitEmptiesScheduled ()
    {
        $this->_settings->schedule('Foo', 'bar');
        $this->assertTrue($this->_settings->commit(), "commit error");
        $this->assertNull($this->_settings->getScheduled('Foo'), "Scheduled not empty after commit");
    }

    /**
     * @covers Store_Settings::load
     * @return void
     */
    public function testLoad ()
    {
        $this->_driver->config['foo'] = 'bar';
        $this->assertTrue($this->_settings->load());
        $this->assertEquals('bar', $this->_settings->get('foo'));
    }

    /**
     * @covers Store_Settings::load
     * @covers Store_Settings::getErrorMessage
     * @return void
     */
    public function testFailedLoadProducesMessage ()
    {
        $this->_driver->fail = true;
        $this->assertFalse($this->_settings->load());
        $this->assertSame('foo', $this->_settings->getErrorMessage());
    }

    /**
     * @covers Store_Settings::load
     * @return void
     */
    public function testCorrectOriginalValueAfterLoad ()
    {
        $this->_driver->config['foo'] = 'bar';
        $this->assertTrue($this->_settings->load());
        $this->assertEquals('bar', $this->_settings->getOriginal('foo'));
    }

    /**
     * @covers Store_Settings::schedule
     * @covers Store_Settings::getOriginal
     * @return void
     */
    public function testScheduleDoesNotAffectOriginal ()
    {
        $this->_driver->config['foo'] = 'bar';
        $this->assertTrue($this->_settings->load());
        $this->_settings->schedule('foo', 'baz');
        $this->assertEquals('bar', $this->_settings->getOriginal('foo'));
    }

    /**
     * @covers Store_Settings::schedule
     * @covers Store_Config::setDefault
     * @covers Store_Config::getDefault
     * @return void
     */
    public function testScheduleDoesNotAffectDefault ()
    {
        Store_Config::setDefault('foo', 'bar');
        $this->_settings->schedule('foo', 'baz');
        $this->assertEquals('bar', Store_Config::getDefault('foo'));
    }

    public function testGetAllScheduled()
    {
        $this->_settings->schedule('foo', 'bar');
        $this->_settings->schedule('hello', 'world');

        $expected = array(
            'foo' => 'bar',
            'hello' => 'world',
        );

        $this->assertEquals($expected, $this->_settings->getAllScheduled());
    }

    public function testPersistsNewDefaultValues()
    {
        $defaults = array(
            'MyDefault'         => '1234',
            'AnotherDefault'    => '5678',
        );

        Store_Config::setDefaults($defaults);
        $this->_driver->config['Foo'] = 'Bar';
        $this->assertTrue($this->_settings->persistNewDefaultValues($defaults));

        $expected = $defaults;
        $expected['Foo'] = 'Bar';

        $this->assertEquals($expected, $this->_driver->config);
    }

    public function testCommitDoesntChangeExistingDefaultsWithNewValues()
    {
        $defaults = array(
            'MyDefault'         => '1234',
            'AnotherDefault'    => '5678',
        );

        Store_Config::setDefaults($defaults);
        $this->_driver->config['Foo'] = 'Bar';
        $this->assertTrue($this->_settings->persistNewDefaultValues($defaults));

        $expected = $defaults;
        $expected['Foo'] = 'Bar';

        // ensure that changing defaults doesn't persist to the config
        $newDefaults = array(
            'MyDefault'         => '9876',
            'AnotherDefault'    => '5432',
        );

        $this->_settings->persistNewDefaultValues($newDefaults);
        $this->assertEquals($expected, $this->_driver->config);
    }

    public function testGetVars()
    {
        $this->_driver->config = array(
            'SomeConfig'        => 'SomeValue',
            'AnotherSetting'    => 'AnotherValue',
        );

        $this->_settings->load();

        $expected = array(
            'SomeConfig',
            'AnotherSetting',
        );

        $this->assertEquals($expected, $this->_settings->getVars());
    }

    public function testExistsForExistingVariable()
    {
        $this->_driver->config = array(
            'Foo' => 'Bar',
        );

        $this->_settings->load();

        $this->assertTrue($this->_settings->exists('Foo'));
    }

    public function testExistsForNonExistingVariable()
    {
        $this->_driver->config = array(
            'Foo' => 'Bar',
        );

        $this->_settings->load();

        $this->assertFalse($this->_settings->exists('Foobar'));
    }
}
