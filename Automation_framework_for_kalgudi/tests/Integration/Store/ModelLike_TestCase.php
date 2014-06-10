<?php

/**
 * Implements base code and a CRUD smoke-test framework for ModelLike classes
 */
abstract class ModelLike_TestCase extends Interspire_IntegrationTest
{
	abstract protected function _getCrudSmokeInstance ();

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getName';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setName';
	}

	protected function _getCrudSmokeValue1 ()
	{
		return 'foo';
	}

	protected function _getCrudSmokeValue2 ()
	{
		return 'bar';
	}

	protected function _getFindSmokeSetPattern ()
	{
		return 'test_smoke_%s';
	}

	protected function _getFindSmokeLikePattern ()
	{
		return 'test_smoke_%';
	}

	protected function _getFindSmokeInstance ()
	{
		return $this->_getCrudSmokeInstance();
	}

	protected function _getFindSmokeGetMethod ()
	{
		return $this->_getCrudSmokeGetMethod();
	}

	protected function _getFindSmokeSetMethod ()
	{
		return $this->_getCrudSmokeSetMethod();
	}

	protected function _getFindSmokeColumn ()
	{
		return 'name';
	}

	public function testCreateReadUpdateDeleteSmoke ()
	{
		$getMethod = $this->_getCrudSmokeGetMethod();
		$setMethod = $this->_getCrudSmokeSetMethod();
		$value1 = $this->_getCrudSmokeValue1();
		$value2 = $this->_getCrudSmokeValue2();

		$model = $this->_getCrudSmokeInstance();
		$this->assertSame($model, $model->$setMethod($value1), "set method return value mismatch");

		$model->getDb()->clearError();
		$this->assertTrue($model->save(), "insert failed, db error: " . $model->getDb()->getErrorMsg());
		$id = $model->getId();
		$this->assertGreaterThan(0, $id, "getId is <= 0");
		$this->assertTrue($model->load(), "insert-load failed, db error: " . $model->getDb()->getErrorMsg());
		$this->assertEquals($value1, $model->$getMethod(), "inserted value is incorrect");
		$this->assertInstanceOf(get_class($model), $model->$setMethod($value2), "set method is not returning class instance");
		$this->assertTrue($model->save(), "update failed, db error: " . $model->getDb()->getErrorMsg());
		$this->assertEquals($id, $model->getId(), "id changed after update, did insert occur instead?");
		$this->assertTrue($model->load(), "update-load failed, db error: " . $model->getDb()->getErrorMsg());
		$this->assertEquals($value2, $model->$getMethod(), "updated value is incorrect");
		$this->assertTrue($model->delete(), "delete failed, db error: " . $model->getDb()->getErrorMsg());
		$this->assertFalse($model->load(), "load succeeded after delete");
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		// do not run this test by default
		return array();
	}

	/**
	 * @dataProvider dataProviderCloneCorrectlySubClones
	 */
	public function testCloneCorrectlySubClones ($cloneMethod)
	{
		$model = $this->_getCrudSmokeInstance();
		$subInstance = $model->$cloneMethod();

		if (!is_object($subInstance)) {
			$this->fail("$cloneMethod is not returning an object, check the test setup to make sure $cloneMethod will return an object");
		}

		$subInstanceAgain = $model->$cloneMethod();
		$this->assertSame($subInstance, $subInstanceAgain, "two consecutive calls to $cloneMethod returned different instances, expected the same instance via internal caching");

		$cloned = $model->copy();
		$this->assertNull($cloned->getId());
		$clonedSubInstance = $cloned->$cloneMethod();

		$this->assertNotSame($subInstance, $clonedSubInstance, "a call to $cloneMethod returned the same instance before and after cloning, expected different instances");
	}

	public function testFindSmoke ()
	{
		$getMethod = $this->_getFindSmokeGetMethod();
		$setMethod = $this->_getFindSmokeSetMethod();
		$setPattern = $this->_getFindSmokeSetPattern();

		if (!$getMethod || !$setMethod || !$setPattern) {
			$this->markTestSkipped();
			return false;
		}

		$createdModels = array();
		$expected = array();

		$model = $this->_getFindSmokeInstance();
		$createdModels[] = $model;
		$value = sprintf($setPattern, 1);
		$this->assertTrue($model->$setMethod($value)->save(), "failed to save find-test data with $setMethod -> $value: " . $model->getDb()->GetErrorMsg());
		array_unshift($expected, $value);

		$model = $this->_getFindSmokeInstance();
		$createdModels[] = $model;
		$value = sprintf($setPattern, 2);
		$this->assertTrue($model->$setMethod($value)->save(), "failed to save find-test data with $setMethod -> $value");
		array_unshift($expected, $value);

		$model = $this->_getFindSmokeInstance();
		$createdModels[] = $model;
		$value = sprintf($setPattern, 3);
		$this->assertTrue($model->$setMethod($value)->save(), "failed to save find-test data with $setMethod -> $value");
		array_unshift($expected, $value);

		$model = $this->_getFindSmokeInstance();
		$createdModels[] = $model;
		$value = sprintf($setPattern, 4);
		$this->assertTrue($model->$setMethod($value)->save(), "failed to save find-test data with $setMethod -> $value");
		array_unshift($expected, $value);

		$class = get_class($model);
		$query = $this->_getFindSmokeColumn() . " LIKE '" . $this->_getFindSmokeLikePattern() . "'";
		$models = call_user_func($class . '::find', $query);
		$models->sort($this->_getFindSmokeColumn(), 'desc');

		$count = $models->count();
		$this->assertNotEquals(false, $count, "find query failed");
		$this->assertEquals(4, $count, "find result count mismatch");

		$actual = array();
		foreach ($models as $model) {
			$this->assertInstanceOf($class, $model);
			$actual[] = $model->$getMethod();
		}

		$this->assertEquals($expected, $actual, "array result mismatch");

		// clean up created content
		foreach ($createdModels as $model) {
			$model->delete();
		}
	}
}
