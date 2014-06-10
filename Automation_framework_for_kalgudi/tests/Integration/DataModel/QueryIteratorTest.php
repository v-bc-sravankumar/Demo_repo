<?php

namespace Integration\DataModel;

use DataModel_QueryIterator;

/**
 * @group nosample
 */
class QueryIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function getMockModel()
    {
        return $this->getMockBuilder('DataModel_Record')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDeleteAllPassesContextToModelDelete()
    {
        $context = array('foo' => 'bar');
        $work = $this->getMock('DataModel_UnitOfWork');

        $model1 = $this->getMockModel();
        $model1
            ->expects($this->once())
            ->method('delete')
            ->with(
                $this->equalTo($work),
                $this->equalTo($context)
            )
            ->will($this->returnValue(true));

        $model2 = $this->getMockModel();
        $model2
            ->expects($this->once())
            ->method('delete')
            ->with(
                $this->equalTo($work),
                $this->equalTo($context)
            )
            ->will($this->returnValue(true));

        $iterator = $this->getMockBuilder('DataModel_QueryIterator')
            ->disableOriginalConstructor()
            ->setMethods(array('next', 'valid', 'current', '_execute'))
            ->getMock();

        $iterator
            ->expects($this->at(0))
            ->method('valid')
            ->will($this->returnValue(true));

        $iterator
            ->expects($this->at(1))
            ->method('current')
            ->will($this->returnValue($model1));

        $iterator
            ->expects($this->at(3))
            ->method('valid')
            ->will($this->returnValue(true));

        $iterator
            ->expects($this->at(4))
            ->method('current')
            ->will($this->returnValue($model2));

        $iterator
            ->expects($this->at(6))
            ->method('valid')
            ->will($this->returnValue(false));

        $this->assertTrue($iterator->deleteAll($work, $context));
    }
}
