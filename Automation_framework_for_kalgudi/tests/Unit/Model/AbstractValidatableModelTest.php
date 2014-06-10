<?php

namespace Unit\Model;

use Model\AbstractValidatableModel;
use Model\Exception\ValidationException;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\Constraints as Assert;

class TestValidatableModel extends AbstractValidatableModel
{
  protected $fields = array(
    'email_field',
    'int_field',
  );

  public function getValidationRules()
  {
    return array(
      'email_field' => new Assert\Email(array('message' => 'invalid email')),
      'int_field' => array(
        new Assert\Type(array('type' => 'integer', 'message' => 'invalid int')),
        new Assert\NotBlank(array('message' => 'blank int')),
      ),
    );
  }
}

class AbstractValidatableModelTest extends PHPUnit_Framework_TestCase
{
  private function assertValidationErrors($errors)
  {
    foreach ($errors as $error) {
      $this->assertInstanceOf('Model\\ValidationError', $error);
    }

    $this->assertArrayHasKey('email_field', $errors);
    $this->assertContains('email', $errors['email_field']->getConstraints());
    $this->assertEquals('foo', $errors['email_field']->getValue());

    $this->assertArrayHasKey('int_field', $errors);
    $this->assertContains('type', $errors['int_field']->getConstraints());
    $this->assertContains('notblank', $errors['int_field']->getConstraints());
    $this->assertEquals('', $errors['int_field']->getValue());
  }

  public function testIsValidForValidDataSucceeds()
  {
    $data = array(
      'email_field' => 'foo@bar.com',
      'int_field' => 4,
    );

    $model = new TestValidatableModel($data);

    $this->assertTrue($model->isValid());
  }

  public function testGetValidationErrorsForValidDataIsEmpty()
  {
    $data = array(
      'email_field' => 'foo@bar.com',
      'int_field' => 4,
    );

    $model = new TestValidatableModel($data);
    $model->isValid();

    $this->assertEmpty($model->getValidationErrors());
  }

  public function testIsValidForInvalidDataFails()
  {
    $data = array(
      'email_field' => 'foo',
      'int_field' => '',
    );

    $model = new TestValidatableModel($data);

    $this->assertFalse($model->isValid());
  }

  public function testGetValidationErrorsContainsErrorsForInvalidData()
  {
     $data = array(
      'email_field' => 'foo',
      'int_field' => '',
    );

    $model = new TestValidatableModel($data);
    $model->isValid();

    $this->assertValidationErrors($model->getValidationErrors());
  }

  public function testSaveThrowsExceptionForInvalidData()
  {
    try {
      $data = array(
        'email_field' => 'foo',
        'int_field' => '',
      );

      $model = new TestValidatableModel($data);
      $model->save();

      $this->fail('ValidationException was not thrown');
    }
    catch (ValidationException $exception) {
      $this->assertValidationErrors($exception->getValidationErrors());
    }
  }

  public function testSaveDoesntThrowExceptionForValidData()
  {
    $data = array(
      'email_field' => 'foo@bar.com',
      'int_field' => 4,
    );

    $model = new TestValidatableModel($data);
    $model->save();
  }
}
