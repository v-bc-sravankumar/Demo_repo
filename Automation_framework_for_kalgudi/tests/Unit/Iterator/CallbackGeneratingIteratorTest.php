<?php

namespace Unit\Iterator;

use Iterator\CallbackGeneratingIterator;
use ArrayIterator;

class CallbackGeneratingIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callback is not callable.
     */
    public function testConstructorThrowsExceptionForInvalidCallback()
    {
        new CallbackGeneratingIterator('hello');
    }

    public function testIteratorIteratesOverGeneratedIterators()
    {
        $expected = range(0, 49);

        // creates array iterators with the range from the index * increment
        // iterating this should produce the values 0..49
        $iterator = new CallbackGeneratingIterator(function($index) {
            $increment = 10;
            $limit = 5;

            if ($index == $limit) {
                return false;
            }

            $offset = $index * $increment;

            return new ArrayIterator(range($offset, $offset + 9));
        });

        $this->assertEquals($expected, iterator_to_array($iterator));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Callback did not return an iterator or FALSE. string given.
     */
    public function testExceptionThrownIfCallbackDoesntReturnIteratorOrFalse()
    {
        $iterator = new CallbackGeneratingIterator(function($index) {
            return 'foobar';
        });

        $iterator->valid();
    }

    public function testValidRewindsBeforeFirstIteration()
    {
        $generated = false;

        $iterator = new CallbackGeneratingIterator(function($index) use (&$generated) {
            if ($generated) {
                return false;
            }

            $generated = true;
            return new ArrayIterator(array(1,2,3));
        });

        $this->assertTrue($iterator->valid());
    }

    public function testCurrentRewindsBeforeFirstIteration()
    {
        $generated = false;

        $iterator = new CallbackGeneratingIterator(function($index) use (&$generated) {
            if ($generated) {
                return false;
            }

            $generated = true;
            return new ArrayIterator(array(3,4,5));
        });

        $this->assertEquals(3, $iterator->current());
    }

    public function testGetCallbackIndex()
    {
        $iterator = new CallbackGeneratingIterator(function($index) {
            $limit = 4;

            if ($index == $limit) {
                return false;
            }

            return new ArrayIterator(array(1,2,3));
        });

        iterator_to_array($iterator);

        $this->assertEquals(4, $iterator->getCallbackIndex());
    }
}
