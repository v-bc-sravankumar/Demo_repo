<?php

namespace Integration\Store\Search\IndexStrategy;

use Store\Search\IndexStrategy\BackgroundIndexStrategy;
use Bigcommerce\SearchClient\Document\BrandDocument;
use Bigcommerce\SearchClient\Document\CategoryDocument;
use Interspire_TaskManager_Internal;
use Test\FixtureTest;

/**
 * @group nosample
 */
class BackgroundIndexStrategyTest extends FixtureTest
{
    /**
     * @return BackgroundIndexStrategy
     */
    private function getStrategy()
    {
        return new BackgroundIndexStrategy(
            \Config\Environment::get('search.indexing.background.queue'),
            'search.strategy.index.mock',
            'search.strategy.index.background',
            $GLOBALS['app']['logger'],
            $GLOBALS['app']['statsd.client']
        );
    }

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

    /**
     * @return BrandDocument
     */
    private function getBrand()
    {
        $document = new BrandDocument();
        $document
            ->setId($this->getFaker()->unique()->randomNumber(1))
            ->setName($this->getFaker()->unique()->sentence())
            ->setPageTitle($this->getFaker()->sentence())
            ->setKeywords($this->getFaker()->words())
            ->setImageFile($this->getFaker()->word() . '.png');

        return $document;
    }

    /**
     * @return CategoryDocument
     */
    private function getCategory()
    {
        $document = new CategoryDocument();
        $document
            ->setId($this->getFaker()->unique()->randomNumber(1))
            ->setName($this->getFaker()->unique()->sentence())
            ->setDescription($this->getFaker()->text())
            ->setPageTitle($this->getFaker()->sentence())
            ->setKeywords($this->getFaker()->words())
            ->setParentId($this->getFaker()->randomNumber())
            ->setIsVisible($this->getFaker()->boolean())
            ->setUrl('/' . $this->getFaker()->unique()->word() . '.html');

        return $document;
    }

    private function assertExecuteTask()
    {
        $this->assertTrue(Interspire_TaskManager_Internal::hasTasks());
        $this->assertTrue(Interspire_TaskManager_Internal::executeNextTask(null, $task));

        return $task;
    }

    /**
     * @param string $method The method name to be called on the strategy.
     */
    private function assertBulkIndexJobForDocument($method)
    {
        $document = $this->getBrand();

        $strategy = $this->getStrategy();
        $strategy->$method($document);

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\IndexDocumentsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals(array('brand' => array($document->getId())), $args['ids']);
    }

    public function testIndexDocumentCreatesIndexDocumentsJob()
    {
        $this->assertBulkIndexJobForDocument('indexDocument');
    }

    /**
     * Background updating is not supported, this will result in a re-index.
     */
    public function testUpdateDocumentCreatesIndexDocumentsJob()
    {
        $this->assertBulkIndexJobForDocument('updateDocument');
    }

    public function testDeleteDocumentCreatesDeleteDocumentsJob()
    {
        $strategy = $this->getStrategy();
        $strategy->deleteDocument('product', 10);

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\DeleteDocumentsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals(array('product' => array(10)), $args['ids']);
    }

    public function testDeleteAllDocumentsCreatesDeleteAllDocumentsJob()
    {
        $strategy = $this->getStrategy();
        $strategy->deleteAllDocuments('category');

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\DeleteAllDocumentsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals('category', $args['type']);
    }

    /**
     * @param string $method The method name to be called on the strategy.
     */
    private function assertBulkIndexJobForDocuments($method)
    {
        /** @var \Bigcommerce\SearchClient\Document\AbstractDocument[] $documents */
        $documents = array(
            $this->getBrand(),
            $this->getBrand(),
            $this->getCategory(),
            $this->getCategory(),
        );

        $strategy = $this->getStrategy();
        $strategy->$method($documents);

        $task = $this->assertExecuteTask();

        $expected = array(
            'brand' => array($documents[0]->getId(), $documents[1]->getId()),
            'category' => array($documents[2]->getId(), $documents[3]->getId()),
        );

        $this->assertEquals('\Job\Search\Index\IndexDocumentsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals($expected, $args['ids']);
    }

    public function testBulkIndexDocumentsCreatesIndexDocumentsJob()
    {
        $this->assertBulkIndexJobForDocuments('bulkIndexDocuments');
    }

    /**
     * Background updating is not supported, this will result in a re-index.
     */
    public function testBulkUpdateDocumentsCreatesIndexDocumentsJob()
    {
        $this->assertBulkIndexJobForDocuments('bulkUpdateDocuments');
    }

    public function testBulkDeleteDocumentsCreatesDeleteDocumentsJob()
    {
        $documents = array(
            'product' => array(1,2,3),
            'brand' => array(4,5,6),
            'category' => array(7,8,9),
        );

        $strategy = $this->getStrategy();
        $strategy->bulkDeleteDocuments($documents);

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\DeleteDocumentsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals($documents, $args['ids']);
    }

    public function testRebuildProductPricesCreatesRebuildProductPricesJob()
    {
        $ids = array(4,12,38,59,61);

        $strategy = $this->getStrategy();
        $strategy->rebuildProductPrices($ids);

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\RebuildProductPricesJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals($ids, $args['ids']);
    }

    public function testRebuildTypeCreatesRebuildIndexJob()
    {
        $strategy = $this->getStrategy();
        $strategy->rebuildType('post');

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\RebuildIndexJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals('post', $args['type']);
    }

    public function testUpdateBrandNameOnProductsCreatesUpdateBrandNameOnProductsJob()
    {
        $strategy = $this->getStrategy();
        $strategy->UpdateBrandNameOnProducts(44, 'foobar');

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\UpdateBrandNameOnProductsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals(44, $args['brand_id']);
        $this->assertEquals('foobar', $args['brand_name']);
    }

    public function testUpdateAttributesForProductsCreatesJob()
    {
        $ids = array(1,2,3,7,8,9);

        $strategy = $this->getStrategy();
        $strategy->updateAttributesForProducts($ids);

        $task = $this->assertExecuteTask();

        $this->assertEquals('\Job\Search\Index\UpdateAttributesForProductsJob', $task['class']);
        $args = json_decode($task['data'], true);
        $this->assertEquals($ids, $args['ids']);
    }
}
