<?php

namespace Unit\Lib\Console\Profile;

use Console\Commands\Profile\ExportCommand;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

if (!defined('PRODUCT_VERSION')) {
    define('PRODUCT_VERSION', '7.5.10');
}

class ExportCommandTest extends \PHPUnit_Framework_TestCase
{
    private $_commandMock;

    public function setUp()
    {
        $this->_commandMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Console\\Profile\\TestableExportCommand')
            ->setMethods(array('executeSystemCommand', 'createTempFilename', 'createTempDirectory'))
            ->getMock();

        $this->_definition = $this->_commandMock->getDefinition();

        // Add the 'verbose' option as this is not added when we exeucte a command in this manner.
        $this->_definition->addOption(new InputOption('--verbose', null, InputOption::VALUE_NONE));
    }

    public function testConfiguration()
    {
        // Duplicate here so that it shows up in code coverage.
        $command    = new ExportCommand();
        $definition = $command->getDefinition();
        $options    = $definition->getOptions();

        $this->assertCount(4, $options);
        $this->assertTrue($definition->hasOption('dataset'));
        $this->assertTrue($definition->hasOption('output-filename'));
        $this->assertTrue($definition->hasOption('s3-bucket-name'));
        $this->assertTrue($definition->hasOption('profile-name'));
    }

    public function testSuccess()
    {
        $input              = $this->_getArrayInput(array('dataset' => array('shipping')));
        $output             = new NullOutput();
        $datasetCommandMock = $this->_prepareDatasetCommandMock();
        $profileClassMock   = $this->_prepareProfileClassMock(array(
            'compressToTempAndDelete' => 'test-directory/test-filename',
        ));
        $applicationMock    = $this->_prepareApplicationMock(array('s3BucketName' => false));

        $configClassMock = $this->getMockClass('\\Store_Config', array('get'));

        $configClassMock::staticExpects($this->at(0))
            ->method('get')
            ->with($this->equalTo('ShopPath'))
            ->will($this->returnValue('http://test-hostname'));

        $this->_prepareCommandMock();

        $this->_commandMock
            ->setDatasetCommand($datasetCommandMock)
            ->setProfileClass($profileClassMock)
            ->setApplication($applicationMock)
            ->setConfigClass($configClassMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Can't continue. Both --output-filename and --s3-bucket-name are not set. Profile would not be saved.
     */
    public function testNoDestination()
    {
        $input           = new ArrayInput(array('--verbose' => false), $this->_definition);
        $output          = new NullOutput();
        $applicationMock = $this->_prepareApplicationMock(array(
            's3BucketName' => null,
            'username'     => false,
        ));

        $this->_commandMock
            ->setApplication($applicationMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Unsupported dataset: unsupported-dataset.
     */
    public function testUnsupportedDataset()
    {
        $input              = $this->_getArrayInput(array('dataset' => array('shipping', 'unsupported-dataset')));
        $output             = new NullOutput();
        $datasetCommandMock = $this->_prepareDatasetCommandMock();

        $this->_prepareCommandMock();

        $this->_commandMock
            ->setDatasetCommand($datasetCommandMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Exporting shipping dataset from the store database failed.
     */
    public function testExportDatasetFailed()
    {
        $input              = $this->_getArrayInput(array('dataset' => array('shipping')));
        $output             = new NullOutput();
        $datasetCommandMock = $this->_prepareDatasetCommandMock(array('exitCode' => 255));

        $this->_prepareCommandMock();

        $this->_commandMock
            ->setDatasetCommand($datasetCommandMock)
            ->execute($input, $output);
    }

    /*** HELPER FUNCTIONS ***/

    private function _prepareApplicationMock($overrideSettings = array())
    {
        $defaultSettings = array(
            's3BucketName' => 'bucket-name',
            'username'     => 'test-username',
        );

        $settings = array_merge($defaultSettings, $overrideSettings);
        $index    = 0;

        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array('getConfigSetting'))
            ->getMock();

        if ($settings['s3BucketName'] !== false) {
            $applicationMock
                ->expects($this->at($index++))
                ->method('getConfigSetting')
                ->with($this->equalTo('s3BucketName'))
                ->will($this->returnValue($settings['s3BucketName']));
        }

        if ($settings['username'] !== false) {
            $applicationMock
                ->expects($this->at($index++))
                ->method('getConfigSetting')
                ->with($this->equalTo('username'))
                ->will($this->returnValue($settings['username']));
        }

        return $applicationMock;
    }

    private function _prepareDatasetCommandMock($overrideSettings = array())
    {
        $defaultSettings = array(
            'exitCode' => 0,
        );

        $settings = array_merge($defaultSettings, $overrideSettings);

        $datasetCommandMock = $this
            ->getMockBuilder('\\Console\\Commands\\Profile\\Export\\DatasetCommand')
            ->setMethods(array('run'))
            ->getMock();

        $arguments = array(
            'command'            => 'profile:export:dataset',
            'dataset-name'       => 'shipping',
            '--output-directory' => 'test-directory/profile',
        );

        $datasetCommandMock
            ->expects($this->at(0))
            ->method('run')
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo('parameters', $arguments),
                    $this->isInstanceOf('\\Symfony\\Component\\Console\\Input\\InputInterface')))
            ->will($this->returnValue($settings['exitCode']));

        return $datasetCommandMock;
    }

    private function _getArrayInput($overrideSettings = array())
    {
        $defaultSettings = array(
            'dataset'        => null,
            'outputFilename' => null,
        );

        $settings = array_merge($defaultSettings, $overrideSettings);

        $arguments = array(
            '--verbose' => false,
            '--s3-bucket-name' => 'test-bucket',
        );

        if ($settings['dataset'] !== null) {
            $arguments['--dataset'] = (array) $settings['dataset'];
        }

        if ($settings['outputFilename'] !== null) {
            $arguments['--output-filename'] = $settings['outputFilename'];
        }

        $input = new ArrayInput($arguments, $this->_definition);

        return $input;
    }

    public function _prepareCommandMock($overrideSettings = array())
    {
        $defaultSettings = array(
            'createTempDirectory' => 'temp-directory',
        );

        $settings = array_merge($defaultSettings, $overrideSettings);
        $index    = 0;

        if ($defaultSettings['createTempDirectory'] !== false) {
            $this->_commandMock
                ->expects($this->at($index++))
                ->method('createTempDirectory')
                ->will($this->returnValue('test-directory'));
        }
    }

    public function _prepareProfileClassMock($overrideSettings = array())
    {
        $defaultSettings = array(
            'compressToTempAndDelete' => true,
            'uploadToS3'              => true,
            'outputFilename'          => null,
        );

        $settings = array_merge($defaultSettings, $overrideSettings);
        $index    = 0;

        $profileClassMock = $this->getMockClass(
            '\\Profile',
            array(
                'compressToTempAndDelete',
                'uploadToS3',
            ));

        if ($settings['compressToTempAndDelete'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('compressToTempAndDelete')
                ->with($this->equalTo('test-directory'), $settings['outputFilename'])
                ->will($this->returnValue($settings['compressToTempAndDelete']));
        }

        if ($settings['uploadToS3'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('uploadToS3')
                ->with(
                    $this->equalTo('test-bucket'),
                    $this->equalTo('test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz'),
                    $this->equalTo('test-directory/test-filename'));
        }

        return $profileClassMock;
    }
}

class TestableExportCommand extends ExportCommand
{
    public function execute($input, $output)
    {
        parent::execute($input, $output);
    }
}

