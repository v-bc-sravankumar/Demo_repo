<?php

namespace Unit\Job\Search\Index;

use Job\Search\Index\RebuildProductPricesJob;

/**
 * @group nosample
 */
class RebuildProductPricesJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $ids = array(1,5,6,20);

        $indexer = $this
            ->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('updateProductPricesByIds')
            ->with($ids);

        $job = $this->getMock('\Job\Search\Index\RebuildProductPricesJob', array('getIndexerForType'));
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
     * @expectedExceptionMessage No document ids given.
     */
    public function testRunWithNoIdsThrowsException()
    {
        $job = new RebuildProductPricesJob();
        $job->run(array());
    }
}
