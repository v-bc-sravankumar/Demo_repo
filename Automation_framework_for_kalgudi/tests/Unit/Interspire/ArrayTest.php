<?php

namespace Unit\Interspire;

use Interspire_Array;

class ArrayTest extends \PHPUnit_Framework_TestCase
{
    public function isNumericallyIndexedDataProvider()
    {
        return array(
            array(array(1,2,3), true),
            array(array(0 => 0, 2 => 2, 3 => 3), true),
            array(array(1, 'foo' => 2, 3), false),
        );
    }

    /**
     * @dataProvider isNumericallyIndexedDataProvider
     */
    public function testIsNumericallyIndexed($array, $expected)
    {
        $this->assertEquals($expected, Interspire_Array::isNumericallyIndexed($array));
    }

    public function testUniqueCaseInsensitiveRemovesDuplicates()
    {
        $array = array('foo', 'bar', 'Foo', 'Bar', 'foobar');

        $unique = Interspire_Array::uniqueCaseInsensitive($array);

        $expected = array('foo', 'bar', 'foobar');

        $this->assertEquals($expected, $unique);
    }

    public function testUniqueCaseInsensitiveRemovesDuplicatesWithAssociativeKeys()
    {
        $array = array('foo' => 'bar', 'Foo' => 'Bar', 'hello');

        $unique = Interspire_Array::uniqueCaseInsensitive($array);

        $expected = array('foo' => 'bar', 'hello');

        $this->assertEquals($expected, $unique);
    }
}
