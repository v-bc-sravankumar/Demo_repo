<?php

namespace Integration\Job\Search;

use Job\Search\Index\AbstractIndexJob;
use Interspire_TaskManager_Internal;
use Bigcommerce\SearchClient\Document\BrandDocument;
use Bigcommerce\SearchClient\Document\CategoryDocument;
use Bigcommerce\SearchClient\Exception\Operation\OperationException;
use Bigcommerce\SearchClient\Exception\Operation\UpdateOperationException;

/**
 * @group nosample
 */
class AbstractIndexJobTest extends \PHPUnit_Framework_TestCase
{
    private function clearQueue()
    {
        while (Interspire_TaskManager_Internal::executeNextTask() !== null);
    }

    public function setUp()
    {
        $this->clearQueue();
    }

    public function tearDown()
    {
        $this->clearQueue();
    }

    public function testFailureCausesJobToRetry()
    {
        $args = array(
            'index.strategy' => 'search.strategy.index.mock',
        );

        $first = true;

        /** @var AbstractIndexJob|\PHPUnit_Framework_MockObject_MockObject $job */
        $job = new TestJob($args, false);
        $job->perform();

        $this->assertTrue(Interspire_TaskManager_Internal::hasTasks());
        $this->assertTrue(Interspire_TaskManager_Internal::executeNextTask(null, $task));

        $this->assertEquals(get_class($job), $task['class']);
    }

    public function testJobIsNotRetriedIfThresholdExceeded()
    {
        $args = array(
            'retry_delay' => \Config\Environment::get('search.indexing.background.retry_limit') + 1,
        );

        $job = $this->getMock('\Job\Search\Index\AbstractIndexJob', array('run'), array($args));
        $job
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue(false));

        $job->perform();

        $this->assertFalse(Interspire_TaskManager_Internal::hasTasks());
    }

    private function getCleanupFailingJob($exception = null)
    {
        if ($exception === null) {
            $exception = new OperationException();
        }

        $strategy = $this->getMock('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface');
        $strategy
            ->expects($this->once())
            ->method('cleanup')
            ->will($this->throwException($exception));

        $job = $this->getMockBuilder('\Job\Search\Index\AbstractIndexJob')
            ->setMethods(array('run', 'getIndexStrategy', 'retryJob', 'getIndexerForType'))
            ->setConstructorArgs(array(array()))
            ->getMock();

        $job
            ->expects($this->atLeastOnce())
            ->method('getIndexStrategy')
            ->will($this->returnValue($strategy));

        $job
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue(true));

        return $job;
    }

    public function testExceptionInStrategyCleanupCausesRetry()
    {
        $job = $this->getCleanupFailingJob();

        $job
            ->expects($this->once())
            ->method('retryJob')
            ->will($this->returnValue(100));

        $job->perform();
    }

    /**
     * @expectedException \Bigcommerce\SearchClient\Exception\Operation\OperationException
     */
    public function testExceptionInStrategyCleanupRethrowsExceptionWhenRetryLimitReached()
    {
        $job = $this->getCleanupFailingJob();

        $job
            ->expects($this->once())
            ->method('retryJob')
            ->will($this->returnValue(0));

        $job->perform();
    }

    public function testUpdateOperationExceptionCausesIndexOfFaileDocumentsIfRetryLimitReached()
    {
        $documents = array(
            new BrandDocument(array('id' => 4)),
            new BrandDocument(array('id' => 5)),
            new CategoryDocument(array('id' => 6)),
        );

        $exception = new UpdateOperationException($documents);

        $job = $this->getCleanupFailingJob($exception);

        $job
            ->expects($this->once())
            ->method('retryJob')
            ->will($this->returnValue(0));

        $brandIndexer = $this->getMockBuilder('\Store\Search\Indexer\BrandIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('indexDocumentsByIds'))
            ->getMock();

        $brandIndexer
            ->expects($this->once())
            ->method('indexDocumentsByIds')
            ->with($this->equalTo(array(4,5)), $this->equalTo(false));

        $job
            ->expects($this->at(3))
            ->method('getIndexerForType')
            ->with($this->equalTo('brand'))
            ->will($this->returnValue($brandIndexer));

        $categoryIndexer = $this->getMockBuilder('\Store\Search\Indexer\CategoryIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('indexDocumentsByIds'))
            ->getMock();

        $categoryIndexer
            ->expects($this->once())
            ->method('indexDocumentsByIds')
            ->with($this->equalTo(array(6)), $this->equalTo(false));

        $job
            ->expects($this->at(4))
            ->method('getIndexerForType')
            ->with($this->equalTo('category'))
            ->will($this->returnValue($categoryIndexer));

        $job->perform();
    }

    /**
     * @expectedException \Bigcommerce\SearchClient\Exception\Operation\OperationException
     */
    public function testIndexOfFaileDocumentsThrowsExceptionIfIndexDocumentsByIdsThrowsException()
    {
        $documents = array(
            new BrandDocument(array('id' => 4)),
        );

        $exception = new UpdateOperationException($documents);

        $job = $this->getCleanupFailingJob($exception);

        $job
            ->expects($this->once())
            ->method('retryJob')
            ->will($this->returnValue(0));

        $brandIndexer = $this->getMockBuilder('\Store\Search\Indexer\BrandIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('indexDocumentsByIds'))
            ->getMock();

        $brandIndexer
            ->expects($this->once())
            ->method('indexDocumentsByIds')
            ->with($this->equalTo(array(4)), $this->equalTo(false))
            ->will($this->throwException(new OperationException()));

        $job
            ->expects($this->at(3))
            ->method('getIndexerForType')
            ->with($this->equalTo('brand'))
            ->will($this->returnValue($brandIndexer));

        $job->perform();
    }
}

class TestJob extends AbstractIndexJob
{
    private $return;

    public function __construct(array $args = null, $return = true)
    {
        parent::__construct($args);

        $this->return = $return;
    }

    public function run(array $args)
    {
        return $this->return;
    }
}
