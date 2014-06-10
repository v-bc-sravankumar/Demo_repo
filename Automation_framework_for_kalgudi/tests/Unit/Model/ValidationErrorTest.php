<?php

namespace Unit\Model;

use PHPUnit_Framework_TestCase;
use Model\ValidationError;

class ValidationErrorTest extends PHPUnit_Framework_TestCase
{
  public function testGetField()
  {
    $error = new ValidationError('myfield', array('email', 'notnull'), 'foo');
    $this->assertEquals('myfield', $error->getField());
  }

  public function testGetConstraints()
  {
    $error = new ValidationError('myfield', array('email', 'notnull'), 'foo');
    $this->assertEquals(array('email', 'notnull'), $error->getConstraints());
  }

  public function testGetValue()
  {
    $error = new ValidationError('myfield', array('email', 'notnull'), 'foo');
    $this->assertEquals('foo', $error->getValue());
  }
}
