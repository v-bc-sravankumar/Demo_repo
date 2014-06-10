<?php
require_once('LegacyTableModel.php');

class ModelLikeTest extends PHPUnit_Framework_TestCase
{
	/** @var $db Db_Mysql */
	protected $db;

	private static function _getDbConnection()
	{
		return new Db_Mysql(TEST_DB_SERVER, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME);
	}

	public static function setUpBeforeClass()
	{
		$sql = "
			CREATE TABLE IF NOT EXISTS `model_like_legacy_primary_key_test` (
			  `legacyid` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `data` varchar(255) NOT NULL DEFAULT 'test data',
			  PRIMARY KEY (`legacyid`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		";
		self::_getDbConnection()->query($sql);
	}

	public function testInsertIntoLegacyTable()
	{
		$model = new LegacyTableModel();
		$model->setData('abc');

		$this->assertTrue($model->save());
	}

	/**
	 * @depends testInsertIntoLegacyTable
	 * @return LegacyTableModel|mixed
	 */
	public function testSelectFromLegacyTable()
	{
		/** @var $model LegacyTableModel */
		$model = LegacyTableModel::findByData('abc')->first();
		$this->assertTrue($model instanceof LegacyTableModel);
		$this->assertEquals('abc', $model->getData());

		return $model;
	}

	/**
	 * @depends testSelectFromLegacyTable
	 * @param LegacyTableModel $model
	 */
	public function testDeleteFromLegacyTable(LegacyTableModel $model)
	{
		$this->assertTrue($model->delete());
	}

	public static function tearDownAfterClass()
	{
		$sql = "drop table `model_like_legacy_primary_key_test`";
		self::_getDbConnection()->query($sql);
	}

}
