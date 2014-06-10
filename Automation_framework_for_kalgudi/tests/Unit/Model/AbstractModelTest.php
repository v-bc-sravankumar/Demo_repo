<?php

namespace Unit\Model;

use Model\AbstractModel;
use PHPUnit_Framework_TestCase;

class TestModel extends AbstractModel
{
  protected $fields = array(
    'field_one',
    'field_two',
    'field_three',
  );

  protected function beforeCommit()
  {
    if ($this->getValidField() == 'hook') {
      $this->setValidField('changed');
    }
  }

  public function getValidField()
  {
    return $this->getField('field_one');
  }

  public function setValidField($value)
  {
    return $this->setField('field_one', $value);
  }

  public function getInvalidField()
  {
    return $this->getField('invalid_field');
  }

  public function setInvalidField($value)
  {
    return $this->setField('invalid_field', $value);
  }
}

class AbstractModelTest extends PHPUnit_Framework_TestCase
{
  public function testGetFields()
  {
    $expected = array(
      'field_one',
      'field_two',
      'field_three',
    );

    $model = new TestModel();

    $this->assertEquals($expected, $model->getFields());
  }

  public function testSetFieldForValidFieldReturnsModel()
  {
    $model = new TestModel();
    $this->assertEquals($model, $model->setValidField('foo'));
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage invalid_field
   */
  public function testSetFieldForInvalidFieldThrowsException()
  {
    $model = new TestModel();
    $model->setInvalidField('foo');
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage invalid_field
   */
  public function testGetInvalidFieldThrowsException()
  {
    $model = new TestModel();
    $model->getInvalidField();
  }

  public function testGetFieldForValidFieldReturnsSetData()
  {
    $model = new TestModel();
    $model->setValidField('foo');
    $this->assertEquals('foo', $model->getValidField());
  }

  public function testGetDataMatchesConstructedModelData()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => null,
    );

    $model = new TestModel($data);
    $this->assertEquals($data, $model->getData());
  }

  public function testGetDataMatchesSetData()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => null,
    );

    $model = new TestModel();
    $model->setData($data);
    $this->assertEquals($data, $model->getData());
  }

  public function testGetDataForEmptyModelReturnsArrayOfNulls()
  {
    $expected = array(
      'field_one' => null,
      'field_two' => null,
      'field_three' => null,
    );

    $model = new TestModel();
    $this->assertEquals($expected, $model->getData());
  }

  public function testGetDataContainsSetFields()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => 'hello',
    );

    $expected = array(
      'field_one' => 'test',
      'field_two' => 'bar',
      'field_three' => 'hello',
    );

    $model = new TestModel($data);
    $model->setValidField('test');
    $this->assertEquals($expected, $model->getData());
  }

  public function testSetDataRemovesInvalidFields()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'invalid_field' => 'hello',
    );

    $expected = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => null,
    );

    $model = new TestModel();
    $model->setData($data);
    $this->assertEquals($expected, $model->getData());
  }

  public function testRevertRestoresModelToOriginalData()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => 'hello',
    );

    $model = new TestModel($data);
    $model->save();

    $model->setValidField('test');
    $model->revert();
    $this->assertEquals($data, $model->getData());
  }

  public function testSetDataRevertsPendingChanges()
  {
    $model = new TestModel();
    $model->setValidField('test');

    $newData = array(
      'field_one' => 'one',
      'field_two' => 'two',
      'field_three' => 'three',
    );

    $model->setData($newData);
    $this->assertEquals($newData, $model->getData());
  }

  public function testSetDataForPartialDataOnlyChangesNewFields()
  {
     $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => 'hello',
    );

    $model = new TestModel($data);

    $newData = array(
      'field_one' => 'foobar',
      'field_two' => null,
    );

    $model->setData($newData);

    $expected = array(
      'field_one' => 'foobar',
      'field_two' => null,
      'field_three' => 'hello',
    );

    $this->assertEquals($expected, $model->getData());
  }

  public function testSaveReturnsInstance()
  {
    $model = new TestModel();
    $this->assertEquals($model, $model->save());
  }

  public function testSaveCommitsChanges()
  {
    $data = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
      'field_three' => null,
    );

    $model = new TestModel($data);
    $model->setValidField('hello');
    $model->save();

    $expected = array(
      'field_one' => 'hello',
      'field_two' => 'bar',
      'field_three' => null,
    );

    $model->revert();
    $this->assertEquals($expected, $model->getData());
  }

  public function testBeforeCommitHook()
  {
    $model = new TestModel();
    $model->setValidField('hook');
    $model->save();

    $this->assertEquals('changed', $model->getValidField());
  }
}
