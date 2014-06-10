<?php

namespace Unit\Lib\Profile;

use Profile\Dataset;

class DatasetTest extends \PHPUnit_Framework_TestCase
{
    private $_storeConfigMock;

    public function setUp()
    {
    }

    public function twoExitCodeProvider()
    {
        return array(
            array(0, 0),
            array(0, 255),
            array(255, 0),
        );
    }

    /**
     * @dataProvider twoExitCodeProvider
     */
    public function testImport($importConfigSettingsExitCode, $importDatabaseTablesExitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('readFile', 'importConfigSettings', 'importDatabaseTables', 'isFile'))
            ->setConstructorArgs(array('test-directory'))
            ->getMock();

        $datasetMock->setName('shipping');

        $datasetMock
            ->expects($this->at(0))
            ->method('isFile')
            ->with($this->equalTo('test-directory/shipping.json'))
            ->will($this->returnValue(true));

        $datasetMock
            ->expects($this->at(1))
            ->method('readFile')
            ->with($this->equalTo('test-directory/shipping.json'))
            ->will($this->returnValue(json_encode((object) array(
                'version'        => '0.0.1',
                'configSettings' => new \stdClass(),
            ))));

        $datasetMock
            ->expects($this->at(2))
            ->method('importConfigSettings')
            ->will($this->returnValue($importConfigSettingsExitCode));

        if ($importConfigSettingsExitCode === 0) {
            $datasetMock
                ->expects($this->at(3))
                ->method('importDatabaseTables')
                ->will($this->returnValue($importDatabaseTablesExitCode));
        }

        $exitCode = $datasetMock->import();

