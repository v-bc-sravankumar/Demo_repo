<?php

namespace Unit\Lib\Console\Profile;

use Console\Commands\Profile\ListCommand;
use Console\TableHelper;

use Profile;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

if (!defined('PRODUCT_VERSION')) {
    define('PRODUCT_VERSION', '7.5.10');
}

class ListCommandTest extends \PHPUnit_Framework_TestCase
{
    private $_command;
    private $_definition;
    private $_applicationMock;
    private $_tableHelperMock;

    public function setUp()
    {
        $this->_command    = new TestableListCommand();
        $this->_definition = $this->_command->getDefinition();

        // Add the 'verbose' option as this is not added when we exeucte a command in this manner.
        $this->_definition->addOption(new InputOption('--verbose', null, InputOption::VALUE_NONE));

        $this->_tableHelperMock = $this
            ->getMockBuilder('\\Console\\TableHelper')
            ->setMethods(array('setHeaders', 'addRows', 'render'))
            ->getMock();
    }

    public function testConfiguration()
    {
        // Duplicate here so that it shows up in code coverage.
        $command    = new ListCommand();
        $definition = $command->getDefinition();
        $options    = $definition->getOptions();

        $this->assertCount(3, $options);
        $this->assertTrue($definition->hasOption('s3-bucket-name'));
        $this->assertTrue($definition->hasOption('show-all-versions'));
        $this->assertTrue($definition->hasOption('show-all-owners'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must specify an AWS S3 bucket name.
     */
    public function testNoS3BucketName()
    {
        $input           = new ArrayInput(array(), $this->_definition);
        $output          = new NullOutput();
        $applicationMock = $this->_prepareApplicationMock(array(
            's3BucketName' => null,
            'username'     => false,
        ));

        $this->_command
            ->setApplication($applicationMock)
            ->execute($input, $output);
    }

    public function testNoShowAll()
    {
        $input                          = new ArrayInput(
            array('--s3-bucket-name' => 'test-bucket'),
            $this->_definition);
        $output                         = new NullOutput();
        $applicationMock                = $this->_prepareApplicationMock(array(
            's3BucketName' => false,
            'username'     => 'test-username',
        ));
        list($profileClassMock, $eTags) = $this->_prepareProfileClassMock(array(
            'application'     => $applicationMock,
            'showAllVersions' => false,
            'showAllOwners'   => false,
        ));

        $this->_tableHelperMock
            ->expects($this->at(0))
            ->method('setHeaders')
            ->with($this->equalTo(array('Store Hostname', 'Datasets', 'ETag')))
            ->will($this->returnValue($this->_tableHelperMock));

        $this->_tableHelperMock
            ->expects($this->at(1))
            ->method('addRows')
            ->with($this->equalTo(array(array('test-hostname', 'shipping', substr($eTags[0], 0, 8)))))
            ->will($this->returnValue($this->_tableHelperMock));

        $this->_command
            ->setApplication($applicationMock)
            ->setTableHelper($this->_tableHelperMock)
            ->setProfileClass($profileClassMock)
            ->execute($input, $output);
    }

    public function testShowAll()
    {
        $input                          = new ArrayInput(
            array(
                '--s3-bucket-name'    => 'test-bucket',
                '--show-all-versions' => true,
                '--show-all-owners'   => true,
            ),
            $this->_definition);
        $output                         = new NullOutput();
        $applicationMock                = $this->_prepareApplicationMock(array(
            's3BucketName' => false,
            'username'     => 'test-username',
        ));
        list($profileClassMock, $eTags) = $this->_prepareProfileClassMock(array(
            'application'     => $applicationMock,
            'showAllVersions' => true,
            'showAllOwners'   => true,
        ));

        $this->_tableHelperMock
            ->expects($this->at(0))
            ->method('setHeaders')
            ->with($this->equalTo(array('Store Version', 'Owner', 'Store Hostname', 'Datasets', 'ETag')))
            ->will($this->returnValue($this->_tableHelperMock));

        $this->_tableHelperMock
            ->expects($this->at(1))
            ->method('addRows')
            ->with($this->equalTo(array(
                array(PRODUCT_VERSION, 'test-username', 'test-hostname', 'shipping', substr($eTags[0], 0, 8)),
                array('7.5.11', 'test-username', 'test-hostname', 'shipping', substr($eTags[1], 0, 8)),
                array(PRODUCT_VERSION, 'test-username', 'test-hostname', 'shipping', substr($eTags[2], 0, 8)),
                array('7.5.11', 'test-username', 'test-hostname', 'shipping', substr($eTags[3], 0, 8)),
                array(PRODUCT_VERSION, 'test-other-username', 'test-hostname', 'shipping', substr($eTags[4], 0, 8)),
                array('7.5.11', 'test-other-username', 'test-hostname', 'shipping', substr($eTags[5], 0, 8)),
                array(PRODUCT_VERSION, 'test-other-username', 'test-hostname', 'shipping', substr($eTags[6], 0, 8)),
                array('7.5.11', 'test-other-username', 'test-hostname', 'shipping', substr($eTags[7], 0, 8)),
            )))
            ->will($this->returnValue($this->_tableHelperMock));

        $this->_command
            ->setApplication($applicationMock)
            ->setTableHelper($this->_tableHelperMock)
            ->setProfileClass($profileClassMock)
            ->execute($input, $output);
    }

    private function _prepareApplicationMock($overrideSettings = array())
    {
        $defaultSettings = array(
            's3BucketName' => 'bucket-name',
            'usenrame'     => 'test-username',
        );

        $settings = array_merge($defaultSettings, $overrideSettings);

        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array(
                'getConfigSetting',
            ))
            ->getMock();

        if ($settings['s3BucketName'] !== false) {
            $applicationMock
                ->expects($this->any())
                ->method('getConfigSetting')
                ->with($this->equalTo('s3BucketName'))
                ->will($this->returnValue($settings['s3BucketName']));
        }

        if ($settings['username'] !== false) {
            $applicationMock
                ->expects($this->any())
                ->method('getConfigSetting')
                ->with($this->equalTo('username'))
                ->will($this->returnValue($settings['username']));
        }

        return $applicationMock;
    }

    public function _prepareProfileClassMock($overrideSettings = array())
    {
        $defaultSettings = array(
            'listFromS3Bucket' => true,
            'application'      => null,
            's3BucketName'     => 'test-bucket',
            'showAllVersions'  => false,
            'showAllOwners'    => false,
        );

        $settings = array_merge($defaultSettings, $overrideSettings);
        $index    = 0;
        $eTags    = array();
        $profiles = array();

        $profileClassMock = $this->getMockClass('\\Profile', array('listFromS3Bucket'));

        $profiles[] = new Profile(
            PRODUCT_VERSION,
            'test-username',
            'test-hostname',
            array('shipping'),
            $eTags[] = md5(rand()),
            'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz');

        if ($settings['showAllVersions'] !== false) {
            $profiles[] = new Profile(
                '7.5.11',
                'test-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-username/7.5.11/test-hostname/shipping.tar.gz');

            $profiles[] = new Profile(
                PRODUCT_VERSION,
                'test-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz');

            $profiles[] = new Profile(
                '7.5.11',
                'test-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-username/7.5.11/test-hostname/shipping.tar.gz');
        }

        if ($settings['showAllOwners'] !== false) {
            $profiles[] = new Profile(
                PRODUCT_VERSION,
                'test-other-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-other-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz');

            $profiles[] = new Profile(
                '7.5.11',
                'test-other-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-other-username/7.5.11/test-hostname/shipping.tar.gz');

            $profiles[] = new Profile(
                PRODUCT_VERSION,
                'test-other-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-other-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz');

            $profiles[] = new Profile(
                '7.5.11',
                'test-other-username',
                'test-hostname',
                array('shipping'),
                $eTags[] = md5(rand()),
                'test-other-username/7.5.11/test-hostname/shipping.tar.gz');
        }

        if ($settings['listFromS3Bucket'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('listFromS3Bucket')
                ->with(
                    $this->equalTo($settings['s3BucketName']),
                    $this->equalTo($settings['showAllVersions']),
                    $this->equalTo($settings['showAllOwners']),
                    $this->equalTo($settings['application']))
                ->will($this->returnValue($profiles));
        }

        return array($profileClassMock, $eTags);
    }
}

class TestableListCommand extends ListCommand
{
    public function execute($input, $output)
    {
        parent::execute($input, $output);
    }
}

