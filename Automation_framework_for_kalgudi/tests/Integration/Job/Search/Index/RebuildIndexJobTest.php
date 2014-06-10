<?php

namespace Unit\Job\Search\Index;

use Job\Search\Index\RebuildIndexJob;

/**
 * @group nosample
 */
class RebuildIndexJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $indexer = $this
            ->getMockBuilder('\Store\Search\Indexer\BrandIndexer')
            ->disableOriginalConstructor()
            ->getMock();

        $job = $this->getMock('\Job\Search\Index\RebuildIndexJob', array('getIndexerForType'));
        $job
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo('brand'))
            ->will($this->returnValue($indexer));

        $args = array(
            'type' => 'brand',
        );

        $job->run($args);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "NULL".
     */
    public function testRunWithNoTypeThrowsException()
    {
        $job = new RebuildIndexJob();
        $job->run(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "foo".
     */
    public function testRunWithInvalidTypeThrowsException()
    {
        $job = new RebuildIndexJob();
        $job->run(array('type' => 'foo'));
    }
}
