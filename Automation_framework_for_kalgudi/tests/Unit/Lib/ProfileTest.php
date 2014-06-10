<?php

namespace Unit\Lib;

use Profile;
use Console\Application;

if (!defined('PRODUCT_VERSION')) {
    define('PRODUCT_VERSION', '7.5.10');
}

class ProfileTest extends \PHPUnit_Framework_TestCase
{
    public function profileProvider()
    {
        return array(
            array(
                new Profile(
                    PRODUCT_VERSION,
                    'test-username',
                    'test.dev2.syd1bc.bigcommerce.net',
                    array('shipping', 'customers'),
                    md5(rand()),
                    'test-username/'.PRODUCT_VERSION.'/test.dev2.syd1bc.bigcommerce.net/shipping.tar.gz'
                ),
            ),
        );
    }

    public function testConstructor()
    {
        $profile = new Profile(
            PRODUCT_VERSION,
            'test-username',
            'test-hostname',
            array('dataset1', 'dataset2'),
            $eTag = md5(rand()),
            'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz'
        );

        $this->assertEquals(PRODUCT_VERSION, $profile->getStoreVersion());
        $this->assertEquals('test-username', $profile->getOwner());
        $this->assertEquals('test-hostname', $profile->getStoreHostname());
        $this->assertCount(2, $datasets = $profile->getDatasets());
        $this->assertEquals('dataset1', $datasets[0]);
        $this->assertEquals('dataset2', $datasets[1]);
        $this->assertEquals($eTag, $profile->getETag());
        $this->assertEquals('test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz', $profile->getS3Filename());
    }

    /**
     * @dataProvider profileProvider
     */
    public function testGenerateRow(Profile $profile)
    {
        // Test with 'showVersions' set to false
        $row = $profile->generateRow(false);
        $this->assertCount(3, $row);
        $this->assertEquals($profile->getStoreHostname(), $row[0]);
        $this->assertEquals(implode(', ', $profile->getDatasets()), $row[1]);
        $this->assertEquals(substr($profile->getETag(), 0, 8), $row[2]);

        // Test with 'showVersions' set to true
        $row = $profile->generateRow(true);
        $this->assertCount(4, $row);
        $this->assertEquals($profile->getStoreVersion(), $row[0]);
        $this->assertEquals($profile->getStoreHostname(), $row[1]);
        $this->assertEquals(implode(', ', $profile->getDatasets()), $row[2]);
        $this->assertEquals(substr($profile->getETag(), 0, 8), $row[3]);

        // Test with 'showOwners' set to true
        $row = $profile->generateRow(true, true);
        $this->assertCount(5, $row);
        $this->assertEquals($profile->getStoreVersion(), $row[0]);
        $this->assertEquals($profile->getOwner(), $row[1]);
        $this->assertEquals($profile->getStoreHostname(), $row[2]);
        $this->assertEquals(implode(', ', $profile->getDatasets()), $row[3]);
        $this->assertEquals(substr($profile->getETag(), 0, 8), $row[4]);
    }

    public function testListFromS3Bucket()
    {
        list($s3Mock, $eTags) = $this->_prepareS3Mock(array(
            'get_object_url' => false,
            'batch'          => false,
            'create_object'  => false,
            'send'           => false,
            'areOK'          => false,
        ));

        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array('getConfigSetting'))
            ->getMock();

        $applicationMock
            ->expects($this->at(0))
            ->method('getConfigSetting')
            ->with($this->equalTo('username'))
            ->will($this->returnValue('test-username'));

        Profile::setS3($s3Mock);

        $profiles = Profile::listFromS3Bucket('some-bucket-name', false, false, $applicationMock);

