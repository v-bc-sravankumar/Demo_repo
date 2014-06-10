<?php

namespace Unit\Settings\Driver;

use Store_Settings_Driver_Local;
use Store_Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase;

class LocalTest extends PHPUnit_Framework_TestCase
{
    /**
     * Gets a configured local settings driver.
     *
     * @param bool $installed If TRUE then the standard config will set the store to be installed.
     * @return Store_Settings_Driver_Local
     */
    private function getDriver($installed = true)
    {
        vfsStream::setup();

        if ($installed) {
            $config = "<?php
                return array (
                    'isSetup' => true,
                    'StoreName' => 'Stuff',
                    'Foo' => 'Bar',
                );
            ";
        }
        else {
            $config = "<?php
                return array (
                    'isSetup' => false,
                    'Foo' => 'Bar',
                );
            ";
        }

        vfsStream::create(array(
            'config' => array(
                'config.php' => $config,
                'config.backup.php' => "<?php
                    return array (
                        'isSetup' => true,
                        'BackupFile' => 1,
                    );
                ",
            ),
        ));

        $driver = new Store_Settings_Driver_Local();
        $driver
            ->setFilename(vfsStream::url('config/config.php'))
            ->setBackupFilename(vfsStream::url('config/config.backup.php'));

        return $driver;
    }

    private function assertIsValidConfigFile($file, $expectedConfig)
    {
        $actual = file_get_contents(vfsStream::url($file));
        $this->assertStringStartsWith('<?php', $actual, $file . ' is not a php file');
        $this->assertContains('return array (', $actual, $file . ' does not return an array');

        $config = require vfsStream::url($file);
        $this->assertEquals($expectedConfig, $config, 'config contents mismatch');
    }

    private function getExpectedConfig($changes)
    {
        $config = array(
            'isSetup'   => true,
            'StoreName' => 'Stuff',
            'Foo'       => 'Bar',
        );

        return array_merge($config, $changes);
    }

    /**
     * Test that setting a config value will cause a rewrite of the config file.
     */
    public function testPush()
    {
        $config = array(
            'AllowPurchasing' => 1,
        );

        $this->getDriver()->push($config);

        $this->assertIsValidConfigFile('config/config.php', $this->getExpectedConfig($config));
    }

    public function testPullBackupIfStoreNotInstalled ()
    {
        $config = $this->getDriver(false)->pull();

        $this->assertSame(1, $config['BackupFile']);
    }

    public function testRestoreBackup ()
    {
        $this->getDriver(false)->restoreBackup();

        $this->assertFileEquals(vfsStream::url('config/config.php'), vfsStream::url('config/config.backup.php'));
    }

    /**
     * @expectedException Store_Settings_Driver_Local_BackupRestoreException
     */
    public function testRestoreMissingBackupFails ()
    {
        $driver = $this->getDriver();

        unlink(vfsStream::url('config/config.backup.php'));

        $driver->restoreBackup();
    }

    public function testPull()
    {
        $config = $this->getDriver()->pull();

        $this->assertSame('Stuff', $config['StoreName']);
    }

    /**
     * @link https://jira.bigcommerce.com/browse/BIG-4210 Local config driver won't save null values
     */
    public function testCanSaveNullValue()
    {
        $config = array(
            'StoreName' => null,
        );

        $driver = $this->getDriver()->push($config);

        $this->assertIsValidConfigFile('config/config.php', $this->getExpectedConfig($config));
    }

    public function testUpgradeGlobalsFormatToArray()
    {
        vfsStream::setup();

        vfsStream::create(array(
            'config' => array(
                'config.php' => '<?php
                    $GLOBALS["ISC_CFG"]["isSetup"] = true;
                    $GLOBALS["ISC_CFG"]["StoreName"] = "Foo Store";
                ',
                'config.backup.php' => '',
            ),
        ));

        $driver = new Store_Settings_Driver_Local();
        $driver
            ->setFilename(vfsStream::url('config/config.php'))
            ->setBackupFilename(vfsStream::url('config/config.backup.php'));

        $config = $driver->pull();

        $expectedConfig = array(
            'isSetup' => true,
            'StoreName' => 'Foo Store',
        );

        $this->assertEquals($expectedConfig, $config);

        $this->assertIsValidConfigFile('config/config.php', $expectedConfig);

        // backup won't be converted until config is pushed
        $driver->push($expectedConfig);

        $this->assertIsValidConfigFile('config/config.backup.php', $expectedConfig);
    }
}
