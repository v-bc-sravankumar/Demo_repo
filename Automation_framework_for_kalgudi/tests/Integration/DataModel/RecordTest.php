<?php

namespace Integration\DataModel;

use DataModel_Record;
use DataModel_UnitOfWork;
use DataModel\QueryValue;

/**
 * This class implements model-independent tests of the DataModel_Record suite
 */
class RecordTest extends \Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		$this->dropTestTables();
		$this->createTestTables();
	}

	public function tearDown ()
	{
		parent::tearDown();
		$this->dropTestTables();
	}

	public function dropTestTables ()
	{
		$model = new Test_DataModel_Record;
		$this->assertTrue($this->fixtures->Query("DROP TABLE IF EXISTS `[|PREFIX|]" . $model->getTableName() . "_hasone`"), "failed to drop hasone table");
		$this->assertTrue($this->fixtures->Query("DROP TABLE IF EXISTS `[|PREFIX|]" . $model->getTableName() . "`"), "failed to drop base table");
	}

	public function createTestTables ()
	{
		$model = new Test_DataModel_Record;

		$sql = "
CREATE TABLE `[|PREFIX|]" . $model->getTableName() . "` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `foo` VARCHAR(255) DEFAULT NULL,
  `bar` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB";
		$this->assertTrue($this->fixtures->Query($sql), "failed to create base table");

		$sql = "
CREATE TABLE `[|PREFIX|]" . $model->getTableName() . "_hasone` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `modellike_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hasone_modellike_id`
    FOREIGN KEY (`modellike_id` )
    REFERENCES `[|PREFIX|]test_modellike` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB";
		$this->assertTrue($this->fixtures->Query($sql), "failed to create hasone table");
	}

	public function isMysqlStrict ()
	{
		$global = explode(",", $this->fixtures->FetchOne("SELECT @@GLOBAL.sql_mode"));
		$session = explode(",", $this->fixtures->FetchOne("SELECT @@SESSION.sql_mode"));

		if (
			in_array("STRICT_TRANS_TABLES", $global)
			|| in_array("STRICT_TRANS_TABLES", $session)
			|| in_array("STRICT_ALL_TABLES", $global)
			|| in_array("STRICT_ALL_TABLES", $session)
			) {
			return true;
		}

		return false;
	}

	/**
	 * @covers DataModel_Record::getTableName
	 */
	public function testGetTableName ()
	{
		$model = new Test_DataModel_Record;
		$this->assertEquals('test_modellike', $model->getTableName());
	}

	/**
	 * @covers DataModel_Record::getPrimaryKey
	 */
	public function testGetPrimaryKey ()
	{
		$model = new Test_DataModel_Record;
		$this->assertEquals('id', $model->getPrimaryKey());
	}

	/**
	 * @covers DataModel_Record::_setData
	 * @covers DataModel_Record::_getData
	 */
	public function testSetGetData ()
	{
		$value = 'abc';
		$value2 = '123';
		$model = new Test_DataModel_Record;
		$this->assertNull($model->getFoo());

		$this->assertInstanceOf('DataModel_Record', $model->setFoo($value));
		$this->assertEquals($value, $model->getFoo());

		$this->assertInstanceOf('DataModel_Record', $model->setFoo($value2));
		$this->assertEquals($value2, $model->getFoo());

		$expected = array(
			$model->getTableName() => array(
				'id' => null,
				'foo' => $value2,
				'bar' => null,
			),
		);

		$this->assertEquals($expected, $model->getProtectedData());
	}

	/**
	 * @covers DataModel_Record::_setData
	 */
	public function testSetFailsForInvalidColumn ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->setInvalid('test'), "setting invalid column should have failed");
	}

	/**
	 * @covers DataModel_Record::_getData
	 */
	public function testGetFailsForInvalidColumn ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->getInvalid(), "setting invalid column should have failed");
	}

	/**
	 * @covers DataModel_Record::save
	 */
	public function testInsertWithNoChangesFails ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->save(), "save should have failed");
	}

	/**
	 * @covers DataModel_Record::save
	 * @covers DataModel_Record::_setData
	 */
	public function testUpdateWithNoChangesSucceeds ()
	{
		$model = new Test_DataModel_Record;
		$this->assertSame($model, $model->setFoo('test'));
		$this->assertTrue($model->save(), "failed to insert model");
		$this->assertTrue($model->save(), "failed to update model");
	}

	public function testConstructWithSimpleData ()
	{
		$value = 'abc';
		$data = array(
			'foo' => $value,
		);

		$model = new Test_DataModel_Record($data);
		$this->assertEquals($value, $model->getFoo(), "Foo value mismatch");
		$this->assertNull($model->getId(), "Id value mismatch");

		$expected = array(
			$model->getTableName() => array(
				'foo' => $value,
			),
		);

		$this->assertEquals($expected, $model->getProtectedData());
	}

	public function testConstructWithRelatedData ()
	{
		$model = new Test_ModelRecord_HasOne;
		$parent = new Test_DataModel_Record;

		$value = 'abc';
		$data = array(
			$model->getTableName() => array(
				'modellike_id' => 1,
			),
			$parent->getTableName() => array(
				'id' => 1,
				'foo' => $value,
			),
		);

		$model = new Test_ModelRecord_HasOne($data);
		$parent = $model->getModelLike();

		$this->assertEquals($value, $parent->getFoo(), "Foo value mismatch");
		$this->assertNull($model->getId(), "Id value mismatch");

		$expected = $data;
		$this->assertEquals($expected, $model->getProtectedData());
	}

	public function testCreateWithValidDataSucceeds ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());
		$this->assertEquals(1, $model->getId());

		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());
		$this->assertEquals(2, $model->getId());
	}

	public function testCreateWithInvalidDataFails ()
	{
		if (!$this->isMysqlStrict()) {
			// non-strict mysql will truncate with a warning instead of producing an error
			$this->markTestSkipped();
			return;
		}

		$model = new Test_DataModel_Record;
		$model->setFoo(str_repeat('a', 256));
		$this->assertFalse($model->save());
		$this->assertNull($model->getId());
		$this->assertFalse($model->load(1));
	}

	public function testBeforeInsertRuns ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertFalse($model->beforeInsert);
		$model->save();
		$this->assertTrue($model->beforeInsert);
	}

	public function testAfterInsertRuns ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertFalse($model->afterInsert);
		$model->save();
		$this->assertTrue($model->afterInsert);
	}

	public function testExplicitLoadSucceeds ()
	{
		$model = new Test_DataModel_Record;
		$this->assertSame($model, $model->setFoo('abc'));
		$this->assertTrue($model->save(), "failed to save model");

		$loaded = new Test_DataModel_Record;
		$this->assertTrue($loaded->load(1), "failed to load model");
		$this->assertSame($model->getFoo(), $loaded->getFoo(), "test value mismatch");
	}

	public function testImpliedLoadSucceeds ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$model->save();

		$loaded = new Test_DataModel_Record;
		$loaded->setId(1);
		$this->assertTrue($loaded->load());
		$this->assertEquals($model->getFoo(), $loaded->getFoo());
	}

	public function testEmptyLoadFails ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->load());
	}

	public function testLoadForNonExistingRecordFails ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->load(1));
	}

	public function testGetClonerIdBeforeCloningReturnsFalse ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->getClonerId());
	}

	/**
	 * @covers DataModel_Record::__clone
	 * @covers DataModel_Record::getClonerId
	 */
	public function testCloneCreatesSameClassWithSameData ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');

		$clone = $model->copy();
		$this->assertInstanceOf(get_class($model), $clone);
		$this->assertEquals($model->getFoo(), $clone->getFoo());
		$this->assertNull($clone->getId());
		$this->assertSame($model->getId(), $clone->getClonerId());
	}

	/**
	 * @covers DataModel_Record::__clone
	 */
	public function testCloneAfterSaveCopiesDataExceptId ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$model->save();

		$cloned = $model->copy();
		$this->assertNull($cloned->getId());
	}

	/**
	 * @covers DataModel_Record::__clone
	 * @covers DataModel_Record::getCloner
	 */
	public function testClonerReturnsCorrectReferences ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$model->save();

		$cloned = $model->copy();
		$this->assertNotSame($cloned, $model);
		$this->assertSame($cloned->getCloner(), $model);
	}

	public function testIsClone() {
		$model = new Test_DataModel_Record;
		$model->save();
		$this->assertFalse($model->isClone());

		$cloned = $model->copy();
		$this->assertTrue($cloned->isClone());
	}

	public function testCloneAndSaveCreatesNewRecord ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save(), "failed to save model: " . $model->getDb()->GetErrorMsg());

		$cloned = $model->copy();
		$this->assertTrue($cloned->save(), "failed to save clone: " . $model->getDb()->GetErrorMsg());
		$this->assertEquals(1, $model->getId(), "original model id mismatch");
		$this->assertEquals(2, $cloned->getId(), "cloned model id mismatch");
	}

	public function testCreateAndUpdate ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->save();
		$model->setFoo('123');
		$this->assertTrue($model->save());

		$model = new Test_DataModel_Record;
		$model->load(1);
		$this->assertEquals(1, $model->getId());
		$this->assertEquals('123', $model->getFoo());
	}

	public function testLoadAndUpdate ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->save();

		$model = new Test_DataModel_Record;
		$model->load(1);
		$model->setFoo('123');
		$this->assertTrue($model->save());

		$model = new Test_DataModel_Record;
		$model->load(1);
		$this->assertEquals(1, $model->getId());
		$this->assertEquals('123', $model->getFoo());
	}

	public function testUpdateWithSameInitialValue ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->save();

		$model = new Test_DataModel_Record;
		$model->load(1);
		$model->setFoo('123');
		$model->setFoo('abc');
		$model->save();

		$model = new Test_DataModel_Record;
		$model->load(1);
		$this->assertEquals(1, $model->getId());
		$this->assertEquals('abc', $model->getFoo());
	}

	public function testExplicitPartialUpdateSucceeds ()
	{
		// this test inserts a full record and then attempts to manually update only one field of that record using
		// a new model with setid -> save

		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->setBar('123');
		$this->assertTrue($model->save(), "failed to insert model");

		$id = $model->getId();

		$model = new Test_DataModel_Record;
		$model->setId($id)
			->setFoo('def');
		$this->assertTrue($model->save(), "failed to update model");
		$this->assertSame($id, $model->getId(), "id mismatch - did an insert occur instead of an update?");

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->load($id), "failed to load model");
		$this->assertEquals('def', $model->getFoo(), "Foo was not updated");
		$this->assertEquals('123', $model->getBar(), "Bar was updated, but shouldn't've been");
	}

	public function testBeforeUpdateRuns ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->save();
		$model->setFoo('123')
			->save();
		$this->assertTrue($model->beforeUpdate);
	}

	public function testAfterUpdateRuns ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc')
			->save();
		$model->setFoo('123')
			->save();
		$this->assertTrue($model->afterUpdate);
	}

	public function testUpdateWithInvalidDataFails ()
	{
		if (!$this->isMysqlStrict()) {
			// non-strict mysql will truncate with a warning instead of producing an error
			$this->markTestSkipped();
			return;
		}

		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());
		$model->setFoo(str_repeat('a', 256));
		$this->assertFalse($model->save());
		$this->assertTrue($model->load());
		$this->assertEquals('abc', $model->getFoo());
	}

	public function testCreateAndDelete ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());
		$this->assertTrue($model->delete());
		$this->assertFalse($model->load(1));
	}

	public function testLoadAndDelete ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->load(1));
		$this->assertTrue($model->delete());
		$this->assertFalse($model->load(1));
	}

	public function testSetIdAndDelete ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_DataModel_Record;
		$model->setId(1);
		$this->assertTrue($model->delete());
		$this->assertFalse($model->load(1));
	}

	public function testDeleteWithInvalidIdFails ()
	{
		$model = new Test_DataModel_Record;
		$this->assertFalse($model->delete());
	}

	public function testBeforeDeleteRuns ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());
		$this->assertTrue($model->delete());
		$this->assertTrue($model->beforeDelete);
	}

	public function testOuterTransactionRemains ()
	{
		// this tests to make sure that a manual transaction outside the ModelLike still works even though ModelLike
		// manages its own transactions

		$model = new Test_DataModel_Record;
		$model->getDb()->StartTransaction();

		$model->setFoo('abc');
		$this->assertTrue($model->save(), "failed to save new model");
		$id = $model->getId();

		$model->getDb()->RollbackTransaction();
		$this->assertFalse($model->load($id), "load($id) should have failed");
	}

	public function testBeforeInsertRollback ()
	{
		$model = new Test_Data_BeforeBlockerModelRecord;
		$model->setFoo('abc');
		$this->assertFalse($model->save());
		$this->assertNull($model->getId());
		$this->assertFalse($model->load(1));
	}

	public function testAfterInsertRollback ()
	{
		$model = new Test_Data_AfterBlockerModelRecord;
		$model->setFoo('abc');
		$this->assertFalse($model->save(), "save should have failed");
		$this->assertNull($model->getId(), "Id value mismatch");
		$this->assertFalse($model->load(1), "load(1) should have failed");
	}

	public function testBeforeUpdateRollback ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_Data_BeforeBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$model->setFoo('def');
		$this->assertFalse($model->save());
		$this->assertEquals('def', $model->getFoo());

		$model = new Test_Data_BeforeBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$this->assertEquals('abc', $model->getFoo());
	}

	public function testAfterUpdateRollback ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_Data_AfterBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$model->setFoo('def');
		$this->assertFalse($model->save());
		$this->assertEquals('def', $model->getFoo());

		$model = new Test_Data_AfterBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$this->assertEquals('abc', $model->getFoo());
	}

	public function testBeforeDeleteRollback ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_Data_BeforeBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$this->assertFalse($model->delete());
	}

	public function testAfterDeleteRollback ()
	{
		$model = new Test_DataModel_Record;
		$model->setFoo('abc');
		$this->assertTrue($model->save());

		$model = new Test_Data_AfterBlockerModelRecord;
		$this->assertTrue($model->load(1));
		$this->assertFalse($model->delete());
	}

	public function createFindTestData ()
	{
		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('1')->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('2')->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('3')->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('12')->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('23')->save());

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->setFoo('13')->save());
	}

	public function testCorrectQueryModelClassName ()
	{
		$models = Test_DataModel_Record::find();
		$this->assertEquals('Integration\DataModel\Test_DataModel_Record', $models->getQueryModelClassName());
	}

	public function testCorrectHydrationClassName ()
	{
		$models = Test_DataModel_Record::find();
		$this->assertEquals('Integration\DataModel\Test_DataModel_Record', $models->getHydrationModelClassName());
	}

	public function testCorrectHydrationModel ()
	{
		$models = Test_DataModel_Record::find();
		$this->assertInstanceOf('\Integration\DataModel\Test_DataModel_Record', $models->getHydrationModel());
	}

	public function testFindAll ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find();
		$results = array();
		foreach ($models as $model) {
			$results[$model->getId()] = $model->getFoo();
		}
		$expected = array(
			1 => '1',
			2 => '2',
			3 => '3',
			4 => '12',
			5 => '23',
			6 => '13',
		);
		$this->assertEquals($expected, $results);
		$this->assertEquals(6, $models->count());
	}

	public function testCountAll ()
	{
		$this->createFindTestData();
		$this->assertEquals(6, Test_DataModel_Record::find()->count());
	}

	public function testBasicFind ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find("foo LIKE '1%'");
		$counter = 0;
		foreach ($models as $model) {
			$counter++;
			$this->assertInstanceOf('\Integration\DataModel\Test_DataModel_Record', $model);
		}
		$this->assertEquals(3, $counter);
	}

	public function testBasicFindCount ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find("foo LIKE '1%'");
		$this->assertEquals(3, $models->count());
	}

	public function testFindWithLimit ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find("foo LIKE '1%'")->limit(2);
		$counter = 0;
		foreach ($models as $model) {
			$counter++;
			$this->assertInstanceOf('\Integration\DataModel\Test_DataModel_Record', $model);
		}
		$this->assertEquals(2, $counter);
		$this->assertEquals(3, $models->count());
	}

	public function testFindWithSort ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find()->sort("foo", "desc");
		$results = array();
		foreach ($models as $model) {
			$results[] = $model->getFoo();
		}
		$expected = array(
			'3',
			'23',
			'2',
			'13',
			'12',
			'1',
		);
		$this->assertEquals($expected, $results);
	}

	public function testFindWithLimitOffset ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find("foo LIKE '1%'")->sort("foo", "DESC")->limit(2)->offset(1);
		$results = array();
		foreach ($models as $model) {
			$results[] = $model->getFoo();
		}
		$expected = array(
			'12',
			'1',
		);
		$this->assertEquals($expected, $results);
		$this->assertEquals(3, $models->count());
	}

	public function testLimitCount ()
	{
		$this->createFindTestData();
		$models = Test_DataModel_Record::find()->limit(2);
		$counter = 0;
		foreach ($models as $model) {
			$counter++;
		}
		$this->assertEquals(2, $counter);
		$this->assertEquals(6, $models->count());
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage SQL Error
	 */
	public function testCreateWithNoParentFails ()
	{
		$child = new Test_ModelRecord_HasOne;
		$child->setModellikeId(1);
		$this->assertFalse($child->save());
	}

	public function testDeleteWithChildDataFails ()
	{
		$parent = new Test_DataModel_Record;
		$child = new Test_ModelRecord_HasOne;

		$parent->setFoo('bar')->save();
		$child->setModellikeId($parent->getId())->save();

		$this->assertFalse($parent->delete());
	}

	public function testUpdateWithNoParentFails ()
	{
		$parent = new Test_DataModel_Record;
		$child = new Test_ModelRecord_HasOne;

		$parent->setFoo('bar')->save();
		$child->setModellikeId($parent->getId())->save();

		$child->setModellikeId(2);
		$this->assertFalse($child->save());
	}

	public function testDeleteCascades ()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$child = new Test_ModelRecord_HasOne;

		$parent->setFoo('bar')->save();
		$child->setModellikeId($parent->getId())->save();

		$this->assertTrue($parent->delete());
		$this->assertFalse($child->load());
	}

	/**
	 * Removes line breaks and tabs from query and removes extra spaces.
	 * Note that this method is purely for testing purposes and unsafe as it would remove extra spaces even from strings.
	 * @param string $query
	 * @return string
	 */
	private static function trimQuery($query)
	{
		$originalLen = strlen($query);
		$query = str_replace("\n", " ", $query);
		$query = str_replace("\t", " ", $query);
		$query = str_replace("  ", " ", $query);
		if (strlen($query) != $originalLen) {
			return self::trimQuery($query);
		}
		return trim($query);
	}

	public function testGetFindQueryWithWhereAndOneJoin()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$joins = array(
			array(
				'model'      => new Test_ModelRecord_HasOne,
				'foreignKey' => "id",
				'references' => "modellike_id",
				'joinType'   => "LEFT JOIN",
			)
		);
		$query = $parent->getFindQuery("id = 5", $joins);
		$actualQuery = self::trimQuery($query);
		$expectedQuery =
			"SELECT `test_modellike`.`id` AS `test_modellike.id`, `test_modellike`.`foo` AS `test_modellike.foo`, `test_modellike`.`bar` AS `test_modellike.bar` , `test_modellike_hasone`.`id` AS `test_modellike_hasone.id`, `test_modellike_hasone`.`modellike_id` AS `test_modellike_hasone.modellike_id` " .
			"FROM `[|PREFIX|]test_modellike` AS `test_modellike` ".
			"LEFT JOIN `[|PREFIX|]test_modellike_hasone` AS `test_modellike_hasone` ON `test_modellike_hasone`.`modellike_id` = `test_modellike`.`id` WHERE id = 5 /*%ORDERBY%*/ /*%LIMIT%*/";
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetCountQueryWithWhereAndOneJoin()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
			$joins = array(
				array(
					'model'      => new Test_ModelRecord_HasOne,
					'foreignKey' => "modellike_id",
					'references' => "id",
					'joinType'   => "LEFT JOIN",
				)
			);
			$query = $parent->getCountquery("id = 5", $joins);
			$actualQuery = self::trimQuery($query);
			$expectedQuery =
				"SELECT COUNT(*) " .
				"FROM `[|PREFIX|]test_modellike` AS `test_modellike` ".
				"LEFT JOIN `[|PREFIX|]test_modellike_hasone` AS `test_modellike_hasone` ON `test_modellike_hasone`.`id` = `test_modellike`.`modellike_id` WHERE id = 5";
			$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetFindQueryWithWhereAndOneJoinTwoJoinColumns()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$joins = array(
			array(
				'model'      => new Test_ModelRecord_HasOne,
				'foreignKey' => array("id", "foo"),
				'references' => array("modellike_id", "id"),
				'joinType'   => "LEFT JOIN",
			)
		);
		$query = $parent->getFindQuery("id = 5", $joins);
		$actualQuery = self::trimQuery($query);
		$expectedQuery =
			"SELECT `test_modellike`.`id` AS `test_modellike.id`, `test_modellike`.`foo` AS `test_modellike.foo`, `test_modellike`.`bar` AS `test_modellike.bar` , `test_modellike_hasone`.`id` AS `test_modellike_hasone.id`, `test_modellike_hasone`.`modellike_id` AS `test_modellike_hasone.modellike_id` " .
			"FROM `[|PREFIX|]test_modellike` AS `test_modellike` ".
			"LEFT JOIN `[|PREFIX|]test_modellike_hasone` AS `test_modellike_hasone` ".
			"ON `test_modellike_hasone`.`modellike_id` = `test_modellike`.`id` ".
			"AND `test_modellike_hasone`.`id` = `test_modellike`.`foo` ".
			"WHERE id = 5 /*%ORDERBY%*/ /*%LIMIT%*/";
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetFindQueryWithWhereAndOneJoinWithIntConstant()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$joins = array(
			array(
				'model'      => new Test_ModelRecord_HasOne,
				'foreignKey' => array(4),
				'references' => array("joined_side_column"),
				'joinType'   => "LEFT JOIN",
			)
		);
		$query = $parent->getFindQuery("id = 5", $joins);
		$actualQuery = self::trimQuery($query);
		$expectedQuery =
			"SELECT `test_modellike`.`id` AS `test_modellike.id`, `test_modellike`.`foo` AS `test_modellike.foo`, `test_modellike`.`bar` AS `test_modellike.bar` , `test_modellike_hasone`.`id` AS `test_modellike_hasone.id`, `test_modellike_hasone`.`modellike_id` AS `test_modellike_hasone.modellike_id` " .
			"FROM `[|PREFIX|]test_modellike` AS `test_modellike` ".
			"LEFT JOIN `[|PREFIX|]test_modellike_hasone` AS `test_modellike_hasone` ".
			"ON `test_modellike_hasone`.`joined_side_column` = 4 ".
			"WHERE id = 5 /*%ORDERBY%*/ /*%LIMIT%*/";
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetFindQueryWithJoinWithQueryValue()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$joins = array(
			array(
				'model'      => new Test_ModelRecord_HasOne,
				'foreignKey' => array("id", new QueryValue("foobar")),
				'references' => array("modellike_id", "id"),
				'joinType'   => "LEFT JOIN",
			)
		);
		$query = $parent->getFindQuery("id = 5", $joins);
		$actualQuery = self::trimQuery($query);
		$expectedQuery =
			"SELECT `test_modellike`.`id` AS `test_modellike.id`, `test_modellike`.`foo` AS `test_modellike.foo`, `test_modellike`.`bar` AS `test_modellike.bar` , `test_modellike_hasone`.`id` AS `test_modellike_hasone.id`, `test_modellike_hasone`.`modellike_id` AS `test_modellike_hasone.modellike_id` " .
			"FROM `[|PREFIX|]test_modellike` AS `test_modellike` ".
			"LEFT JOIN `[|PREFIX|]test_modellike_hasone` AS `test_modellike_hasone` ON `test_modellike_hasone`.`modellike_id` = `test_modellike`.`id` " .
			"AND `test_modellike_hasone`.`id` = 'foobar' " .
			"WHERE id = 5 /*%ORDERBY%*/ /*%LIMIT%*/";
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testCustomFindWithRelatedData ()
	{
		$parent = new Test_Data_CascadeDeleteRecord;
		$parent->setFoo('bar')->save();

		$child = new Test_ModelRecord_HasOne;
		$child->setModellikeId($parent->getId())->save();

		$child = $child->copy();
		$child->save();

		$child = $child->copy();
		$child->save();

		$children = Test_ModelRecord_HasOne::find()->join(new Test_Data_CascadeDeleteRecord, 'modellike_id');
		$this->assertEquals(3, $children->count());
		foreach ($children as $child) {
			$this->assertEquals('bar', $child->getModellike()->getFoo(), "failed child record match with implied fkey table");
		}

		$children = Test_ModelRecord_HasOne::find()->join(new Test_Data_CascadeDeleteRecord, 'test_modellike_hasone.modellike_id');
		$this->assertEquals(3, $children->count());
		foreach ($children as $child) {
			$this->assertEquals('bar', $child->getModellike()->getFoo(), "failed child record match with explicit fkey table");
		}
	}

	public function testColumnCanBeInsertedAsNull ()
	{
		$model = new Test_DataModel_Record;
		$this->assertSame($model, $model->setFoo('foo'), "setFoo return value mismatch");
		$this->assertSame($model, $model->setBar(null), "setBar return value mismatch");
		$this->assertTrue($model->save(), "failed to save model");
		$id = $model->getId();

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->load($id), "failed to load model");
		$this->assertSame('foo', $model->getFoo(), "Foo value mismatch");
		$this->assertNull($model->getBar(), "Bar value mismatch");
	}

	public function testColumnCanBeUpdatedToNull ()
	{
		$model = new Test_DataModel_Record;
		$this->assertSame($model, $model->setFoo('foo'), "setFoo return value mismatch");
		$this->assertSame($model, $model->setBar('bar'), "setBar return value mismatch");
		$this->assertTrue($model->save(), "failed to insert model");
		$id = $model->getId();

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->load($id), "failed to load model");
		$this->assertSame('foo', $model->getFoo(), "Foo value mismatch");
		$this->assertSame('bar', $model->getBar(), "Bar value mismatch");
		$this->assertSame($model, $model->setBar(null));
		$this->assertTrue($model->save(), "failed to update model");

		$model = new Test_DataModel_Record;
		$this->assertTrue($model->load($id), "failed to load model after update");
		$this->assertNull($model->getBar(), "expected Bar to be null");
	}

	public function testDeleteIncludesContextDataInEvent()
	{
		$model = new Test_DataModel_Record();
		$model->setFoo('foo');
		$model->save();

		$context = array(
			'foo' => 'bar',
		);

		$self = $this;
		$triggered = false;
		\Interspire_Event::bind(
			'test.datamodel.deleted',
			function ($event) use ($self, &$triggered, $model, $context) {
				$self->assertEquals($model, $event->data['before']);
				$self->assertEquals($context, $event->data['extra']);

				$triggered = true;
			}
		);

		$model->delete(null, $context);

		$this->assertTrue($triggered, 'test.datamodel.deleted was not triggered');
	}
}

