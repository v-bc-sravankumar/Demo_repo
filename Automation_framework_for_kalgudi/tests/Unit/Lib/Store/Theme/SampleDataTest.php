<?php

namespace Unit\Lib\Store\Theme;

use \Store_Config as Config;
use \PHPUnit_Framework_TestCase;

class SampleDataTest extends PHPUnit_Framework_TestCase
{
	public function testInstallerInstalls()
	{
		$db = $this->getMock('Db_Mysql');
		$cache = $this->getMock('ISC_DATA_STORE');

		$dataset = new \StdClass;
		$dataset->data = array(
			'foo' => 'bar'
		);

		$loader = $this->getMock('\Db\Tools\BulkLoader', array('loadFromArray'), array($db));
		$loader
			->expects($this->once())
			->method('loadFromArray')
			->with(array('foo' => 'bar'))
			->will($this->returnValue(true));

		$installer = $this->getMock('Theme\SampleData\Installer', array('loadDataset', 'getBulkLoader', 'clearCaches'), array($cache, $db));
		$installer
			->expects($this->once())
			->method('loadDataset')
			->with(ISC_BASE_PATH . '/config/samples/foo/bar.json')
			->will($this->returnValue($dataset));

		$installer
			->expects($this->once())
			->method('getBulkLoader')
			->will($this->returnValue($loader));

		$installer
			->expects($this->once())
			->method('clearCaches');

		$installer->installSampleData('foo', 'bar');

	}

	public function testConfigSettingsAreUpdatedOnInstall()
	{

	    $db = $this->getMock('Db_Mysql');
	    $cache = $this->getMock('ISC_DATA_STORE');

	    $dataset = new \StdClass;
	    $dataset->settings = array(
	        'baz' => array(
                'beep' => 'bop'
            )
	    );

	    $installer = $this->getMock('Theme\SampleData\Installer', array('loadDataset', 'getBulkLoader', 'scheduleConfig', 'commitConfig', 'clearCaches'), array($cache, $db));
	    $installer
    	    ->expects($this->once())
    	    ->method('loadDataset')
    	    ->with(ISC_BASE_PATH . '/config/samples/foo/bar.json')
    	    ->will($this->returnValue($dataset));

	    $loader = $this->getMock('\Db\Tools\BulkLoader', array('loadFromArray'), array($db));

	    $installer
            ->expects($this->once())
            ->method('getBulkLoader')
            ->will($this->returnValue($loader));

	    $installer
            ->expects($this->atLeastOnce())
            ->method('scheduleConfig')
            ->with('baz', array('beep' => 'bop'));

	    $installer
	       ->expects($this->once())
	       ->method('commitConfig')
	       ->will($this->returnValue(true));

	    $installer
    	    ->expects($this->once())
    	    ->method('clearCaches');

	    $installer->installSampleData('foo', 'bar');

	}

	public function testUninstallerReturnsFalseIfFixtureNotLoaded()
	{
		$db = $this->getMock('Db_Mysql');
		$cache = $this->getMock('ISC_DATA_STORE');

		$uninstaller = $this->getMock('\Theme\SampleData\Uninstaller', array('loadFixture'), array($cache, $db));

		$uninstaller
			->expects($this->never())
			->method('clearCaches');

		$result = $uninstaller->uninstallSampleData('foo', 'bar');
		$this->assertFalse($result);

	}

	public function testUninstallerMakesCorrectCalls()
	{

		$truncate = $this->getMock('\DataModel\TruncateQuery', array('execute'), array(), 'TruncateQuery', false);
		$truncate->expects($this->any())->method('execute')->will($this->returnValue(true));

		$db = $this->getMock('\Db_Mysql', array('Query', 'StartTransaction', 'RollbackTransaction'));

		$db
			->expects($this->once())
			->method('StartTransaction');

		$db
			->expects($this->at(1))
			->method('Query')
			->with("DELETE FROM `bar` WHERE this='that' OR something='else'");

		$cache = $this->getMock('\ISC_DATA_STORE');

		$uninstaller = $this->getMock('\Theme\SampleData\Uninstaller', array('loadFixture', 'getTruncateQuery'), array($cache, $db));

		$fixture = new \StdClass;
		$fixture->tables['foo'] = array();
		$fixture->tables['bar'] = array("this='that'", "something='else'");

		$uninstaller
			->expects($this->once())
			->method('getTruncateQuery')
			->with('foo')
			->will($this->returnValue($truncate));

		$uninstaller
			->expects($this->once())
			->method('loadFixture')
			->will($this->returnValue($fixture));
		;

		$result = $uninstaller->uninstallSampleData('foo', 'bar');
	}

}
