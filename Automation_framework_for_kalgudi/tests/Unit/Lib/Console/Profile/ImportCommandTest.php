<?php

namespace Unit\Console\Profile;

use Console\Commands\Profile\ImportCommand;
use Profile;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

if (!defined('PRODUCT_VERSION')) {
    define('PRODUCT_VERSION', '7.5.10');
}

class ImportCommandTest extends \PHPUnit_Framework_TestCase
{
    private $_command;
    private $_definition;

    public function setUp()
    {
        $this->_command    = new TestableImportCommand();
        $this->_definition = $this->_command->getDefinition();

        // Add the 'verbose' option as this is not added when we exeucte a command in this manner.
        $this->_definition->addOption(new InputOption('--verbose', null, InputOption::VALUE_NONE));
    }

    public function testConfiguration()
    {
        // Duplicate here so that it shows up in code coverage.
        $command    = new ImportCommand();
        $definition = $command->getDefinition();
        $options    = $definition->getOptions();

        $this->assertCount(7, $options);
        $this->assertTrue($definition->hasOption('dataset'));
        $this->assertTrue($definition->hasOption('s3-bucket-name'));
        $this->assertTrue($definition->hasOption('s3-etag'));
        $this->assertTrue($definition->hasOption('s3-filename'));
        $this->assertTrue($definition->hasOption('show-all-versions'));
        $this->assertTrue($definition->hasOption('show-all-owners'));
    }

    public function showAllVerboseProvider()
    {
        return array(
            array(true, true, true),    // 111
            array(true, true, false),   // 110
            array(true, false, true),   // 101
            array(true, false, false),  // 100
            array(false, true, true),   // 011
            array(false, true, false),  // 010
            array(false, false, true),  // 001
            array(false, false, false), // 000
        );
    }