        $this->assertCount(1, $profiles);
        $this->assertEquals(PRODUCT_VERSION, $profiles[0]->getStoreVersion());
        $this->assertEquals('test-hostname', $profiles[0]->getStoreHostname());
        $this->assertCount(1, $datasets = $profiles[0]->getDatasets());
        $this->assertEquals('shipping', $datasets[0]);
        $this->assertEquals($eTags[0], $profiles[0]->getETag());
        $this->assertEquals(
            'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
            $profiles[0]->getS3Filename());
    }

    public function trueFalseProvider()
    {
        return array(array(true), array(false));
    }

    /**
     * @dataProvider trueFalseProvider
     */
    public function testListFromS3BucketMultipleVersions($showAllVersions)
    {
        list($s3Mock, $eTags) = $this->_prepareS3Mock(array(
            'get_object_url'           => false,
            'batch'                    => false,
            'create_object'            => false,
            'send'                     => false,
            'areOK'                    => false,
            'includeDifferentVersions' => true,
        ));

        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array('getConfigSetting'))
            ->getMock();

        $applicationMock
            ->expects($this->exactly($showAllVersions ? 2 : 1))
            ->method('getConfigSetting')
            ->with($this->equalTo('username'))
            ->will($this->returnValue('test-username'));

        Profile::setS3($s3Mock);

        $profiles = Profile::listFromS3Bucket('some-bucket-name', $showAllVersions, false, $applicationMock);

        if ($showAllVersions) {
            $this->assertCount(2, $profiles);
            $this->assertEquals(PRODUCT_VERSION, $profiles[0]->getStoreVersion());
            $this->assertEquals('test-username', $profiles[0]->getOwner());
            $this->assertEquals('test-hostname', $profiles[0]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[0]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[0], $profiles[0]->getETag());
            $this->assertEquals(
                'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                $profiles[0]->getS3Filename());

            $this->assertEquals('7.5.11', $profiles[1]->getStoreVersion());
            $this->assertEquals('test-username', $profiles[1]->getOwner());
            $this->assertEquals('test-hostname', $profiles[1]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[1]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[1], $profiles[1]->getETag());
            $this->assertEquals(
                'test-username/7.5.11/test-hostname/shipping.tar.gz',
                $profiles[1]->getS3Filename());
        } else {
            $this->assertCount(1, $profiles);
            $this->assertEquals(PRODUCT_VERSION, $profiles[0]->getStoreVersion());
            $this->assertEquals('test-username', $profiles[0]->getOwner());
            $this->assertEquals('test-hostname', $profiles[0]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[0]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[0], $profiles[0]->getETag());
            $this->assertEquals(
                'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                $profiles[0]->getS3Filename());
        }
    }

    /**
     * @dataProvider trueFalseProvider
     */
    public function testListFromS3BucketMultipleOwners($showAllOwners)
    {
        list($s3Mock, $eTags) = $this->_prepareS3Mock(array(
            'get_object_url'         => false,
            'batch'                  => false,
            'create_object'          => false,
            'send'                   => false,
            'areOK'                  => false,
            'includeDifferentOwners' => true,
        ));

        $applicationMock = $this
            ->getMockBuilder('\\Console\\Application')
            ->setMethods(array('getConfigSetting'))
            ->getMock();

        if (!$showAllOwners) {
            $applicationMock
                ->expects($this->exactly(2))
                ->method('getConfigSetting')
                ->with($this->equalTo('username'))
                ->will($this->returnValue('test-username'));
        }

        Profile::setS3($s3Mock);

        $profiles = Profile::listFromS3Bucket('some-bucket-name', false, $showAllOwners, $applicationMock);

        if ($showAllOwners) {
            $this->assertCount(2, $profiles);
            $this->assertEquals(PRODUCT_VERSION, $profiles[0]->getStoreVersion());
            $this->assertEquals('test-username', $profiles[0]->getOwner());
            $this->assertEquals('test-hostname', $profiles[0]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[0]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[0], $profiles[0]->getETag());
            $this->assertEquals(
                'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                $profiles[0]->getS3Filename());

            $this->assertEquals(PRODUCT_VERSION, $profiles[1]->getStoreVersion());
            $this->assertEquals('other-username', $profiles[1]->getOwner());
            $this->assertEquals('test-hostname', $profiles[1]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[1]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[1], $profiles[1]->getETag());
            $this->assertEquals(
                'other-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                $profiles[1]->getS3Filename());
        } else {
            $this->assertCount(1, $profiles);
            $this->assertEquals(PRODUCT_VERSION, $profiles[0]->getStoreVersion());
            $this->assertEquals('test-username', $profiles[0]->getOwner());
            $this->assertEquals('test-hostname', $profiles[0]->getStoreHostname());
            $this->assertCount(1, $datasets = $profiles[0]->getDatasets());
            $this->assertEquals('shipping', $datasets[0]);
            $this->assertEquals($eTags[0], $profiles[0]->getETag());
            $this->assertEquals(
                'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                $profiles[0]->getS3Filename());
        }
    }

    /**
     * @expectedException Exception
     */
    public function testParseFilenameNotEnoughParts()
    {
        Profile::parseFilename('test-hostname');
    }

    public function testParseFilename()
    {
        $filename = 'test-username/'.PRODUCT_VERSION.'/test-hostname/customers-shipping.tar.gz';
        $parsed   = Profile::parseFilename($filename);

        $this->assertCount(4, $parsed);
        $this->assertEquals(PRODUCT_VERSION, $parsed['storeVersion']);
        $this->assertEquals(array('customers', 'shipping'), $parsed['datasets']);
        $this->assertEquals('test-username', $parsed['owner']);
        $this->assertEquals('test-hostname', $parsed['storeHostname']);
    }

    public function testBuildS3Filename()
    {
        $configClassMock = $this->getMockClass('\\Store_Config', array('get'));

        $configClassMock::staticExpects($this->exactly(2))
            ->method('get')
            ->with($this->equalTo('ShopPath'))
            ->will($this->returnValue('http://test-hostname'));

        $s3Filename = Profile::buildS3Filename('test-username', array('customers', 'shipping'), null, $configClassMock);

        $this->assertEquals('test-username/'.PRODUCT_VERSION.'/test-hostname/customers-shipping.tar.gz', $s3Filename);

        // Test that filenames are alphabetised and that custom profile name is honoured.
        $s3Filename = Profile::buildS3Filename(
            'test-username',
            array('shipping', 'customers'),
            'other-hostname',
            $configClassMock);

        $this->assertEquals('test-username/'.PRODUCT_VERSION.'/other-hostname/customers-shipping.tar.gz', $s3Filename);
    }

    public function testSupportedDatasets()
    {
        $datasets = Profile::getSupportedDatasets();

        $this->assertCount(7, $datasets);
        $this->assertEquals('coupons', $datasets[0]);
        $this->assertEquals('customers', $datasets[1]);
        $this->assertEquals('gift_certificates', $datasets[2]);
        $this->assertEquals('orders', $datasets[3]);
        $this->assertEquals('payment_providers', $datasets[4]);
        $this->assertEquals('products', $datasets[5]);
        $this->assertEquals('shipping', $datasets[6]);
    }

    public function testIsSupportedDatasetInvalidDataset()
    {
        $supported = Profile::isSupportedDataset('invalid-dataset');

        $this->assertFalse($supported);
    }

    public function testIsSupportedDataset()
    {
        $supported = Profile::isSupportedDataset('shipping');

        $this->assertTrue($supported);
    }

    public function testGetConfigSetting()
    {
        $storeConfig = $this->getMockClass('\\Store_Config', array('get'));

        $storeConfig::staticExpects($this->at(0))
            ->method('get')
            ->with($this->equalTo('testConfigSetting'))
            ->will($this->returnValue('test-config-value'));

        $settings = Profile::getConfigSettings(array('testConfigSetting'), $storeConfig);

        $this->assertObjectHasAttribute('testConfigSetting', $settings);
        $this->assertEquals('test-config-value', $settings->testConfigSetting);
    }

    public function testDownloadFromS3ToTemp()
    {
        list($s3Mock, $eTags)                  = $this->_prepareS3Mock(array(
            'list_objects'  => false,
            'batch'         => false,
            'create_object' => false,
            'send'          => false,
            'areOK'         => false,
        ));
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => $s3Mock,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'tarCompress'         => false,
            'rmFilename'          => false,
            'rmDirectory'        => false,
        ));

        $profileMock
            ->setProfileClass($profileClassMock)
            ->downloadFromS3ToTemp('test-bucket', new Application());
    }

    public function testUploadToS3()
    {
        list($s3Mock)                         = $this->_prepareS3Mock(array(
            'list_objects'   => false,
            'get_object_url' => false,
            'areOK'          => 1,
        ));
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => $s3Mock,
            'createTempFilename'  => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'tarCompress'         => false,
            'rmFilename'          => false,
            'rmDirectory'         => false,
        ));

        $profileClassMock::uploadToS3(
            'test-bucket',
            'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
            'test-filename',
            $profileClassMock,
            new Application()
        );
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not upload to Amazon S3.
     */
    public function testUploadToS3Failure()
    {
        list($s3Mock)                         = $this->_prepareS3Mock(array(
            'list_objects'   => false,
            'get_object_url' => false,
            'areOK'              => 0,
        ));
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => $s3Mock,
            'createTempFilename'  => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'tarCompress'         => false,
            'rmFilename'          => false,
            'rmDirectory'        => false,
        ));

        $profileClassMock::uploadToS3(
            'test-bucket',
            'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
            'test-filename',
            $profileClassMock,
            new Application()
        );
    }

    public function testExtractToTempAndDelete()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'             => false,
            'createTempFilename' => false,
            'getS3Filename'      => false,
            'saveFile'           => false,
            'tarCompress'        => false,
            'rmDirectory'        => false,
        ));

        $profileMock
            ->setProfileClass($profileClassMock)
            ->extractToTempAndDelete('test-filename');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not extract 'test-filename'.
     */
    public function testExtractToTempAndDeleteExtractFailure()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'             => false,
            'createTempFilename' => false,
            'getS3Filename'      => false,
            'saveFile'           => false,
            'tarExtract'         => 255,
            'rmFilename'         => false,
            'tarCompress'        => false,
            'rmDirectory'        => false,
        ));

        $profileMock
            ->setProfileClass($profileClassMock)
            ->extractToTempAndDelete('test-filename');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not delete 'test-filename'.
     */
    public function testExtractToTempAndDeleteDeleteFailure()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'             => false,
            'createTempFilename' => false,
            'getS3Filename'      => false,
            'saveFile'           => false,
            'rmFilename'         => 255,
            'tarCompress'        => false,
            'rmDirectory'        => false,
        ));

        $profileMock
            ->setProfileClass($profileClassMock)
            ->extractToTempAndDelete('test-filename');
    }

    public function testCompressToTempAndDelete()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => false,
            'createTempFilename'  => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'rmFilename'          => false,
        ));

        $profileClassMock::compressToTempAndDelete('test-directory', 'test-filename', $profileClassMock);
    }

    public function testCompressToTempAndDeleteGenerateFilename()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'rmFilename'          => false,
        ));

        $profileClassMock::compressToTempAndDelete('test-directory', null, $profileClassMock);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not compress profile data.
     */
    public function testCompressToTempAndDeleteGenerateFilenameCompressFailure()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => false,
            'createTempFilename'  => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'rmFilename'          => false,
            'tarCompress'         => 255,
            'rmDirectory'         => false,
        ));

        $profileClassMock::compressToTempAndDelete('test-directory', 'test-filename', $profileClassMock);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Could not remove temporary profile data.
     */
    public function testCompressToTempAndDeleteGenerateFilenameDeleteFailure()
    {
        list($profileMock, $profileClassMock) = $this->_prepareProfileMock(array(
            's3Mock'              => false,
            'createTempFilename'  => false,
            'getS3Filename'       => false,
            'saveFile'            => false,
            'createTempDirectory' => false,
            'tarExtract'          => false,
            'rmFilename'          => false,
            'rmDirectory'         => 255,
        ));

        $profileClassMock::compressToTempAndDelete('test-directory', 'test-filename', $profileClassMock);
    }

    /*** HELPERS ***/

    private function _prepareS3Mock($overrideSettings = array())
    {
        $defaultSettings = array(
            'list_objects'             => true,
            'get_object_url'           => 'test-profile-url',
            'batch'                    => true,
            'create_object'            => true,
            'send'                     => true,
            'areOK'                    => true,
            'includeDifferentVersions' => false,
            'includeDifferentOwners'   => false,
        );

        $settings            = array_merge($defaultSettings, $overrideSettings);
        $index               = 0;
        $batchIndex          = 0;
        $uploadResponseIndex = 0;

        $s3Mock = $this
            ->getMockBuilder('AmazonS3')
            ->disableOriginalConstructor()
            ->setMethods(array('list_objects', 'get_object_url', 'batch'))
            ->getMock();

        $batchMock = $this
            ->getMockBuilder('\\Unit\\Lib\\TestableS3Batch')
            ->setMethods(array('create_object', 'send'))
            ->getMock();

        $uploadResponseMock = $this
            ->getMockBuilder('\\Unit\\Lib\\TestableS3UploadResponse')
            ->setMethods(array('areOK'))
            ->getMock();

        $eTags = array();

        if ($settings['list_objects'] !== false) {
            $profiles = array(
                (object) array(
                    'Key'  => 'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                    'ETag' => $eTags[] = md5(rand()),
                ),
            );

            if ($settings['includeDifferentVersions']) {
                $profiles[] = (object) array(
                    'Key'  => 'test-username/7.5.11/test-hostname/shipping.tar.gz',
                    'ETag' => $eTags[] = md5(rand()),
                );
            }

            if ($settings['includeDifferentOwners']) {
                $profiles[] = (object) array(
                    'Key'  => 'other-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
                    'ETag' => $eTags[] = md5(rand()),
                );
            }

            $s3Mock
                ->expects($this->at($index++))
                ->method('list_objects')
                ->with($this->equalTo('some-bucket-name'))
                ->will($this->returnValue((object) array(
                    'body' => (object) array(
                        'Contents' => $profiles,
                    ),
                )));
        }

        if ($settings['get_object_url'] !== false) {
            $s3Mock
                ->expects($this->at($index++))
                ->method('get_object_url')
                ->with(
                    $this->equalTo('test-bucket'),
                    $this->equalTo('test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz'),
                    $this->equalTo('+5 minutes'))
                ->will($this->returnValue($settings['get_object_url']));
        }

        if ($settings['batch'] !== false) {
            $s3Mock
                ->expects($this->at($index++))
                ->method('batch')
                ->will($this->returnValue($batchMock));

            $s3Mock
                ->expects($this->at($index++))
                ->method('batch')
                ->will($this->returnValue($batchMock));
        }

        if ($settings['create_object'] !== false) {
            $batchMock
                ->expects($this->at($batchIndex++))
                ->method('create_object')
                ->with(
                    $this->equalTo('test-bucket'),
                    $this->equalTo('test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz'),
                    $this->equalTo(array('fileUpload' => 'test-filename')));
        }

        if ($settings['send'] !== false) {
            $batchMock
                ->expects($this->at($batchIndex++))
                ->method('send')
                ->will($this->returnValue($uploadResponseMock));
        }

        if ($settings['areOK'] !== false) {
            $uploadResponseMock
                ->expects($this->at($uploadResponseIndex++))
                ->method('areOK')
                ->will($this->returnValue($settings['areOK']));
        }

        return array($s3Mock, $eTags);
    }

    private function _prepareProfileMock($overrideSettings = array())
    {
        $defaultSettings = array(
            's3Mock'              => null,
            'createTempFilename'  => 'test-filename',
            'getS3Filename'       => 'test-username/'.PRODUCT_VERSION.'/test-hostname/shipping.tar.gz',
            'saveFile'            => true,
            'createTempDirectory' => 'test-directory',
            'tarExtract'          => 0,
            'rmFilename'          => 0,
            'tarCompress'         => 0,
            'rmDirectory'         => 0,
        );

        $settings = array_merge($defaultSettings, $overrideSettings);
        $index    = 0;

        $profileMock = $this
            ->getMockBuilder('\\Profile')
            ->setMethods(array(
                'getS3Filename',
                'saveFile',
                'createTempDirectory',
            ))
            ->disableOriginalConstructor()
            ->getMock();

        if ($settings['getS3Filename'] !== false) {
            $profileMock
                ->expects($this->at($index++))
                ->method('getS3Filename')
                ->will($this->returnValue($settings['getS3Filename']));
        }

        if ($settings['saveFile'] !== false) {
            $profileMock
                ->expects($this->at($index++))
                ->method('saveFile')
                ->with($this->equalTo($settings['createTempFilename']), $this->equalTo('test-profile-url'));
        }

        if ($settings['createTempDirectory'] !== false) {
            $profileMock
                ->expects($this->at($index++))
                ->method('createTempDirectory')
                ->will($this->returnValue($settings['createTempDirectory']));
        }

        $profileClassMock = $this->getMockClass('\\Profile', array(
            'getS3',
            'createTempFilename',
            'executeSystemCommand',
        ));

        $index = 0;

        if ($settings['s3Mock'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('getS3')
                ->will($this->returnValue($settings['s3Mock']));
        }

        if ($settings['createTempFilename'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('createTempFilename')
                ->will($this->returnValue($settings['createTempFilename']));
        }

        if ($settings['tarExtract'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('executeSystemCommand')
                ->with($this->equalTo(
                    'tar '.
                    '--extract '.
                    '--file '.escapeshellarg('test-filename').' '.
                    '--directory '.escapeshellarg('test-directory')))
                ->will($this->returnValue($settings['tarExtract']));
        }

        if ($settings['rmFilename'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('executeSystemCommand')
                ->with($this->equalTo('rm '.escapeshellarg('test-filename')))
                ->will($this->returnValue($settings['rmFilename']));
        }

        if ($settings['tarCompress'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('executeSystemCommand')
                ->with($this->equalTo(
                    'cd '.escapeshellarg('test-directory').' && '.
                    'tar '.
                    '--create '.
                    '--gzip '.
                    '--file '.escapeshellarg('test-filename').' '.
                    'profile'))
                ->will($this->returnValue($settings['tarCompress']));
        }

        if ($settings['rmDirectory'] !== false) {
            $profileClassMock::staticExpects($this->at($index++))
                ->method('executeSystemCommand')
                ->with($this->equalTo('rm -rf '.escapeshellarg('test-directory')))
                ->will($this->returnValue($settings['rmDirectory']));
        }

        return array($profileMock, $profileClassMock);
    }
}

class TestableS3Batch
{
    public function create_object($s3BucketName, $s3Filename, $options)
    {
    }

    public function send()
    {
    }
}

class TestableS3UploadResponse
{
    public function areOK()
    {
    }
}

