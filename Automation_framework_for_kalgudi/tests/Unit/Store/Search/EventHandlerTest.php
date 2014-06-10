<?php

namespace Unit\Store\Search;

use Bigcommerce\SearchClient\Document\AbstractDocument;
use Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface;
use Bigcommerce\SearchClient\Strategy\StrategyInterface;
use Bigcommerce\SearchClient\Provider\ProviderInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use Store\Search\Indexer\AbstractIndexer;
use Store\Search\IndexStrategy\BackgroundIndexStrategy;
use Store_Event;
use Interspire_Event;
use Store_Brand;
use Store\Search\EventHandler;
use Store\Search\IndexStrategy\MockIndexStrategy;
use Store_Statsd;

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{
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
     * @param string|array $types
     * @param IndexStrategyInterface $strategy
     * @param BackgroundIndexStrategy $backgroundStrategy
     * @param array $mockMethods
     * @param LoggerInterface $logger
     * @param Store_Statsd $statsd
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|EventHandler
     */
    private function getHandler(
        $types,
        IndexStrategyInterface $strategy = null,
        BackgroundIndexStrategy $backgroundStrategy = null,
        $mockMethods = null,
        $logger = null,
        Store_Statsd $statsd = null,
        $handlerClass = null,
        $settings = null
    ) {
        if (!is_array($types)) {
            $types = array($types);
        }

        if ($settings === null) {
            $config = array(
                'Feature_SearchIndexing' => true,
            );

            $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
            $settings->load();
        }

        if ($strategy === null) {
            $strategy = new MockIndexStrategy();
        }

        if ($backgroundStrategy === null) {
            $backgroundStrategy = $this->getBackgroundStrategy();
        }

        if ($logger === null) {
            $logger = $this->getMock('Psr\Log\LoggerInterface');
        }

        if ($statsd === null) {
            $statsd = $this
                ->getMockBuilder('Store_Statsd')
                ->setMethods(array('timing', 'increment'))
                ->getMock();
        }

        if ($handlerClass === null) {
            $handlerClass = '\Store\Search\EventHandler';
        }

        return $this->getMock($handlerClass, $mockMethods, array(
            $strategy,
            $backgroundStrategy,
            $settings,
            $logger,
            $statsd
        ));
    }

    /**
     * @param $type
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractIndexer
     */
    private function getIndexer($type)
    {
        return $this->getMockBuilder('\\Store\\Search\\Indexer\\' . ucfirst($type) . 'Indexer')
            ->disableOriginalConstructor(true)
            ->getMock();
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject|StrategyInterface $strategy
     * @param $method
     * @param $id
     * @param $expected
     * @param $type
     * @param int $callIndex
     *
     * @return PHPUnit_Framework_MockObject_MockObject|StrategyInterface
     */
    private function addDocumentAssertionToStrategy($strategy, $method, $id, $expected, $type, $callIndex = 0)
    {
        $strategy
            ->expects($this->at($callIndex))
            ->method($method)
            ->with($this->callback(function(AbstractDocument $document) use ($id, $expected, $type) {
                if ($document->getType() !== $type) {
                    return false;
                }

                if ($document->getId() !== $id) {
                    return false;
                }

                if ($document->getDirtyData() !== $expected) {
                    return false;
                }

                return true;
            }));

        return $strategy;
    }

    public function testCreatedEventTriggersIndex()
    {
        $id = 32;
        $type = 'brand';

        $brand = new Store_Brand();
        $brand->setId($id)
            ->setName('A Brand')
            ->setPageTitle('Brand Page Title')
            ->setSearchKeywords('key,word')
            ->setImageFileName('brand.png');

        $expected = array(
            'name'            => 'A Brand',
            'page_title'      => 'Brand Page Title',
            'keywords'        => array('key','word'),
            'image_file'      => 'brand.png',
        );

        $event = new Interspire_Event(Store_Event::EVENT_BRAND_CREATED);
        $event->data = array(
            'id'    => $id,
            'after' => $brand,
        );

        /** @var PHPUnit_Framework_MockObject_MockObject|MockIndexStrategy $strategy */
        $strategy = $this->getMock('\Store\Search\IndexStrategy\MockIndexStrategy');
        $this->addDocumentAssertionToStrategy($strategy, 'indexDocument', $id, $expected, $type);

        $handler = $this->getHandler($type, $strategy);
        $handler->handleEvent($event);
    }

    public function testUpdatedEventTriggersUpdated()
    {
        $id = 48;
        $type = 'brand';

        $beforeBrand = new Store_Brand();
        $beforeBrand
            ->setId($id)
            ->setName('A Brand')
            ->setPageTitle('Brand Page Title')
            ->setSearchKeywords('key,word')
            ->setImageFileName('brand.png');

        $afterBrand = new Store_Brand();
        $afterBrand
            ->setId($id)
            ->setName('Updated Brand')
            ->setPageTitle('Brand Page Title')
            ->setSearchKeywords('foo,bar')
            ->setImageFileName('brand.png');

        $expected = array(
            'name'            => 'Updated Brand',
            'keywords'        => array('foo','bar'),
        );

        $event = new Interspire_Event(Store_Event::EVENT_BRAND_CHANGED);
        $event->data = array(
            'id'     => $id,
            'before' => $beforeBrand,
            'after'  => $afterBrand,
        );

        /** @var PHPUnit_Framework_MockObject_MockObject|MockIndexStrategy $strategy */
        $strategy = $this->getMock('\Store\Search\IndexStrategy\MockIndexStrategy');
        $this->addDocumentAssertionToStrategy($strategy, 'updateDocument', $id, $expected, $type);

        $handler = $this->getHandler($type, $strategy);
        $handler->handleEvent($event);
    }

    public function testDeleteEventTriggersDelete()
    {
        $id = 288;
        $type = 'brand';

        $event = new Interspire_Event(Store_Event::EVENT_BRAND_DELETED);
        $event->data = array(
            'id'     => $id,
        );

        /** @var PHPUnit_Framework_MockObject_MockObject|MockIndexStrategy $strategy */
        $strategy = $this->getMock('\Store\Search\IndexStrategy\MockIndexStrategy');
        $strategy
            ->expects($this->once())
            ->method('deleteDocument')
            ->with($this->equalTo($type), $this->equalTo($id));

        $handler = $this->getHandler($type, $strategy);
        $handler->handleEvent($event);
    }

    public function testBulkChangedProductTaxPricesEventTriggersRebuildProductPrices()
    {
        $ids = array(49,23,100,2,48,51);

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_BULK_CHANGED_PRODUCT_TAX_PRICES);
        $event->data = $ids;

        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('rebuildProductPrices')
            ->with($this->equalTo($ids));

        $handler = $this->getHandler('product', null, $backgroundStrategy);
        $handler->handledProductChangedTaxPriceEvent($event);
    }

    public function testProductChangedTaxPriceEventTriggersUpdateProductPricesByIds()
    {
        $id = rand(1, 1000);

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_CHANGED_TAX_PRICE);
        $event->data = array('id' => $id);

        $indexer = $this->getIndexer('product');
        $indexer
            ->expects($this->once())
            ->method('updateProductPricesByIds')
            ->with($this->equalTo(array($id)));

        $handler = $this->getHandler('product', null, null, array('getIndexerForType'));
        $handler
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo('product'))
            ->will($this->returnValue($indexer));

        $handler->handledProductChangedTaxPriceEvent($event);
    }

    /**
     * @return array
     */
    public function bulkEventsDataProvider()
    {
        $time = time();

        return array(
            array(
                'product',
                Store_Event::EVENT_PRODUCT_BULK_CHANGED_BRAND,
                array(3,19,203,92),
                null,
                array('prodbrandid' => 34, 'brandname' => 'Brand Name', 'prodlastmodified' => $time),
                array('brand_name' => 'Brand Name', 'date_updated' => date('c', $time), 'brand_id' => 34),
            ),
            array(
                'category',
                Store_Event::EVENT_CATEGORY_BULK_CHANGED_IMAGE,
                array(2,7,87,21),
                null,
                array('catimagefile' => ''),
                array('image_file' => ''),
            ),
            array(
                'category',
                Store_Event::EVENT_CATEGORY_BULK_CHANGED_VISIBILITY,
                array(1,5,10,34),
                null,
                array('catvisible' => 1),
                array('is_visible' => true),
            ),
        );
    }

    /**
     * @dataProvider bulkEventsDataProvider
     */
    public function testBulkChangeEventTriggersUpdateDocument(
        $type,
        $eventName,
        $ids,
        $before,
        $after,
        $expected
    ) {
        $data = array(
            'ids'    => $ids,
            'before' => $before,
            'after'  => $after,
        );

        $event = new Interspire_Event($eventName);
        $event->data = $data;

        /** @var PHPUnit_Framework_MockObject_MockObject|MockIndexStrategy $strategy */
        $strategy = $this->getMock('\Store\Search\IndexStrategy\MockIndexStrategy');

        foreach ($ids as $index => $id) {
            $this->addDocumentAssertionToStrategy($strategy, 'updateDocument', $id, $expected, $type, $index);
        }

        $handler = $this->getHandler($type, $strategy);
        $handler->handleBulkEvent($event);
    }

    public function testSampleDataInstalledEventTriggersRebuild()
    {
        $types = AbstractIndexer::getSupportedTypes();
        $handler = $this->getHandler($types, null, null, array('getIndexerForType'));

        foreach ($types as $i => $type) {
            $indexer = $this->getIndexer($type);
            $indexer
                ->expects($this->once())
                ->method('rebuildType')
                ->with($this->equalTo(false));

            $handler
                ->expects($this->at($i))
                ->method('getIndexerForType')
                ->with($this->equalTo($type))
                ->will($this->returnValue($indexer));
        }

        $event = new Interspire_Event(Store_Event::EVENT_SAMPLE_DATA_INSTALLED);
        $handler->handleSampleDataInstalledEvent($event);
    }

    public function testBindEventsWontRebind()
    {
        $mock = $this->getMockBuilder('\Store\Search\EventHandler');
        $handler = $mock
            ->disableOriginalConstructor()
            ->setMethods(array('getEventBindings'))
            ->getMock();

        $handler
            ->expects($this->once())
            ->method('getEventBindings')
            ->will($this->returnValue(array()));

        $handler->bindEvents();
        $handler->bindEvents();
    }

    public function testHandleIndexingEnabledEventTriggersRebuild()
    {
        $config = array(
            'Feature_SearchIndexing' => true,
        );

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $types = AbstractIndexer::getSupportedTypes();
        $handler = $this->getHandler(
            $types,
            null,
            null,
            array('getIndexerForType'),
            null,
            null,
            null,
            $settings
        );

        foreach ($types as $i => $type) {
            $indexer = $this->getIndexer($type);
            $indexer
                ->expects($this->once())
                ->method('rebuildType')
                ->with($this->equalTo(false));

            $handler
                ->expects($this->at($i))
                ->method('getIndexerForType')
                ->with($this->equalTo($type))
                ->will($this->returnValue($indexer));
        }

        $event = new Interspire_Event(\Store_Settings::EVENT_CONFIG_COMMITTED);
        $event->data = array(
            'previous' => array(),
            'current' => array(
                'Feature_SearchIndexing' => true,
            ),
        );

        $handler->handleIndexingEnabledEvent($event);

        $this->assertNotEmpty($settings->get('ExistingStoreInitialIndexDate'));
    }

    public function changedUrlEventDataProvider()
    {
        return array(
            array(Store_Event::EVENT_PRODUCT_CHANGED_URL, ProviderInterface::TYPE_PRODUCT),
            array(Store_Event::EVENT_CATEGORY_CHANGED_URL, ProviderInterface::TYPE_CATEGORY),
            array(Store_Event::EVENT_WEBSITE_WEB_PAGE_CHANGED_URL, ProviderInterface::TYPE_PAGE),
            array(Store_Event::EVENT_WEBSITE_NEWS_ITEM_CHANGED_URL, ProviderInterface::TYPE_POST),
        );
    }

    /**
     * @dataProvider changedUrlEventDataProvider
     */
    public function testChangedUrlEventTriggersUpdateUrl($eventName, $type)
    {
        $id = rand(1, 1000);
        $oldUrl = '/foo.html';
        $newUrl = '/bar.html';

        $event = new Interspire_Event($eventName);
        $event->data = array('id' => $id, 'before' => $oldUrl, 'after' => $newUrl);

        $indexer = $this->getIndexer($type);
        $indexer
            ->expects($this->once())
            ->method('updateUrl')
            ->with(
                $this->equalTo($id),
                $this->equalTo($oldUrl),
                $this->equalTo($newUrl)
            );

        $handler = $this->getHandler($type, null, null, array('getIndexerForType'));
        $handler
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo($type))
            ->will($this->returnValue($indexer));

        $handler->handleUrlChangedEvent($event);
    }

    public function testHandleBrandNameChangedEventTriggersUpdateBrandName()
    {
        $brandId = 77;
        $brandName = 'Updated Brand';

        $oldBrand = new \Store_Brand();
        $oldBrand
            ->setId($brandId)
            ->setName('Old Brand');

        $newBrand = new \Store_Brand();
        $newBrand
            ->setId($brandId)
            ->setName($brandName);

        $event = new Interspire_Event(Store_Event::EVENT_BRAND_CHANGED);
        $event->data = array(
            'id'     => $brandId,
            'before' => $oldBrand,
            'after'  => $newBrand,
        );

        $type = ProviderInterface::TYPE_PRODUCT;

        $indexer = $this->getIndexer($type);
        $indexer
            ->expects($this->once())
            ->method('updateBrandName')
            ->with(
                $this->equalTo($brandId),
                $this->equalTo($brandName),
                $this->equalTo(false)
            );

        $handler = $this->getHandler($type, null, null, array('getIndexerForType'));
        $handler
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo($type))
            ->will($this->returnValue($indexer));

        $handler->handleBrandNameChangedEvent($event);
    }

    public function testHandleBrandNameChangedEventWithUnchangedNameIsIgnored()
    {
        $brandId = 88;
        $brandName = 'Unchanged Brand';

        $brand = new \Store_Brand();
        $brand
            ->setId($brandId)
            ->setName($brandName);

        $event = new Interspire_Event(Store_Event::EVENT_BRAND_CHANGED);
        $event->data = array(
            'id'     => $brandId,
            'before' => $brand,
            'after'  => $brand,
        );

        $type = ProviderInterface::TYPE_PRODUCT;

        $handler = $this->getHandler($type, null, null, array('getIndexerForType'));
        $handler
            ->expects($this->never())
            ->method('getIndexerForType');

        $handler->handleBrandNameChangedEvent($event);
    }

    public function getHandleProductAttributeEventProductIdDataProvider()
    {
        return array(
            array(
                array('before' => new \Store_Product_Attribute(array('product_id' => 5))),
                5
            ),
            array(
                array('before' => new \Store_Product_Attribute(array('product_hash' => 43))),
                43
            ),
            array(
                array('after' => new \Store_Product_Attribute(array('product_id' => 3))),
                3
            ),
            array(
                array('after' => new \Store_Product_Attribute(array('product_hash' => 2))),
                2
            ),
            array(
                array(
                    'before' => new \Store_Product_Attribute(array('product_id' => 10)),
                    'after' => new \Store_Product_Attribute(array('product_id' => 11)),
                ),
                10
            ),
            array(
                array(
                    'before' => new \Store_Product_Attribute(array('product_hash' => 15)),
                    'after' => new \Store_Product_Attribute(array('product_id' => 16)),
                ),
                15
            ),
            array(
                array(
                    'before' => new \Store_Product_Attribute(),
                    'after' => new \Store_Product_Attribute(),
                    'extra' => array('product_id' => 22)
                ),
                22
            ),
        );
    }

    /**
     * @dataProvider getHandleProductAttributeEventProductIdDataProvider
     */
    public function testHandleProductAttributeEventAddsProductIdToScheduledList($data, $id)
    {
        $type = ProviderInterface::TYPE_PRODUCT;
        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
        $event->data = $data;

        $handler->handleProductAttributeEvent($event);

        $this->assertEquals(array($id), $handler->getModifiedAttributesProducts());
    }

    public function testHandleProductAttributeEventDoesntAddSameProductIdToListMoreThanOnce()
    {
        $type = ProviderInterface::TYPE_PRODUCT;
        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
        $event->data = array('before' => new \Store_Product_Attribute(array('product_id' => 4)));

        $handler->handleProductAttributeEvent($event);
        $handler->handleProductAttributeEvent($event);

        $this->assertEquals(array(4), $handler->getModifiedAttributesProducts());
    }

    public function testHandleProductAttributeEventRegistersShutdownFunctionForFirstCallOnly()
    {
        $type = ProviderInterface::TYPE_PRODUCT;
        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );
        $handler
            ->expects($this->once())
            ->method('registerFlushUpdateProductAttributesFunction');

        $productAttribute = new \Store_Product_Attribute();
        $productAttribute->setProductId(5);

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
        $event->data = array(
            'after'  => $productAttribute,
        );

        $handler->handleProductAttributeEvent($event);

        $handler->setShutDownRegistered();

        $productAttribute = new \Store_Product_Attribute();
        $productAttribute->setProductId(6);

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
        $event->data = array(
            'after'  => $productAttribute,
        );

        $handler->handleProductAttributeEvent($event);
    }

    public function testHandleProductAttributeEventFlushesQueueWhenLimitReached()
    {
        $ids = range(1, EventHandler::MAX_MODIFIED_ATTRIBUTE_PRODUCTS);

        $type = ProviderInterface::TYPE_PRODUCT;
        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction', 'flushUpdateAttributesForProducts'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );
        $handler
            ->expects($this->once())
            ->method('flushUpdateAttributesForProducts');

        foreach ($ids as $id) {
            $productAttribute = new \Store_Product_Attribute();
            $productAttribute->setProductId($id);

            $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
            $event->data = array(
                'after'  => $productAttribute,
            );

            $handler->handleProductAttributeEvent($event);
        }
    }

    public function testFlushUpdateAttributesForProducts()
    {
        $ids = array(4,2,78,1,33);

        $type = ProviderInterface::TYPE_PRODUCT;

        $indexer = $this->getIndexer($type);
        $indexer
            ->expects($this->once())
            ->method('updateAttributesForProducts')
            ->with(
                $this->equalTo($ids),
                $this->equalTo(false)
            );

        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction', 'getIndexerForType'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );

        $handler
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo($type))
            ->will($this->returnValue($indexer));

        foreach ($ids as $id) {
            $productAttribute = new \Store_Product_Attribute();
            $productAttribute->setProductId($id);

            $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
            $event->data = array(
                'after'  => $productAttribute,
            );

            $handler->handleProductAttributeEvent($event);
        }

        $handler->flushUpdateAttributesForProducts();

        $this->assertEquals(array(), $handler->getModifiedAttributesProducts());
    }

    public function testProductDeletionRemovesProductFromUpdateAttributesQueue()
    {
        $type = ProviderInterface::TYPE_PRODUCT;
        $id = 25;


        $indexer = $this->getIndexer($type);
        $indexer
            ->expects($this->once())
            ->method('deleteDocument')
            ->with($this->equalTo($id));

        $handler = $this->getHandler(
            $type,
            null,
            null,
            array('registerFlushUpdateProductAttributesFunction', 'getIndexerForType'),
            null,
            null,
            __NAMESPACE__ . '\TestSearchEventHandler'
        );

        $handler
            ->expects($this->once())
            ->method('getIndexerForType')
            ->with($this->equalTo($type))
            ->will($this->returnValue($indexer));

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_TYPE_PRODUCT_ATTRIBUTE_CREATED);
        $event->data = array('before' => new \Store_Product_Attribute(array('product_id' => $id)));

        $handler->handleProductAttributeEvent($event);

        $this->assertEquals(array($id), $handler->getModifiedAttributesProducts());

        $event = new Interspire_Event(Store_Event::EVENT_PRODUCT_DELETED);
        $event->data = array('id' => $id);

        $handler->handleEvent($event);

        $this->assertEquals(array(), $handler->getModifiedAttributesProducts());
    }
}

class TestSearchEventHandler extends EventHandler
{
    public function getModifiedAttributesProducts()
    {
        return $this->modifiedAttributeProducts;
    }

    public function setShutDownRegistered()
    {
        $this->updateAttributesShutdownRegistered = true;
    }
}
