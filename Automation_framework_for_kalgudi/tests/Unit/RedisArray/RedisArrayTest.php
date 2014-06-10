<?php

namespace Unit\RedisArray;

use RedisArray\RedisArray;

class RedisArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The offset "foo.bar" is invalid. Redis array index must be scalar and cannot contain the "." character.
     */
    public function testOffsetSetWithInvalidKeyThrowsException()
    {
        $array = $this->getMockBuilder('\RedisArray\RedisArray')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $array['foo.bar'] = 'foo';
    }
}