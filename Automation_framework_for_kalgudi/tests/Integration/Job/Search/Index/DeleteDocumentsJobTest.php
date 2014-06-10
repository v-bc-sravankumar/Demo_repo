<?php

namespace Integration\Job\Search\Index;

use Job\Search\Index\DeleteDocumentsJob;

/**
 * @group nosample
 */
class DeleteDocumentsJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $ids = array(
            'brand' => array(1,2,3),
            'post'  => array(4,5,6),
        );

        $strategy = $this->getMock('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface');
        $strategy
            ->expects($this->at(0))
            ->method('bulkDeleteDocuments')
            ->with($this->equalTo(array('brand' => $ids['brand'])));

        $strategy
            ->expects($this->at(1))
            ->method('bulkDeleteDocuments')
            ->with($this->equalTo(array('post' => $ids['post'])));

        $backgroundStrategy = $this->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy')
            ->disableOriginalConstructor()
            ->getMock();

        $args = array(
            'ids' => $ids,
        );

        $job = $this->getMock('\Job\Search\Index\DeleteDocumentsJob', array('getIndexStrategy', 'getBackgroundStrategy'));
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
     * @expectedExceptionMessage No document ids given.
     */
    public function testRunWithNoIdsThrowsException()
    {
        $job = new DeleteDocumentsJob();
        $job->run(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "foo".
     */
    public function testRunWithInvalidTypeThrowsException()
    {
        $job = new DeleteDocumentsJob();
        $job->run(array('ids' => array('foo' => array(1,2,3))));
    }
}
