<?php

namespace Unit\Store\Search\Indexer;

use Bigcommerce\SearchClient\DocumentMapper\DocumentMapperIterator;
use Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface;
use Bigcommerce\SearchClient\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Store\Search\Indexer\AbstractIndexer;
use Store\Search\Indexer\IndexerFactory;
use Store\Search\IndexStrategy\BackgroundIndexStrategy;
use Store\Search\Provider\Local\DocumentMapper\AbstractDocumentMapper;
use Bigcommerce\SearchClient\Document\AbstractDocument;
use Bigcommerce\SearchClient\IndexStrategy\DirectIndexStrategy;
use Store_Statsd;
use Symfony\Component\Process\Exception\RuntimeException;
use Liip\Monitor\Result\CheckResult;

class AbstractIndexerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = \Logging\Logger::getInstance();

        $this->statsd = $this
            ->getMockBuilder('Store_Statsd')
            ->setMethods(array('timing', 'increment'))
            ->getMock();

        $this->keyStore = $this
            ->getMockBuilder('Interspire_KeyStore')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Store_Statsd
     */
    protected $statsd;

    /**
     * @var Interspire_KeyStore
     */
    protected $keyStore;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProviderInterface
     */
    private function getProvider()
    {
        return $this->getMock('\Bigcommerce\SearchClient\Provider\ProviderInterface');
    }

    /**
     * @param array $methods The methods to mock (if any).
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|BackgroundIndexStrategy
     */
    private function getBackgroundStrategy(array $methods = null)
    {
        $mock = $this
            ->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy')
            ->disableOriginalConstructor()
            ->setMethods($methods ?: array())
            ->getMock();

        return $mock;
    }

    /**
     * @param ProviderInterface $provider
     * @param BackgroundIndexStrategy $backgroundStrategy
     * @param \Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface $strategy
     *
     * @return TestIndexer
     */
    private function getIndexer(
        ProviderInterface $provider,
        BackgroundIndexStrategy $backgroundStrategy = null,
        IndexStrategyInterface $strategy = null
    ) {
        if ($backgroundStrategy === null) {
            $backgroundStrategy = $this->getBackgroundStrategy();
        }

        if ($strategy === null) {
            $strategy = new DirectIndexStrategy($provider);
        }

        return new TestIndexer(
            $strategy,
            new TestMapper('test'),
            $this->logger,
            $this->statsd,
            $backgroundStrategy,
            $this->keyStore
        );
    }

    private function getIndexerConstructorArgs()
    {
        return array(
            new DirectIndexStrategy($this->getProvider()),
            new TestMapper('test'),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy(),
            $this->keyStore
        );
    }

    public function testIndexDocument()
    {
        $data = array(
            'id'  => 4,
            'foo' => 'f',
            'bar' => 'b',
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|IndexStrategyInterface $strategy */
        $strategy = $this
            ->getMockBuilder('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface')
            ->getMock();

        $strategy->expects($this->once())
            ->method('indexDocument')
            ->with($this->callback(function (AbstractDocument $document) {
                $data = $document->getData();

                return $document->getId() == 5 &&
                    $data['foo'] == 'f' &&
                    $data['bar'] == 'b';
            }));

        $indexer =  new TestIndexer(
            $strategy,
            new TestMapper('test'),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy(),
            $this->keyStore
        );

        $indexer->indexDocument(5, $data);
    }

    public function testUpdateDocument()
    {
        $expectedData = array(
            'foo' => 'foo',
        );

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('updateDocument')
            ->with($this->callback(function (TestDocument $document) use ($expectedData) {
                return $document->getId() == 7 &&
                    $document->getDirtyData() == $expectedData;
            }));

        $indexer = $this->getIndexer($provider);

        $beforeData = array(
            'id'  => 3,
            'foo' => 'f',
            'bar' => 'b',
        );

        $afterData = array(
            'id'  => 3,
            'foo' => 'foo',
            'bar' => 'b',
        );

        // id 7 should override ids in the data
        $document = $indexer->updateDocument(7, $beforeData, $afterData);
    }

    public function testUpdateDocumentThatHasntChangedDoesNothing()
    {
        $provider = $this->getProvider();
        $provider
            ->expects($this->never())
            ->method('updateDocument');

        $indexer = $this->getIndexer($provider);

        $beforeData = array(
            'id'  => 3,
            'foo' => 'f',
            'bar' => 'b',
        );

        // id 7 should override ids in the data
        $document = $indexer->updateDocument(7, $beforeData, $beforeData);
    }

    public function updateUrlDataProvider()
    {
        return array(
            array(ProviderInterface::TYPE_PRODUCT),
            array(ProviderInterface::TYPE_CATEGORY),
            array(ProviderInterface::TYPE_PAGE),
            array(ProviderInterface::TYPE_POST),
        );
    }

    /**
     * @dataProvider updateUrlDataProvider
     */
    public function testUpdateUrl($type)
    {
        $oldUrl = '/old-url.html';
        $newUrl = '/updated-url.html';

        $expectedData = array(
            'url' => $newUrl,
        );

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('updateDocument')
            ->with($this->callback(function (AbstractDocument $document) use ($expectedData) {
                return $document->getId() == 6 &&
                    $document->getDirtyData() == $expectedData;
            }));

        $indexer = IndexerFactory::getIndexerForType(
            $type,
            new DirectIndexStrategy($provider),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy(),
            $this->keyStore
        );

        $document = $indexer->updateUrl(6, $oldUrl, $newUrl);
    }

    /**
     * @dataProvider updateUrlDataProvider
     */
    public function testUpdateUrlWithOldUrlAsNull($type)
    {
        $oldUrl = null;
        $newUrl = '/an-updated-url.html';

        $expectedData = array(
            'url' => $newUrl,
        );

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('updateDocument')
            ->with($this->callback(function (AbstractDocument $document) use ($expectedData) {
                return $document->getId() == 10 &&
                    $document->getDirtyData() == $expectedData;
            }));

        $indexer = IndexerFactory::getIndexerForType(
            $type,
            new DirectIndexStrategy($provider),
            $this->logger,
            $this->statsd,
            $this->getBackgroundStrategy(),
            $this->keyStore
        );

        $document = $indexer->updateUrl(10, $oldUrl, $newUrl);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Type "test" does not have a URL and cannot be updated.
     */
    public function testUpdateUrlForUnsupportedTypeThrowsException()
    {
        $indexer = $this->getIndexer($this->getProvider());
        $indexer->updateUrl(1, 'foo', 'bar');
    }

    public function testDeleteDocument()
    {
        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('deleteDocument')
            ->with($this->equalTo('test'), $this->equalTo(12));

        $indexer = $this->getIndexer($provider);
        $indexer->deleteDocument(12);
    }

    public function testDeleteAllDocuments()
    {
        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('deleteAllDocuments')
            ->with($this->equalTo('test'));

        $indexer = $this->getIndexer($provider);
        $indexer->deleteAllDocuments();
    }

    public function testDeleteDocumentsByIds()
    {
        $ids = array(1,3,5);
        $expected = array('test' => $ids);

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('bulkDeleteDocuments')
            ->with($this->equalTo($expected));

        $indexer = $this->getIndexer($provider);
        $indexer->deleteDocumentsByIds($ids);
    }

    public function testRebuildTypeCreatesRebuildJob()
    {
        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('rebuildType')
            ->with($this->equalTo('test'));

        $indexer = $this->getIndexer($this->getProvider(), $backgroundStrategy);
        $indexer->rebuildType();
    }

    public function testRebuildTypeInBackgroundDirectlyIndexes()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TestIndexer $indexer */
        $indexer = $this
            ->getMockBuilder('Unit\Store\Search\Indexer\TestIndexer')
            ->setConstructorArgs($this->getIndexerConstructorArgs())
            ->setMethods(array('deleteAllDocuments', 'indexAllDocuments'))
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('deleteAllDocuments')
            ->will($this->returnValue(true));

        $indexer
            ->expects($this->once())
            ->method('indexAllDocuments');

        $this->keyStore
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('elastic.index.rebuild.test'), $this->greaterThan(0));

        $indexer->rebuildType(true);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attempting to rebuild an index in the background with no background index strategy.
     */
    public function testRebuildThrowsExceptionWithNoBackgroundStrategy()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TestIndexer $indexer */
        $indexer = new TestIndexer(
            new DirectIndexStrategy($this->getProvider()),
            new TestMapper('test'),
            $this->logger,
            $this->statsd,
            null,
            $this->keyStore
        );

        $indexer->rebuildType();
    }

    public function testIndexIterator()
    {
        $data = array(
            array(
                'id'  => 1,
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            array(
                'id'  => 2,
                'foo' => 'hello',
                'bar' => 'world',
            ),
        );

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('bulkIndexDocuments')
            ->with($this->callback(function ($arg) use ($data) {
                if (!($arg instanceof DocumentMapperIterator)) {
                    return false;
                }

                if (!$arg->getInnerIterator() instanceof \ArrayIterator) {
                    return false;
                }

                return $arg->getInnerIterator()->getArrayCopy() === $data;
            }));

        $indexer = $this->getIndexer($provider);
        $indexer->all = $data;
        $indexer->indexAllDocuments();
    }

    /**
     * @return array
     */
    public function invalidIteratorDataProvider()
    {
        return array(
            array('foo'),
            array(new \stdClass()),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidIteratorDataProvider
     */
    public function testIndexIteratorThrowsExceptionIfNotArrayOrTraversable($records)
    {
        $provider = $this->getProvider();
        $indexer = $this->getIndexer($provider);
        $indexer->all = $records;

        $indexer->indexAllDocuments();
    }

    /**
     * @return array
     */
    public function validIteratorDataProvider()
    {
        return array(
            array(array(array('foo'), array('bar'))),
            array(new \ArrayIterator()),
        );
    }

    /**
     * @dataProvider validIteratorDataProvider
     */
    public function testIndexIteratorForArrayOrTraversable($records)
    {
        $provider = $this->getProvider();
        $indexer = $this->getIndexer($provider);
        $indexer->all = $records;

        $indexer->indexAllDocuments();
    }

    public function testIndexAllDocuments()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TestIndexer $indexer */
        $indexer = $this
            ->getMockBuilder('Unit\Store\Search\Indexer\TestIndexer')
            ->setConstructorArgs($this->getIndexerConstructorArgs())
            ->setMethods(array('indexIterator'))
            ->getMock();

        $data = array(
            array('foo' => 'bar'),
            array('foo' => 'foo'),
        );

        $indexer->all = $data;

        $indexer
            ->expects($this->once())
            ->method('indexIterator')
            ->with($this->equalTo($data));

        $indexer->indexAllDocuments();
    }

    public function testIndexDocumentsByIdsCreatesBackgroundJob()
    {
        $ids = array(4,5,6);

        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('bulkIndexDocuments')
            ->with($this->callback(function (array $documents) use ($ids) {
                $count = 0;

                /** @var AbstractDocument $document */
                foreach ($documents as $document) {
                    $count++;

                    if ($document->getType() !== 'test') {
                        return false;
                    }
                    if (!in_array($document->getId(), $ids)) {
                        return false;
                    }
                }

                return count($ids) === $count;
            }));

        $indexer = $this->getIndexer($this->getProvider(), $backgroundStrategy);
        $indexer->indexDocumentsByIds($ids);
    }

    public function testIndexDocumentsByIdsInBackgroundIndexesDirectly()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TestIndexer $indexer */
        $indexer = $this
            ->getMockBuilder('Unit\Store\Search\Indexer\TestIndexer')
            ->setConstructorArgs($this->getIndexerConstructorArgs())
            ->setMethods(array('indexIterator'))
            ->getMock();

        $data = array(
            array('foo' => 'hello'),
            array('foo' => 'world'),
        );

        $indexer->byIds = $data;

        $indexer
            ->expects($this->once())
            ->method('indexIterator')
            ->with($this->equalTo($data));

        $indexer->indexDocumentsByIds(array(1,2,3), true);
    }

    public function testUpdateIterator()
    {
        $data = array(
            array(
                'id'  => 1,
                'foo' => 'foobar',
            ),
            array(
                'id'  => 2,
                'bar' => 'helloworld',
            ),
        );

        $provider = $this->getProvider();
        $provider
            ->expects($this->once())
            ->method('bulkUpdateDocuments')
            ->with($this->callback(function ($arg) use ($data) {
                if (!($arg instanceof DocumentMapperIterator)) {
                    return false;
                }

                if (!$arg->getInnerIterator() instanceof \ArrayIterator) {
                    return false;
                }

                return $arg->getInnerIterator()->getArrayCopy() === $data;
            }));

        $indexer = $this->getIndexer($provider);
        $indexer->all = $data;
        $indexer->updateAll();
    }

    /**
     * @return array
     */
    public function isIndexingEnabledDataProvider()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @dataProvider isIndexingEnabledDataProvider
     */
    public function testIsIndexingEnabled($enabled)
    {
        $config = array('Feature_SearchIndexing' => $enabled);

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $this->assertEquals($enabled, AbstractIndexer::isIndexingEnabled($settings));
    }

    public function checkAndReindexDataProvider()
    {
        return array(
            array(CheckResult::WARNING),
            array(CheckResult::CRITICAL),
        );
    }

    /**
     * @dataProvider checkAndReindexDataProvider
     */
    public function testCheckAndReindexRebuildsIfHealthNotOk($status)
    {
        $result = new CheckResult('foo', 'bar', $status);

        $indexer = $this
            ->getMockBuilder('Unit\Store\Search\Indexer\TestIndexer')
            ->setConstructorArgs($this->getIndexerConstructorArgs())
            ->setMethods(array('rebuildType', 'checkHealth'))
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('checkHealth')
            ->will($this->returnValue($result));

        $indexer
            ->expects($this->once())
            ->method('rebuildType');

        $this->assertFalse($indexer->checkAndReindex());
    }

    public function testCheckAndReindexDoesntRebuildIfHealthOk()
    {
        $result = new CheckResult('foo', 'bar', CheckResult::OK);

        $indexer = $this
            ->getMockBuilder('Unit\Store\Search\Indexer\TestIndexer')
            ->setConstructorArgs($this->getIndexerConstructorArgs())
            ->setMethods(array('rebuildType', 'checkHealth'))
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('checkHealth')
            ->will($this->returnValue($result));

        $indexer
            ->expects($this->never())
            ->method('rebuildType');

        $this->assertTrue($indexer->checkAndReindex());
    }
}

class TestMapper extends AbstractDocumentMapper
{
    /**
     * {@inheritdoc}
     */
    public function mapToDocument($data)
    {
        return new TestDocument($data);
    }

    /**
     * {@inheritdoc}
     */
    public function mapFromDocument(AbstractDocument $document, $forUpdate = false)
    {
        return $document->getData();
    }
}

class TestDocument extends AbstractDocument
{
    protected $fields = array(
        'foo',
        'bar',
        'url',
    );

    /**
     * {@inheritdoc}
     */
    public function getFieldConstraints()
    {
        return array();
    }

    public function setUrl($url)
    {
        return $this->setField('url', $url);
    }
}


class TestIndexer extends AbstractIndexer
{
    protected $documentType = 'test';
    protected $tableName    = 'test';

    public $all;
    public $byIds;

    protected function getAll()
    {
        return $this->all;
    }

    /**
     * {@inheritdoc}
     */
    protected function getByIds(array $ids)
    {
        return $this->byIds;
    }

    public function updateAll()
    {
        $this->updateIterator($this->getAll());
    }

    /**
     * {@inheritdoc}
     */
    protected function createNewDocument($id)
    {
        return new TestDocument(array('id' => $id));
    }
}
