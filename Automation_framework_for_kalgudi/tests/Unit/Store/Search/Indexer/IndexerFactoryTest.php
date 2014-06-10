<?php

namespace Unit\Store\Search\Indexer;

use Psr\Log\LoggerInterface;
use Store\Search\Indexer\IndexerFactory;
use Store\Search\IndexStrategy\BackgroundIndexStrategy;
use Store\Search\IndexStrategy\MockIndexStrategy;
use Store_Statsd;

class IndexerFactoryTest extends \PHPUnit_Framework_TestCase
{
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
     * @return \PHPUnit_Framework_MockObject_MockObject|BackgroundIndexStrategy
     */
    private function getBackgroundStrategy()
    {
        $mock = $this->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy');
        $mock->disableOriginalConstructor();
        return $mock->getMock();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Type "foo" is not supported.
     */
    public function testGetIndexerForTypeThrowsExceptionForUnsupportedType()
    {
        IndexerFactory::getIndexerForType(
            'foo',
            new MockIndexStrategy(),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy()
        );
    }

    public function testGetIndexerForTypeForSupportedType()
    {
        $indexer = IndexerFactory::getIndexerForType(
            'brand',
            new MockIndexStrategy(),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy());

        $this->assertInstanceOf('\Store\Search\Indexer\BrandIndexer', $indexer);
    }
}
