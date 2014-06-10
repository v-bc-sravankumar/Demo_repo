<?php

use Validation\ValidationResult;

class Unit_Validation_ValidationResultTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidEqualsPositiveValueConstructorArgument()
    {
        $result = new ValidationResult(true, "Everything is okay");
        $this->assertTrue($result->isValid());
    }

    public function testIsValidEqualsNegativeValueConstructorArgument()
    {
        $result = new ValidationResult(false, "Everything is not okay");
        $this->assertFalse($result->isValid());
    }

    public function testGetMessageEqualsMessageConstructorArgument()
    {
        $message = "Everything is okay";
        $result = new ValidationResult(true, $message);
        $this->assertEquals($message, $result->getMessage());
    }

    public function testDefaultMessageIsNull()
    {
        $result = new ValidationResult(true);
        $this->assertNull($result->getMessage());
    }
}