class Test_ModelRecord_HasOne extends DataModel_Record
{
	protected $_tableName = 'test_modellike_hasone';

	protected $_data = array(
		'id' => null,
		'modellike_id' => null,
	);

	protected $_modelLike;

	public function getProtectedData ()
	{
		return $this->_data;
	}

	public function getModellikeId ()
	{
		return $this->_getData('modellike_id');
	}

	public function setModellikeId ($value)
	{
		return $this->_setData('modellike_id', (int)$value);
	}

	public function getModelLike ()
	{
		if ($this->_modelLike === null) {
			// for the purpose of this test, this is coded to only support instanciation via preloaded data
			$this->_modelLike = new Test_DataModel_Record($this->_data);
		}
		return $this->_modelLike;
	}
}

class Test_DataModel_Record extends DataModel_Record
{
	protected $_tableName = 'test_modellike';

	protected $_data = array(
		'id' => null,
		'foo' => null,
		'bar' => null,
	);

	protected $_deletedEvent = 'test.datamodel.deleted';

	public $beforeInsert = false;
	public $afterInsert = false;
	public $beforeUpdate = false;
	public $afterUpdate = false;
	public $beforeDelete = false;
	public $afterDelete = false;

	protected function _beforeInsert (DataModel_UnitOfWork $unitOfWork)
	{
		$this->beforeInsert = true;
	}

