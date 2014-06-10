<?php

namespace Unit\Model\Exception;

use PHPUnit_Framework_TestCase;
use Model\AbstractValidatableModel;
use Model\Exception\ValidationException;
use Model\ValidationError;

class TestValidatableModel extends AbstractValidatableModel
{
    protected function getValidationRules()
    {
        return array();
    }
}

class ValidationExceptionTest extends PHPUnit_Framework_TestCase
{
  public function testGetValidationErrors()
  {
    $errors = array(
      'field1' => new ValidationError('field1', array('email'), 'foo'),
      'field2' => new ValidationError('field2', array('null'), null),
    );

    $exception = new ValidationException(new TestValidatableModel(), $errors);
    $this->assertEquals($errors, $exception->getValidationErrors());
  }
}