    /**
     * @dataProvider showAllVerboseProvider
     */
    public function testSuccess($showAllVersions, $showAllOwners, $verbose)
    {
        $input                         = $this->_getArrayInput(
            $showAllVersions,
            $showAllOwners,
            $verbose,
            array('shipping'));
        $output                        = new NullOutput();
        list($profileClassMock, $eTag) = $this->_prepareProfileClassMock(array(
            'showAllVersions' => $showAllVersions,
            'showAllOwners'   => $showAllOwners,
        ));
        $listCommandMock               = $this->_prepareListCommandMock($showAllVersions, $showAllOwners, $verbose);
        $dialogHelperMock              = $this->_prepareDialogHelperMock($output, $eTag);
        $importDatasetCommandMock      = $this->_prepareImportDatasetCommandMock($verbose);

        $this->_command
            ->setProfileClass($profileClassMock)
            ->setListCommand($listCommandMock)
            ->setDialogHelper($dialogHelperMock)
            ->setImportDatasetCommand($importDatasetCommandMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify an AWS S3 bucket name.
     */
    public function testNoS3BucketName()
    {
        $input           = new ArrayInput(array(), $this->_definition);
        $output          = new NullOutput();
        $applicationMock = $this->_prepareApplicationMock(null);

        $this->_command
            ->setApplication($applicationMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Listing the available profiles in the AWS S3 bucket failed.
     */
    public function testListCommandFailure()
    {
        $input           = new ArrayInput(array('--s3-bucket-name' => 'test-bucket'), $this->_definition);
        $output          = new NullOutput();
        $listCommandMock = $this->_prepareListCommandMock(false, false, false, 255);

        $this->_command
            ->setListCommand($listCommandMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not find profile.
     */
    public function testSelectProfileFailure()
    {
        $input                         = $this->_getArrayInput();
        $output                        = new NullOutput();
        list($profileClassMock, $eTag) = $this->_prepareProfileClassMock(array(
            'downloadFromS3ToTemp'   => false,
            'extractToTempAndDelete' => false,
        ));
        $listCommandMock               = $this->_prepareListCommandMock();
        $dialogHelperMock              = $this->_prepareDialogHelperMock($output, 'invalid-etag');

        $this->_command
            ->setProfileClass($profileClassMock)
            ->setListCommand($listCommandMock)
            ->setDialogHelper($dialogHelperMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid dataset 'unsupported-dataset'.
     */
    public function testUnsupportedDataset()
    {
        $input                         = $this->_getArrayInput(false, false, false, 'unsupported-dataset');
        $output                        = new NullOutput();
        $listCommandMock               = $this->_prepareListCommandMock();
        list($profileClassMock, $eTag) = $this->_prepareProfileClassMock();
        $dialogHelperMock              = $this->_prepareDialogHelperMock($output, $eTag);

        $this->_command
            ->setListCommand($listCommandMock)
            ->setProfileClass($profileClassMock)
            ->setDialogHelper($dialogHelperMock)
            ->execute($input, $output);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Importing shipping dataset into the store database failed.
     */
    public function testImportDatasetFailure()
    {
        $input  = $this->_getArrayInput(false, false, false, array('shipping'));
        $output = new NullOutput();
        $listCommandMock               = $this->_prepareListCommandMock();
        list($profileClassMock, $eTag) = $this->_prepareProfileClassMock();
        $dialogHelperMock              = $this->_prepareDialogHelperMock($output, $eTag);
        $importDatasetCommandMock      = $this->_prepareImportDatasetCommandMock(false, 255);

        $this->_command
            ->setListCommand($listCommandMock)
            ->setProfileClass($profileClassMock)
            ->setDialogHelper($dialogHelperMock)
            ->setImportDatasetCommand($importDatasetCommandMock)
            ->execute($input, $output);
    }

    /*** HELPER METHODS ***/

    private function _getArrayInput($showAllVersions = false, $showAllOwners = false, $verbose = false, $dataset = false)
    {
        $arguments = array();

        $arguments['--s3-bucket-name']    = 'test-bucket';
        $arguments['--show-all-versions'] = $showAllVersions;
        $arguments['--show-all-owners']   = $showAllOwners;

        if ($dataset) {
            $arguments['--dataset'] = (array) $dataset;
        }

        if ($verbose) {
            $arguments['--verbose'] = true;
        }

        $input = new ArrayInput($arguments, $this->_definition);

        return $input;
    }

    private function _prepareProfileImportDatasetCommandMock()
    {
        $profileImportArguments = array(
            'command'           => 'profile:import:dataset',
            '--input-directory' => 'temp-directory/profile',
            'dataset-name'      => 'shipping',
        );

        $this->_profileImportDatasetCommandMock
            ->expects($this->at(0))
            ->method('run')
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo('parameters', $profileImportArguments),
                    $this->isInstanceOf('\\Symfony\\Component\\Console\\Input\\InputInterface')))
            ->will($this->returnValue(255));
    }

    private function _prepareDialogHelperMock($output, $eTag)
    {
        $dialogHelperMock = $this
            ->getMockBuilder('\\Symfony\\Component\\Console\\Helper\\DialogHelper')
            ->setMethods(array('ask'))
            ->getMock();

        $dialogHelperMock
            ->expects($this->at(0))
            ->method('ask')
            ->with(
                $this->logicalAnd(
                    $this->equalTo($output),
                    $this->isInstanceOf('\\Symfony\\Component\\Console\\Output\\OutputInterface')),
                $this->equalTo('<question>Profile ETag:</question> '))
            ->will($this->returnValue($eTag));

        return $dialogHelperMock;
    }

    private function _prepareProfileClassMock($overrideSettings = array())
    {
        $defaultSettings = array(
            'showAllVersions'        => false,
            'showAllOwners'          => false,
            'downloadFromS3ToTemp'   => 'temp-filename',
            'extractToTempAndDelete' => 'temp-directory',
        );

        $settings = array_merge($defaultSettings, $overrideSettings);

        $profileMock = $this
            ->getMockBuilder('\\Profile')
            ->setMethods(array('downloadFromS3ToTemp', 'extractToTempAndDelete'))
            ->setConstructorArgs(
                array(
                    PRODUCT_VERSION,
                    'test-username',
                    'test-hostname',
                    array('shipping'),
                    $eTag = md5(rand()),
                    'test-username/test-hostname/'.PRODUCT_VERSION.'/shipping.tar.gz',
                ))
            ->getMock();

        if ($settings['downloadFromS3ToTemp'] !== false) {
            $profileMock
                ->expects($this->at(0))
                ->method('downloadFromS3ToTemp')
                ->with($this->equalTo('test-bucket'))
                ->will($this->returnValue($settings['downloadFromS3ToTemp']));
        }

        if ($settings['extractToTempAndDelete'] !== false) {
            $profileMock
                ->expects($this->at(1))
                ->method('extractToTempAndDelete')
                ->with($this->equalTo('temp-filename'))
                ->will($this->returnValue($settings['extractToTempAndDelete']));
        }

        $profileClass = $this->getMockClass('\\Profile', array('listFromS3Bucket'));

        $profileClass::staticExpects($this->at(0))
            ->method('listFromS3Bucket')
            ->with(
                $this->equalTo('test-bucket'),
                $this->equalTo($settings['showAllVersions']),
                $this->equalTo($settings['showAllOwners']))
            ->will($this->returnValue(array($profileMock)));

        return array($profileClass, $eTag);
    }

    private function _prepareListCommandMock($showAllVersions = false, $showAllOwners = false, $verbose = false, $returnValue = 0)
    {
        $listCommandMock = $this
            ->getMockBuilder('\\Console\\Commands\\Profile\\ListCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('run'))
            ->getMock();

        $arguments = array(
            'command'             => 'profile:list',
            '--s3-bucket-name'    => 'test-bucket',
            '--show-all-versions' => $showAllVersions,
            '--show-all-owners'   => $showAllOwners,
        );

        if ($verbose) {
            $arguments['--verbose'] = true;
        }

        $listCommandMock
            ->expects($this->at(0))
            ->method('run')
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo('parameters', $arguments),
                    $this->isInstanceOf('\\Symfony\\Component\\Console\\Input\\InputInterface')))
            ->will($this->returnValue($returnValue));

        return $listCommandMock;
    }

    private function _prepareImportDatasetCommandMock($verbose = false, $exitCode = 0)
    {
        $importDatasetCommandMock = $this
            ->getMockBuilder('\\Console\\Commands\\Profile\\Import\\DatasetCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('run'))
            ->getMock();

        $arguments = array(
            'command'           => 'profile:import:dataset',
            '--input-directory' => 'temp-directory/profile',
            'dataset-name'      => 'shipping',
        );

        if ($verbose) {
            $arguments['--verbose'] = true;
        }

        $importDatasetCommandMock
            ->expects($this->at(0))
            ->method('run')
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo('parameters', $arguments),
                    $this->isInstanceOf('\\Symfony\\Component\\Console\\Input\\InputInterface')))
            ->will($this->returnValue($exitCode));

        return $importDatasetCommandMock;
    }

    private function _prepareApplicationMock($s3BucketName = 'test-bucket')
    {
        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array('getConfigSetting'))
            ->getMock();

        $applicationMock
            ->expects($this->at(0))
            ->method('getConfigSetting')
            ->with($this->equalTo('s3BucketName'))
            ->will($this->returnValue($s3BucketName));

        return $applicationMock;
    }
}

class TestableImportCommand extends ImportCommand
{
    public function execute($input, $output)
    {
        parent::execute($input, $output);
    }

    public function executeSystemCommand($command)
    {
        // Don't actually execute any system commands when testing.
    }
}

