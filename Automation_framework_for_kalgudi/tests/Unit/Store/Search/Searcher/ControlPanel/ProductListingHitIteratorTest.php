<?php

namespace Unit\Store\Search\Searcher\ControlPanel;

use Bigcommerce\SearchClient\Document\ProductDocument;
use Bigcommerce\SearchClient\Hit\Hit;
use Bigcommerce\SearchClient\Hit\HitIterator;
use Bigcommerce\SearchClient\Hit\HitParserInterface;
use DataModel\ArrayIterator;
use Repository\Products;
use Store\Product;
use Store\Searcher\ControlPanel\ProductListingHitIterator;

class ProductListingHitIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCombinationsEmpty()
    {
        $product1 = new ProductDocument();
        $product1->setInventoryTracking(ProductDocument::INVENTORY_TRACKING_NONE);

        $product2 = new ProductDocument();
        $product2->setInventoryTracking(ProductDocument::INVENTORY_TRACKING_PRODUCT);

        /** @var Products|\PHPUnit_Framework_MockObject_MockObject $products */
        $products = $this
            ->getMockBuilder('Repository\Products')
            ->disableOriginalConstructor()
            ->setMethods(array('getProductCombinationRaw'))
            ->getMock();

        // Assert that the repository is not queried.
        $products
            ->expects($this->never())
            ->method('getProductCombinationRaw');

        $iterator = new ProductListingHitIterator(
            new HitIterator(new ArrayIterator(array($product1, $product2)), new TestHitParser()),
            $products,
            120
        );

        // Assert that, when there are no products with inventory tracking set to 'OPTIONS', the array is empty.
        $this->assertAttributeEmpty('combinations', $iterator);
    }

    public function testGetCombinations()
    {
        $product1 = new ProductDocument();
        $product1->setInventoryTracking(ProductDocument::INVENTORY_TRACKING_OPTIONS);
        $product1->setId(123);

        /** @var Products|\PHPUnit_Framework_MockObject_MockObject $products */
        $products = $this
            ->getMockBuilder('Repository\Products')
            ->disableOriginalConstructor()
            ->setMethods(array('getProductCombinationRaw'))
            ->getMock();

        // Assert that the repository is queried for a list of combinations.
        $products
            ->expects($this->at(0))
            ->method('getProductCombinationRaw')
            ->with($this->equalTo(array(123)))
            ->will($this->returnValue(array(456 => array('product_id' => 123, 'other' => 'test'))));

        $iterator = new ProductListingHitIterator(
            new HitIterator(new ArrayIterator(array($product1)), new TestHitParser()),
            $products,
            120
        );

        // Assert that, when there are products with inventory tracking set to 'OPTIONS', the array is not empty.
        $this->assertAttributeNotEmpty('combinations', $iterator);
    }

    public function testMapDocument()
    {
        $product1 = new ProductDocument();
        $product1->setAvailability('test-availability');
        $product1->setCalculatedPrice(123.45);
        $product1->setId(123);
        $product1->setId(123);
        $product1->setInventoryLevel(10);
        $product1->setInventoryTracking(ProductDocument::INVENTORY_TRACKING_OPTIONS);
        $product1->setIsFeatured(true);
        $product1->setIsVisible(true);
        $product1->setLowInventoryLevel(5);
        $product1->setName('test-name');
        $product1->setSku('test-sku');
        $product1->setThumbnailImageId(456);
        $product1->setThumbnailImagePath('test-thumbnail-path');
        $product1->setUrl('test-url');

        /** @var Products|\PHPUnit_Framework_MockObject_MockObject $products */
        $products = $this
            ->getMockBuilder('Repository\Products')
            ->disableOriginalConstructor()
            ->setMethods(array('getProductCombinationRaw'))
            ->getMock();

        /** @var \ISC_PRODUCT_IMAGE|\PHPUnit_Framework_MockObject_MockObject $image */
        $image = $this
            ->getMockBuilder('ISC_PRODUCT_IMAGE')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setProductImageId',
                    'setProductId',
                    'setSourceFilePath',
                    'setIsThumbnail',
                    'getResizedUrl'
                )
            )
            ->getMock();

        // Assert that the product image is created correctly and that it is queried for the image URL.
        $image
            ->expects($this->at(0))
            ->method('setProductImageId')
            ->with($this->equalTo(456));
        $image
            ->expects($this->at(1))
            ->method('setProductId')
            ->with($this->equalTo(123));
        $image
            ->expects($this->at(2))
            ->method('setSourceFilePath')
            ->with($this->equalTo('test-thumbnail-path'));
        $image
            ->expects($this->at(3))
            ->method('setIsThumbnail')
            ->with($this->isTrue());
        $image
            ->expects($this->at(4))
            ->method('getResizedUrl')
            ->with($this->equalTo(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL))
            ->will($this->returnValue('test-image-url'));

        // Assert that the repository is queried for a list of combinations.
        $products
            ->expects($this->at(0))
            ->method('getProductCombinationRaw')
            ->with($this->equalTo(array(123)))
            ->will($this->returnValue(array(456 => array('product_id' => 123, 'other' => 'test'))));


        $iterator = new ProductListingHitIterator(
            new HitIterator(new ArrayIterator(array($product1)), new TestHitParser()),
            $products,
            ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL,
            $image
        );

        foreach ($iterator as $data) {
            $this->assertEquals(
                array(
                    'availability'            => 'test-availability',
                    'calculated_price'        => 123.45,
                    'id'                      => 123,
                    'image_url'               => 'test-image-url',
                    'inventory_level'         => 10,
                    'inventory_tracking'      => 'sku',
                    'inventory_warning_level' => 5,
                    'is_featured'             => true,
                    'is_visible'              => true,
                    'name'                    => 'test-name',
                    'sku'                     => 'test-sku',
                    'thumbnail'               => true,
                    'url'                     => 'test-url',
                    'inventory_percent'       => 100,
                    'combinations'            => array(array('product_id' => 123, 'other' => 'test')),
                    'sku_combinations'        => true,
                    'image_max_size'          => 'small',
                ),
                $data
            );
        }
    }
}

class TestHitParser implements HitParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        return new Hit($data);
    }
}
