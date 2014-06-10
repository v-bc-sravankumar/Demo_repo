<?php

namespace Integration\Job\Search\Index;

use Job\Search\Index\UpdateAttributesForProductsJob;

/**
 * @group nosample
 */
class UpdateAttributesForProductsJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $ids = array(1,2,3,7,8,9);

        $indexer = $this
            ->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('updateAttributesForProducts')
            ->with(
                $this->equalTo($ids),
                $this->equalTo(true)
            );

        $job = $this->getMock('\Job\Search\Index\UpdateAttributesForProductsJob', array('getIndexerForType'));
        $job
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo('product'))
            ->will($this->returnValue($indexer));

        $args = array(
            'ids' => $ids,
        );

        $job->run($args);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No product ids given.
     */
    public function testRunWithNoIdsThrowsException()
    {
        $job = new UpdateAttributesForProductsJob();
        $job->run(array());
    }
}
