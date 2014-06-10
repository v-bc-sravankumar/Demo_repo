<?php

namespace Unit\Model;

use Model\AbstractSettingsModel;
use PHPUnit_Framework_TestCase;
use Store_Settings;
use Store_Settings_Driver_Dummy;

class TestSettingsModel extends AbstractSettingsModel
{
  protected $fields = array(
    'SettingsFieldOne' => 'field_one',
    'SettingsFieldTwo' => 'field_two',
  );

  public function getFieldOne()
  {
    return $this->getField('field_one');
  }

  public function setFieldOne($value)
  {
    return $this->setField('field_one', $value);
  }

  protected function getValidationRules()
  {
    return array();
  }
}

class AbstractSettingsModelTest extends PHPUnit_Framework_TestCase
{
  protected function getStoreSettings($fail = false)
  {
    $driver = new Store_Settings_Driver_Dummy();
    $driver->config = array(
      'SettingsFieldOne' => 'foo',
      'SettingsFieldTwo' => 'bar',
      'AnotherField' => 'hello',
    );

    if ($fail) {
      $driver->fail = true;
    }

    $settings = new Store_Settings();
    $settings->setDriver($driver);
    $settings->load();

    return $settings;
  }

  public function testLoadSettingsForValidSettingsSucceeds()
  {
    $model = new TestSettingsModel(array(), $this->getStoreSettings());
    $model->load();

    $expected = array(
      'field_one' => 'foo',
      'field_two' => 'bar',
    );

    $this->assertEquals($expected, $model->getData());
  }

  /**
   * @expectedException Model\Exception\LoadException
   */
  public function testFailedLoadSettingsThrowsLoadException()
  {
    $settings = $this->getStoreSettings(true);

    $model = new TestSettingsModel(array(), $settings);
    $model->load();
  }

  public function testSaveSettingsForValidModelSucceeds()
  {
    $settings = $this->getStoreSettings();

    $model = new TestSettingsModel(array(), $settings);
    $model->load();
    $model->setFieldOne('foobar');

    $this->assertEquals($model, $model->save());

    $expected = array(
      'SettingsFieldOne' => 'foobar',
      'SettingsFieldTwo' => 'bar',
      'AnotherField' => 'hello',
    );

    $this->assertEquals($expected, $settings->getDriver()->config);
  }

  /**
   * @expectedException Model\Exception\SaveException
   */
  public function testFailedSaveSettingsThrowsSaveException()
  {
    $settings = $this->getStoreSettings(true);

    $model = new TestSettingsModel(array(), $settings);
    $model->save();
  }
}
