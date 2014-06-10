<?php

namespace Unit\Job\Search\Index;

use Job\Search\Index\UpdateBrandNameOnProductsJob;

/**
 * @group nosample
 */
class UpdateBrandNameOnProductsJobTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $brandId = 45;
        $brandName = 'A Brand';

        $indexer = $this
            ->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('updateBrandName')
            ->with($this->equalTo($brandId), $this->equalTo($brandName), $this->equalTo(true));

        $job = $this->getMock('\Job\Search\Index\UpdateBrandNameOnProductsJob', array('getIndexerForType'));
        $job
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo('product'))
            ->will($this->returnValue($indexer));

        $args = array(
            'brand_id' => $brandId,
            'brand_name' => $brandName,
        );

        $job->run($args);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No brand_id was given.
     */
    public function testRunWithoutBrandIdThrowsException()
    {
        $job = new UpdateBrandNameOnProductsJob();
        $job->run(array('brand_name' => 'foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No brand_name was given.
     */
    public function testRunWithoutBrandNameThrowsException()
    {
        $job = new UpdateBrandNameOnProductsJob();
        $job->run(array('brand_id' => 2));
    }
}
