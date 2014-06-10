<?php

namespace Unit\Iterator;

use Iterator\MappingIterator;
use ArrayIterator;

class MappingIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callback is not callable.
     */
    public function testConstructorThrowsExceptionForInvalidCallback()
    {
        new MappingIterator(new ArrayIterator(array(1,2,3)), 'hello');
    }

    public function testCallbackIsAppliedToEachElement()
    {
        $records = array(
            array(
                'foo' => 'foofoo',
                'bar' => 'barbar',
            ),
            array(
                'foo' => 'foofoofoo' ,
                'bar' => 'barbarbar',
            ),
        );

        $iterator = new MappingIterator(new ArrayIterator($records), function($record) {
            $record['foo'] .= '_hello';
            $record['bar'] .= '_world';

            return $record;
        });

        $expected = array(
            array(
                'foo' => 'foofoo_hello',
                'bar' => 'barbar_world',
            ),
            array(
                'foo' => 'foofoofoo_hello' ,
                'bar' => 'barbarbar_world',
            ),
        );

        $this->assertEquals($expected, iterator_to_array($iterator));
    }
}
