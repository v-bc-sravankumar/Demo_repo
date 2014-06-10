<?php

namespace Unit\Lib\Store;

use Store_Config;
use Store_Settings;
use Store_Settings_Driver_Dummy;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $backupDefaults;
    private $backupSettings;

    public function setUp()
    {
        $this->backupDefaults = Store_Config::getDefaults();
        $this->backupSettings = Store_Config::getInstance();
    }

    public function tearDown()
    {
        Store_Config::setDefaults($this->backupDefaults);
        Store_Config::setInstance($this->backupSettings);
    }

    public function testSetDefaults()
    {
        $defaults = array(
            'foo'   => 'bar',
            'hello' => 'world',
        );

        Store_Config::setDefaults($defaults);

        $this->assertEquals($defaults, Store_Config::getDefaults());
    }

    public function testGetVars()
    {
        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'Foo'   => 'Bar',
            'Hello' => 'World',
        )));

        $settings->load();

        Store_Config::setInstance($settings);

        $expected = array(
            'Foo',
            'Hello',
        );

        $this->assertEquals($expected, Store_Config::getVars());
    }

    public function testExistsForExistingVariable()
    {
        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'Foo'   => 'Bar',
        )));

        $settings->load();

        Store_Config::setInstance($settings);

        $this->assertTrue(Store_Config::exists('Foo'));
    }

    public function testExistsForNonExistingVariable()
    {
       $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'Foo'   => 'Bar',
        )));

        $settings->load();

        Store_Config::setInstance($settings);

        $this->assertFalse(Store_Config::exists('Foobar'));
    }
}