        if ($importConfigSettingsExitCode !== 0) {
            $this->assertEquals($importConfigSettingsExitCode, $exitCode);
        } else if ($importDatabaseTablesExitCode !== 0) {
            $this->assertEquals($importDatabaseTablesExitCode, $exitCode);
        } else {
            $this->assertEquals(0, $exitCode);
        }
    }

    /**
     * @dataProvider twoExitCodeProvider
     */
    public function testExport($exportConfigSettingsExitCode, $exportDatabaseTablesExitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('exportSettings', 'exportDatabaseTables'))
            ->setConstructorArgs(array(''))
            ->getMock();

        $datasetMock
            ->expects($this->at(0))
            ->method('exportSettings')
            ->will($this->returnValue($exportConfigSettingsExitCode));

        if ($exportConfigSettingsExitCode === 0) {
            $datasetMock
                ->expects($this->at(1))
                ->method('exportDatabaseTables')
                ->will($this->returnValue($exportDatabaseTablesExitCode));
        }

        $exitCode = $datasetMock->export();

        if ($exportConfigSettingsExitCode !== 0) {
            $this->assertEquals($exportConfigSettingsExitCode, $exitCode);
        } else if ($exportDatabaseTablesExitCode !== 0) {
            $this->assertEquals($exportDatabaseTablesExitCode, $exitCode);
        } else {
            $this->assertEquals(0, $exitCode);
        }
    }

    public function testImportConfigSettings()
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('readFile'))
            ->setConstructorArgs(array('test-directory'))
            ->getMock();

        $datasetMock->setName('test-dataset');
        $datasetMock->setConfigKeys(array('test-key-1', 'test-key-2'));

        $datasetMock
            ->expects($this->at(0))
            ->method('readFile')
            ->with($this->equalTo('test-directory/test-dataset.json'))
            ->will(
                $this->returnValue(
                    json_encode((object) array(
                        'configSettings' => (object) array(
                            'test-key-1' => 'test-value-1',
                            'test-key-2' => 'test-value-2',
                        ),
                    ))));

        $storeConfigMock = $this->getMockClass('\\Store_Config', array('schedule', 'commit'));

        $storeConfigMock::staticExpects($this->at(0))
            ->method('schedule')
            ->with($this->equalTo('test-key-1'), $this->equalTo('test-value-1'));

        $storeConfigMock::staticExpects($this->at(1))
            ->method('schedule')
            ->with($this->equalTo('test-key-2'), $this->equalTo('test-value-2'));

        $storeConfigMock::staticExpects($this->at(2))
            ->method('schedule');

        $datasetMock->importConfigSettings($storeConfigMock);
    }

    public function testExportSettings()
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('writeFile'))
            ->setConstructorArgs(array('test-directory'))
            ->getMock();

        $datasetMock->setName('test-dataset');
        $datasetMock->setConfigKeys(array('test-key-1', 'test-key-2'));

        $datasetMock
            ->expects($this->at(0))
            ->method('writeFile')
            ->with(
                $this->equalTo('test-directory/test-dataset.json'),
                $this->equalTo(
                    json_encode((object) array(
                        'version'        => '0.0.1',
                        'configSettings' => (object) array(
                            'test-key-1' => 'test-value-1',
                            'test-key-2' => 'test-value-2',
                        ),
                    ))));

        $storeConfigMock = $this->getMockClass('\\Store_Config', array('get'));

        $storeConfigMock::staticExpects($this->at(0))
            ->method('get')
            ->with($this->equalTo('test-key-1'))
            ->will($this->returnValue('test-value-1'));

        $storeConfigMock::staticExpects($this->at(1))
            ->method('get')
            ->with($this->equalTo('test-key-2'))
            ->will($this->returnValue('test-value-2'));

        $datasetMock->exportSettings($storeConfigMock);
    }

    public function exitCodeProvider()
    {
        return array(
            array(0),
            array(255),
        );
    }

    /**
     * @dataProvider exitCodeProvider
     */
    public function testImportDatabaseTables($exitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('importDatabaseTable'))
            ->setConstructorArgs(array(''))
            ->getMock();

        $datasetMock->setDatabaseTables(array('test-table-1', 'test-table-2'));

        $datasetMock
            ->expects($this->at(0))
            ->method('importDatabaseTable')
            ->with($this->equalTo('test-table-1'))
            ->will($this->returnValue($exitCode));

        if ($exitCode === 0) {
            $datasetMock
                ->expects($this->at(1))
                ->method('importDatabaseTable')
                ->with($this->equalTo('test-table-2'))
                ->will($this->returnValue(0));
        }

        $datasetExitCode = $datasetMock->importDatabaseTables();

        $this->assertEquals($exitCode, $datasetExitCode);
    }

    /**
     * @dataProvider exitCodeProvider
     */
    public function testExportDatabaseTables($exitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('exportDatabaseTable'))
            ->setConstructorArgs(array(''))
            ->getMock();

        $datasetMock->setDatabaseTables(array('test-table-1', 'test-table-2'));

        $datasetMock
            ->expects($this->at(0))
            ->method('exportDatabaseTable')
            ->with($this->equalTo('test-table-1'))
            ->will($this->returnValue($exitCode));

        if ($exitCode === 0) {
            $datasetMock
                ->expects($this->at(1))
                ->method('exportDatabaseTable')
                ->with($this->equalTo('test-table-2'))
                ->will($this->returnValue(0));
        }

        $datasetExitCode = $datasetMock->exportDatabaseTables();

        $this->assertEquals($exitCode, $datasetExitCode);
    }

    /**
     * @dataProvider exitCodeProvider
     */
    public function testImportDatabaseTable($exitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('executeSystemCommand'))
            ->setConstructorArgs(array('test-directory'))
            ->getMock();

        $datasetMock
            ->expects($this->at(0))
            ->method('executeSystemCommand')
            ->with($this->equalTo(
                'mysql '.
                '--host='.escapeshellarg('test-server').' '.
                '--user='.escapeshellarg('test-user').' '.
                '--password='.escapeshellarg('test-pass').' '.
                escapeshellarg('test-database').' '.
                '< '.escapeshellarg('test-directory/test-table-1.sql')))
            ->will($this->returnValue($exitCode));

        $this->_prepareStoreConfigMock();

        $datasetExitCode = $datasetMock->importDatabaseTable('test-table-1', $this->_storeConfigMock);

        $this->assertEquals($exitCode, $datasetExitCode);
    }

    /**
     * @dataProvider exitCodeProvider
     */
    public function testExportDatabaseTable($exitCode)
    {
        $datasetMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Profile\\TestableDataset')
            ->setMethods(array('executeSystemCommand'))
            ->setConstructorArgs(array('test-directory'))
            ->getMock();

        $datasetMock
            ->expects($this->at(0))
            ->method('executeSystemCommand')
            ->with($this->equalTo(
                'mysqldump '.
                '--disable-keys '.
                '--add-drop-table '.
                '--host='.escapeshellarg('test-server').' '.
                '--user='.escapeshellarg('test-user').' '.
                '--password='.escapeshellarg('test-pass').' '.
                escapeshellarg('test-database').' '.
                escapeshellarg('test-table-1').' '.
                '--result-file='.escapeshellarg('test-directory/test-table-1.sql')))
            ->will($this->returnValue($exitCode));

        $this->_prepareStoreConfigMock();

        $datasetExitCode = $datasetMock->ExportDatabaseTable('test-table-1', $this->_storeConfigMock);

        $this->assertEquals($exitCode, $datasetExitCode);
    }

    public function testCreateSuccess()
    {
        $this->assertInstanceOf('\\Profile\\Dataset\\Shipping', Dataset::create('shipping', 'test-profile-directory'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid dataset 'invalid-dataset'.
     */
    public function testCreateFailure()
    {
        Dataset::create('invalid-dataset', 'test-profile-directory');
    }

    /**
     * @expectedException           Exception
     * @expectedExceptionMessage    Datasets must specify a unique name.
     */
    public function testNameNotSupplied()
    {
        new \Unit\Lib\Profile\NamelessDataset('');
    }

    /**
     * @expectedException           Exception
     * @expectedExceptionMessage    Datasets must specify a version number.
     */
    public function testVersionNotSupplied()
    {
        new \Unit\Lib\Profile\VersionlessDataset('');
    }


    /*** HELPERS ***/

    private function _prepareStoreConfigMock()
    {
        $settings = array(
            'dbServer'   => 'test-server',
            'dbUser'     => 'test-user',
            'dbPass'     => 'test-pass',
            'dbDatabase' => 'test-database',
        );

        $storeConfigMock = $this->getMockClass('\\Store_Config', array('get'));

        $index = 0;

        foreach ($settings as $key => $value) {
            $storeConfigMock::staticExpects($this->at($index++))
                ->method('get')
                ->with($this->equalTo($key))
                ->will($this->returnValue($value));
        }

        $this->_storeConfigMock = $storeConfigMock;
    }
}

class TestableDataset extends Dataset
{
    protected $name = 'test';
    protected $version = '0.0.1';

    // Convenience methods for testing.
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setConfigKeys($keys)
    {
        $this->configKeys = $keys;
    }
    public function setDatabaseTables($tables)
    {
        $this->databaseTables = $tables;
    }


    public function importConfigSettings($storeConfigMock = '\\Store_Config')
    {
        return parent::importConfigSettings($storeConfigMock);
    }

    public function importDatabaseTables()
    {
        return parent::importDatabaseTables();
    }

    public function importDatabaseTable($databaseTable, $storeConfigMock = '\\Store_Config')
    {
        return parent::importDatabaseTable($databaseTable, $storeConfigMock);
    }

    public function exportSettings($storeConfigMock = '\\Store_Config')
    {
        return parent::exportSettings($storeConfigMock);
    }

    public function exportDatabaseTables()
    {
        return parent::exportDatabaseTables();
    }

    public function exportDatabaseTable($databaseTable, $storeConfigMock = '\\Store_Config')
    {
        return parent::exportDatabaseTable($databaseTable, $storeConfigMock);
    }
}

class NamelessDataset extends Dataset
{
    protected $version = '5';
}
class VersionlessDataset extends Dataset
{
    protected $name = 'Foo';
}
