<?php

namespace Unit\DataModel;

use DataModel\QueryValue;

class QueryValueTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $value = new QueryValue('foo');
        $this->assertEquals('foo', $value->getValue());
    }

    public function nonScalarDataProvider()
    {
        return array(
            array(new \stdClass()),
            array(array()),
            array(fopen('php://memory', 'r')),
        );
    }

    /**
     * @dataProvider nonScalarDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testNonScalarValueThrowsException($value)
    {
        new QueryValue($value);
    }

    public function scalarDataProvider()
    {
        return array(
            array(5),
            array(4.98),
            array('foobar'),
            array(true),
        );
    }

    /**
     * @dataProvider scalarDataProvider
     */
    public function testScalarValuesDontThrowException($value)
    {
        new QueryValue($value);
    }

    public function testSameValueAsForEqualValuesIsTrue()
    {
        $a = new QueryValue('foo');
        $b = new QueryValue('foo');

        $this->assertTrue($a->sameValueAs($b));
    }

    public function testSameValueAsForUnequalValuesIsFalse()
    {
        $a = new QueryValue('foo');
        $b = new QueryValue('bar');

        $this->assertFalse($a->sameValueAs($b));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSameValueAsForNonQueryValueThrowsException()
    {
        $a = new QueryValue('foo');
        $a->sameValueAs('foo');
    }
}
