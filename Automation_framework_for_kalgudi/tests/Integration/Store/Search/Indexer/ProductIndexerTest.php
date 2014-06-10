<?php

namespace Integration\Store\Search\Indexer;

use Bigcommerce\SearchClient\Document\AbstractDocument;
use Bigcommerce\SearchClient\Document\ProductDocument;
use Bigcommerce\SearchClient\DocumentMapper\DocumentMapperIterator;
use Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface;
use Logging\Logger;
use Psr\Log\LoggerInterface;
use Store\Search\Indexer\ProductIndexer;
use Store\Search\IndexStrategy\BackgroundIndexStrategy;
use Store\Search\IndexStrategy\MockIndexStrategy;
use Store\Search\Provider\Local\DocumentMapper\ProductDocumentMapper;
use Store_Statsd;
use Test\FixtureTest;
use Traversable;
use iterator\MappingIterator;

/**
 * @group nosample
 */
class ProductIndexerTest extends FixtureTest
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Store_Statsd
     */
    private $statsd;

    /**
     * @var array
     */
    private $createdModels = array();

    /**
     * @return BackgroundIndexStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getBackgroundStrategy()
    {
        $mock = $this->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy');
        $mock->disableOriginalConstructor();
        return $mock->getMock();
    }

    /**
     * @param IndexStrategyInterface $strategy
     * @param IndexStrategyInterface $backgroundStrategy
     *
     * @return TestProductIndexer
     */
    private function getIndexer(
        IndexStrategyInterface $strategy = null,
        IndexStrategyInterface $backgroundStrategy = null
    ) {
        if ($strategy === null) {
            $strategy = new MockIndexStrategy();
        }

        if ($backgroundStrategy === null) {
            $backgroundStrategy = $this->getBackgroundStrategy();
        }

        return new TestProductIndexer(
            $strategy,
            new ProductDocumentMapper(),
            $this->logger,
            $this->statsd,
            $backgroundStrategy
        );
    }

    /**
     * @param ProductDocument[]|Traversable $iterator
     * @param array $expected
     *
     * @return int
     */
    private function assertIterator($iterator, array $expected)
    {
        $count = 0;

        foreach ($iterator as $document) {
            $this->assertInstanceOf('\Bigcommerce\SearchClient\Document\ProductDocument', $document);
            $this->assertContains($document->getId(), array_keys($expected));

            $product = array(
                'name' => $document->getName(),
                'tax_zone_prices' => $document->getTaxZonePrices(),
                'attributes' => $document->getAttributes(),
            );

            $this->assertEquals($expected[$document->getId()], $product);

            $this->assertTrue($document->isValid(), 'Document ' . $document->getId() . ' is not valid.');

            $count++;
        }

        return $count;
    }

    private function createAttributesForProduct($productId)
    {
        $createdAttributes = array();

        // create some attributes and assign them to this product
        for ($x = 0; $x < rand(0,3); $x++) {
            $attribute = new \Store_Attribute();
            $attribute
                ->setName($this->getFaker()->unique()->sentence())
                ->setDisplayName($this->getFaker()->unique()->sentence())
                ->setType(new \Store_Attribute_Type_Configurable_Entry_Text());

            if (!$attribute->save()) {
                $this->fail('Failed to save attribute');
            }

            $productAttribute = new \Store_Product_Attribute();
            $productAttribute
                ->setAttributeId($attribute->getId())
                ->setProductId($productId)
                ->setDisplayName('Product ' . $attribute->getDisplayName());

            if (!$productAttribute->save()) {
                $this->fail('Failed to save product attribute');
            }

            $this->createdModels[] = $attribute;

            $createdAttributes[] = array(
                'id'   => $attribute->getId(),
                'name' => $productAttribute->getDisplayName(),
            );
        }

        return $createdAttributes;
    }

    /**
     * @return array
     */
    private function loadProducts()
    {
        $expected = array();

        $products = $this->loadFixture('products');
        $this->loadFixture('custom_urls_products');
        $pricing = $this->loadFixture('product_tax_pricing');

        $pricingByReference = array();
        foreach ($pricing as $price) {
            $reference = $price['price_reference'];

            if (!isset($pricingByReference[$reference])) {
                $pricingByReference[$reference] = array();
            }

            $pricingByReference[$reference]['tax_zone_id_' . $price['tax_zone_id']] = $price['calculated_price'];
        }

        foreach ($products as $product) {
            $expected[$product['productid']] = array(
                'name' => $product['prodname'],
                'tax_zone_prices' => $pricingByReference[$product['prodcalculatedprice']],
                'attributes' => $this->createAttributesForProduct($product['productid']),
            );
        }

        return $expected;
    }

    public function setUp()
    {
        $this->logger = Logger::getInstance();

        $this->statsd = $GLOBALS["app"]["statsd.client"];
        $this->statsd->setEnabled(false);
    }

    public function tearDown()
    {
        foreach ($this->createdModels as $model) {
            $model->delete();
        }

        parent::tearDown();
    }

    public function testGetAll()
    {
        $expected = $this->loadProducts();

        $iterator = $this->getIndexer()->getAllIterator();

        $count = $this->assertIterator($iterator, $expected);

        $this->assertEquals(count($expected), $count);

        // we have a query limit of 5 (defined in the test environment), and 28 products. this should give us 6
        // iterators
        $this->assertEquals(6, $iterator->getInnerIterator()->getCallbackIndex());
    }

    public function testGetByIds()
    {
        $products = $this->loadProducts();
        $ids = array_rand($products, 6);

        $expected = array_intersect_key($products, array_fill_keys($ids, null));

        $iterator = $this->getIndexer()->getByIdsIterator($ids);

        $count = $this->assertIterator($iterator, $expected);

        $this->assertEquals(count($ids), $count);
    }

    public function testUpdateProductPricesByIds()
    {
        $products = $this->loadProducts();

        $ids = array_rand($products, 2);

        $expected = array(
            $ids[0] => array(
                'prices.price'          => $this->getFaker()->randomFloat(2, 0),
                'prices.cost'           => $this->getFaker()->randomFloat(2, 0),
                'prices.retail'         => $this->getFaker()->randomFloat(2, 0),
                'prices.sale'           => $this->getFaker()->randomFloat(2, 0),
                'prices.calculated'     => $this->getFaker()->randomFloat(2, 0),
                'hide_price'            => $this->getFaker()->boolean(),
                'tax_class_id'          => $this->getFaker()->randomNumber(0, 4),
                'prices.tax_zone_prices' => array(
                    'tax_zone_id_1' => $this->getFaker()->randomFloat(2, 0),
                    'tax_zone_id_2' => $this->getFaker()->randomFloat(2, 0),
                ),
            ),
            $ids[1] => array(
                'prices.price'          => $this->getFaker()->randomFloat(2, 0),
                'prices.cost'           => $this->getFaker()->randomFloat(2, 0),
                'prices.retail'         => $this->getFaker()->randomFloat(2, 0),
                'prices.sale'           => $this->getFaker()->randomFloat(2, 0),
                'prices.calculated'     => $this->getFaker()->randomFloat(2, 0),
                'hide_price'            => $this->getFaker()->boolean(),
                'tax_class_id'          => $this->getFaker()->randomNumber(0, 4),
                'prices.tax_zone_prices' => array(
                    'tax_zone_id_1' => $this->getFaker()->randomFloat(2, 0),
                    'tax_zone_id_2' => $this->getFaker()->randomFloat(2, 0),
                ),
            ),
        );

        foreach ($expected as $id => $product) {
            $this->getDb()->UpdateQuery('products', array(
                'prodprice'             => $product['prices.price'],
                'prodcostprice'         => $product['prices.cost'],
                'prodretailprice'       => $product['prices.retail'],
                'prodsaleprice'         => $product['prices.sale'],
                'prodcalculatedprice'   => $product['prices.calculated'],
                'prodhideprice'         => (int)$product['hide_price'],
                'tax_class_id'          => $product['tax_class_id'],
            ), 'productid = ' . $id);

            foreach (range(1, 2) as $index) {
                $this->getDb()->Query(
                    'REPLACE INTO product_tax_pricing SET
                    price_reference  = ' . $product['prices.calculated'] . ',
                    calculated_price = ' . $product['prices.tax_zone_prices']['tax_zone_id_' . $index] . ',
                    tax_zone_id      = ' . $index . ',
                    tax_class_id     = ' . $product['tax_class_id']
                );
            }
        }

        /** @var MockIndexStrategy|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this
            ->getMockBuilder('Store\Search\IndexStrategy\MockIndexStrategy')
            ->setMethods(array('bulkUpdateDocuments'))
            ->getMock();

        $strategy
            ->expects($this->once())
            ->method('bulkUpdateDocuments')
            ->with($this->callback(function (DocumentMapperIterator $iterator) use ($expected) {
                $count = 0;

                /** @var AbstractDocument $document */
                foreach ($iterator as $document) {
                    if (!($document instanceof ProductDocument) ||
                        !array_key_exists($document->getId(), $expected) ||
                        !$document->isDirty()
                    ) {
                        return false;
                    }

                    $dirtyData = $document->getDirtyData();

                    if ($expected[$document->getId()] != $dirtyData || !$document->isValid(true)) {
                        return false;
                    }

                    $count++;
                }

                return $count == count($expected);
            }));

        $indexer = $this->getIndexer($strategy);
        $indexer->updateProductPricesByIds($ids);
    }

    public function testIndexDocumentIncludesTaxPricesAndAttributes()
    {
        $products = $this->loadFixture('products');
        $this->loadFixture('custom_urls_products');
        $pricing = $this->loadFixture('product_tax_pricing');

        $product = reset($products);
        $product = $product->getData();
        $id = $product['productid'];
        $calculatedPrice = $product['prodcalculatedprice'];
        $taxClassId = $product['tax_class_id'];

        $pricesForProduct = array();
        foreach ($pricing as $price) {
            if ($price['price_reference'] == $calculatedPrice && $price['tax_class_id'] == $taxClassId) {
                $pricesForProduct['tax_zone_id_' . $price['tax_zone_id']] = $price['calculated_price'];
            }
        }

        // convert our price fields into doubles to simulate the format of the data coming through a product
        // added/edited event
        $priceFields = array(
            'prodprice',
            'prodcostprice',
            'prodretailprice',
            'prodsaleprice',
            'prodcalculatedprice',
        );

        foreach ($priceFields as $field) {
            $product[$field] = (double)$product[$field];
        }

        $url = \Store_CustomUrl::findByContent('product', $id)->first();
        $product['url'] = $url->getUrl();

        $expectedAttributes = $this->createAttributesForProduct($id);

        /** @var MockIndexStrategy|\PHPUnit_Framework_MockObject_MockObject $strategy */
        $strategy = $this
            ->getMockBuilder('Store\Search\IndexStrategy\MockIndexStrategy')
            ->setMethods(array('indexDocument'))
            ->getMock();

        $strategy
            ->expects($this->once())
            ->method('indexDocument')
            ->with($this->callback(function (ProductDocument $document) use ($pricesForProduct, $expectedAttributes) {
                return $document->getTaxZonePrices() == $pricesForProduct &&
                    $document->getAttributes() == $expectedAttributes &&
                    $document->isValid(true);
            }));

        $indexer = $this->getIndexer($strategy);
        $indexer->indexDocument($id, $product);
    }

    public function testRebuildProductPricesDispatchesRebuildProductPricesJob()
    {
        $ids = array(12,99,3,39,44);

        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('rebuildProductPrices')
            ->with($this->equalTo($ids));

        $indexer = $this->getIndexer(null, $backgroundStrategy);
        $indexer->rebuildProductPrices($ids);
    }

    public function testUpdateBrandNameDispatchesUpdateBrandNameOnProductsJob()
    {
        $brandId = 32;
        $brandName = 'New Brand';

        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('updateBrandNameOnProducts')
            ->with($this->equalTo($brandId), $this->equalTo($brandName));

        $indexer = $this->getIndexer(null, $backgroundStrategy);
        $indexer->updateBrandName($brandId, $brandName);
    }

    public function testUpdateBrandNameInBackground()
    {
        $products = $this->loadFixture('products');
        $product = reset($products);
        $product = $product->getData();

        $brandId = $product['prodbrandid'];
        $brandName = 'Updated Brand';

        $expectedProducts = array();
        foreach ($products as $product) {
            if ($product['prodbrandid'] === $brandId) {
                $expectedProducts[] = array(
                    'productid' => $product['productid'],
                    'brandname' => $brandName,
                );
            }
        }

        $indexer = $this->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('updateIterator'))
            ->getMock();

        $indexer
            ->expects($this->once())
            ->method('updateIterator')
            ->with($this->callback(function (MappingIterator $iterator) use ($brandId, $expectedProducts) {
                $queryIterator = $iterator->getInnerIterator();

                if (!($queryIterator instanceof \Db_QueryIterator)) {
                    return false;
                }

                $expectedQuery = 'SELECT productid FROM products WHERE prodbrandid = ' . $brandId;

                if ($queryIterator->getQuery() != $expectedQuery) {
                    return false;
                }

                foreach ($iterator as $item) {
                    $foundItem = false;
                    foreach ($expectedProducts as $expectedProduct) {
                        if ($item === $expectedProduct) {
                            $foundItem = true;
                        }
                    }

                    if (!$foundItem) {
                        return false;
                    }
                }

                return true;
            }));

        $indexer->updateBrandName($brandId, $brandName, true);
    }

    public function testUpdateAttributesForProductsDispatchesUpdateAttributesForProductsJob()
    {
        $ids = array(4,5,6,10,11,12);

        $backgroundStrategy = $this->getBackgroundStrategy();
        $backgroundStrategy
            ->expects($this->once())
            ->method('updateAttributesForProducts')
            ->with($this->equalTo($ids));

        $indexer = $this->getIndexer(null, $backgroundStrategy);
        $indexer->updateAttributesForProducts($ids);
    }

    private function addIndexerAssertionForAttributes($indexer, $ids, $expected)
    {
        $indexer
            ->expects($this->once())
            ->method('updateIterator')
            ->with($this->callback(function (MappingIterator $iterator) use ($ids, $expected) {
                $arrayIterator = $iterator->getInnerIterator();

                if (!($arrayIterator instanceof \ArrayIterator)) {
                    return false;
                }

                $iteratorIds = $arrayIterator->getArrayCopy();
                sort($iteratorIds);
                sort($ids);

                if ($iteratorIds != $ids) {
                    return false;
                }

                foreach ($iterator as $item) {
                    $id = $item['productid'];

                    if (!in_array($id, $ids)) {
                        return false;
                    }

                    if ($item['attributes'] != $expected[$id]['attributes']) {
                        return false;
                    }
                }

                return true;
            }));
    }

    public function testUpdateAttributesForProductsInBackground()
    {
        $products = $this->loadProducts();

        $ids = array_rand($products, 6);
        $expected = array_intersect_key($products, array_fill_keys($ids, null));

        $indexer = $this->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('updateIterator', 'logDebug'))
            ->getMock();

        $this->addIndexerAssertionForAttributes($indexer, $ids, $expected);

        $indexer->updateAttributesForProducts($ids, true);
    }

    public function testUpdateAttributesForProductsOnlyUpdatesForProductsThatExist()
    {
        $products = $this->loadProducts();

        $ids = array_rand($products, 6);
        $expected = array_intersect_key($products, array_fill_keys($ids, null));
        $idsToUpdate = array_merge($ids, array(2000, 3000));

        $indexer = $this->getMockBuilder('\Store\Search\Indexer\ProductIndexer')
            ->disableOriginalConstructor()
            ->setMethods(array('updateIterator', 'logDebug'))
            ->getMock();

        $this->addIndexerAssertionForAttributes($indexer, $ids, $expected);

        $indexer->updateAttributesForProducts($idsToUpdate, true);
    }
}

class TestProductIndexer extends ProductIndexer
{
    /**
     * @return DocumentMapperIterator
     */
    public function getAllIterator()
    {
        return $this->getDocumentIteratorForRecords($this->getAll());
    }

    /**
     * @param array $ids
     * @return DocumentMapperIterator
     */
    public function getByIdsIterator(array $ids)
    {
        return $this->getDocumentIteratorForRecords($this->getByIds($ids));
    }
}
