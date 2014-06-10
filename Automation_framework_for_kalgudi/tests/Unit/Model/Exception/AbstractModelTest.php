<?php

namespace Unit\Model\Exception;

use PHPUnit_Framework_TestCase;
use Model\Exception\AbstractModelException;
use Model\AbstractModel;

class TestException extends AbstractModelException
{

}

class TestModel extends AbstractModel
{

}

class AbstractModelExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testGetModel()
    {
        $model = new TestModel();
        $exception = new TestException($model, 'foobar');

        $this->assertEquals($model, $exception->getModel());
    }

    public function testGetMessage()
    {
        $exception = new TestException(new TestModel(), 'foobar');
        $this->assertEquals('foobar', $exception->getMessage());
    }
}
