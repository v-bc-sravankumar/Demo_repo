<?php

namespace Unit\Store\Search\Indexer;

use Bigcommerce\SearchClient\Document\AbstractDocument;
use Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface;
use Psr\Log\LoggerInterface;
use Store\Search\Indexer\AbstractIndexer;
use Store\Search\Provider\Local\DocumentMapper\DocumentMapperFactory;
use Store_Statsd;
use Traversable;

abstract class IndexerTestCase extends \PHPUnit_Framework_TestCase
{
    abstract protected function getData();

    /**
     * @param mixed $data
     *
     * @return int The ID of the AbstractDocument or other model.
     */
    abstract protected function getIdFromData($data);

    protected $indexerName;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Store_Statsd
     */
    protected $statsd;

    public function setUp()
    {
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->statsd = $this
            ->getMockBuilder('Store_Statsd')
            ->setMethods(array('timing', 'increment'))
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IndexStrategyInterface
     */
    protected function getStrategy()
    {
        $data = $this->getData();

        // Would love to use array_map here, but getDataFromId() is protected.
        $expectedIds = array();
        foreach ($data as $datum) {
            $expectedIds[] = $this->getIdFromData($datum);
        }

        $t = $this;

        $strategy = $this->getMock('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface');
        $strategy
            ->expects($this->once())
            ->method('bulkIndexDocuments')
            ->with($this->callback(function (Traversable $documents) use ($expectedIds, $t) {
                /** @var AbstractDocument $document */
                foreach ($documents as $index => $document) {
                    if ($document->getId() != $expectedIds[$index]) {
                        return false;
                    }
                }

                return true;
            }));

        return $strategy;
    }

    /**
     * @return \Store\Search\Provider\Local\DocumentMapper\AbstractDocumentMapper
     */
    protected function getMapper()
    {
        return DocumentMapperFactory::getMapperForType($this->indexerName);
    }

    /**
     * @param string $method
     * @param mixed $returnData
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractIndexer
     */
    protected function getIndexer($method, $returnData)
    {
        $indexer = $this->getMock(
            '\\Store\\Search\\Indexer\\' . $this->indexerName . 'Indexer',
            array($method),
            array($this->getStrategy(), $this->getMapper(), $this->logger, $this->statsd));

        $indexer
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($returnData));

        return $indexer;
    }

    /**
     * @param array $data
     * @param AbstractDocument[] $documents
     */
    protected function assertDocuments(array $data, array $documents)
    {
        $index = 0;
        foreach ($documents as $document) {
            $this->assertInstanceOf(
                '\\Bigcommerce\\SearchClient\\Document\\'.$this->indexerName.'Document',
                $document);

            $this->assertEquals($this->getIdFromData($data[$index]), $document->getId());

            $index++;
        }

        $this->assertEquals(count($data), $index);
    }

    public function testIndexAllDocuments()
    {
        $data = $this->getData();

        $indexer = $this->getIndexer('getAll', $data);
        $indexer->indexAllDocuments();
    }

    public function testIndexDocumentsByIds()
    {
        $data = $this->getData();

        $indexer = $this->getIndexer('getByIds', $data);
        $indexer->indexDocumentsByIds(array(1,2,3), true);
    }
}
