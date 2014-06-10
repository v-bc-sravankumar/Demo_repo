<?php

namespace Unit\Job\Search\Index;

use Job\Search\Index\DeleteAllDocumentsJob;

/**
 * @group nosample
 */
class DeleteAllDocumentsJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $strategy = $this->getMock('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface');
        $strategy
            ->expects($this->once())
            ->method('deleteAllDocuments')
            ->with($this->equalTo('brand'));

        $backgroundStrategy = $this->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy')
            ->disableOriginalConstructor()
            ->getMock();

        $args = array(
            'type' => 'brand',
        );

        $job = $this->getMock('\Job\Search\Index\DeleteAllDocumentsJob', array('getIndexStrategy', 'getBackgroundStrategy'));
        $job
            ->expects($this->any())
            ->method('getIndexStrategy')
            ->will($this->returnValue($strategy));

        $job
            ->expects($this->any())
            ->method('getBackgroundStrategy')
            ->will($this->returnValue($backgroundStrategy));

        $job->run($args);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "NULL".
     */
    public function testRunWithNoTypeThrowsException()
    {
        $job = new DeleteAllDocumentsJob();
        $job->run(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "foo".
     */
    public function testRunWithInvalidTypeThrowsException()
    {
        $job = new DeleteAllDocumentsJob();
        $job->run(array('type' => 'foo'));
    }
}