	protected function _afterInsert (DataModel_UnitOfWork $unitOfWork)
	{
		$this->afterInsert = true;
	}

	protected function _beforeUpdate (DataModel_UnitOfWork $unitOfWork)
	{
		$this->beforeUpdate = true;
	}

	protected function _afterUpdate (DataModel_UnitOfWork $unitOfWork)
	{
		$this->afterUpdate = true;
	}

	protected function _beforeDelete (DataModel_UnitOfWork $unitOfWork)
	{
		$this->beforeDelete = true;
	}

	protected function _afterDelete (DataModel_UnitOfWork $unitOfWork)
	{
		$this->afterDelete = true;
	}

	public function getProtectedData ()
	{
		return array($this->getTableName() => array_merge($this->_data[$this->getTableName()], $this->_dirty));
	}

	public function getFoo ()
	{
		return $this->_getData('foo');
	}

	public function setFoo ($value)
	{
		if ($value !== null) {
			$value = (string)$value;
		}
		return $this->_setData('foo', $value);
	}

	public function getBar ()
	{
		return $this->_getData('bar');
	}

	public function setBar ($value)
	{
		if ($value !== null) {
			$value = (string)$value;
		}
		return $this->_setData('bar', $value);
	}

	public function getInvalid ()
	{
		return $this->_getData('invalid');
	}

	public function setInvalid ($value)
	{
		if ($value !== null) {
			$value = (string)$value;
		}
		return $this->_setData('invalid', $value);
	}
}

class Test_Data_CascadeDeleteRecord extends Test_DataModel_Record
{
	protected function _beforeDelete (DataModel_UnitOfWork $unitOfWork)
	{
		$children = Test_ModelRecord_HasOne::find('modellike_id = ' . $this->getId());
		foreach ($children as $child) {
			if (!$child->delete()) {
				return false;
			}
		}
	}
}

class Test_Data_BeforeBlockerModelRecord extends Test_DataModel_Record
{
	protected function _beforeInsert (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}

	protected function _beforeUpdate (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}

	protected function _beforeDelete (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}
}

class Test_Data_AfterBlockerModelRecord extends Test_DataModel_Record
{
	protected function _afterInsert (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}

	protected function _afterUpdate (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}

	protected function _afterDelete (DataModel_UnitOfWork $unitOfWork)
	{
		return false;
	}
}
