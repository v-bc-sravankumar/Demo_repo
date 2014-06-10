<?php

class Unit_Repositories_Products extends PHPUnit_Framework_TestCase
{

    private $repo = null;

    public function setUp()
    {
        $this->repo = new \Repository\Products();
    }

    private function makeMockImageResizer($size)
    {
        $imageResizer = $this->getMock('ImageResizer',
            array(
                'populateFromDatabaseRow',
                'getResizedUrl',
                'getImageLibrary',
            )
        );

        $imageResizer->expects($this->atLeastOnce())
            ->method('populateFromDatabaseRow');

        $imageResizer->expects($this->any())
            ->method('getResizedUrl')
            ->with($size)
            ->will($this->returnValue("thumbnail_{$size}_{$size}.ext"));

        return $imageResizer;
    }

    public function testAppendResizedThumbnailWithoutThumbnail()
    {
        $imageResizer = $this->makeMockImageResizer(50);
        $imageResizer->expects($this->atLeastOnce())
            ->method('populateFromDatabaseRow')
            ->will($this->throwException(new Exception("cannot load the image from raw data")));

        $this->repo->setImageResizer($imageResizer);
        $products = $this->repo->appendResizedThumbnail(array(), array(array('imageprodid' => 'product 1')), 'some valid size');

        foreach($products as $product){
            $this->assertFalse(array_key_exists('image_url', $product));
            $this->assertArrayHasKey('image_max_size', $product);
        }
    }

    public function testAppendResizedThumbnail()
    {
        $imageResizer = $this->makeMockImageResizer(100);
        $this->repo->setImageResizer($imageResizer);

        $products = $this->repo->appendResizedThumbnail(
            array(
                'product 1' => array(
                    'id' => 'product 1',
                ),
            ),
            array(
                array(
                    'imageprodid' => 'product 1',
                ),
            ),
            100);

        foreach($products as $product){
            $this->assertEquals('thumbnail_100_100.ext', $product['image_url']);
            $this->assertEquals('medium', $product['image_max_size']);
        }
    }

    public function testAppendStoreFrontUrl()
    {
        $products = array(
            'product 1' => array(
                'id' => 'product 1',
                'name' => 'product 1 name',
            ),
        );

        $urlGenerator = $this->getMock('Store_UrlGenerator_Mock', array('getStoreFrontUrl'));
        $urlGenerator->expects($this->any())
            ->method('getStoreFrontUrl')
            ->will($this->returnValue('product 1 url'));

        $this->repo->setUrlGenerator($urlGenerator);
        $products = $this->repo->appendStoreFrontUrl($products);

        foreach($products as $product){
            $this->assertEquals('product 1 url', $product['url']);
        }
    }

    public function testAppendCombinations_NoSku()
    {
        $products = array(
            'product 1' => array(
                'id' => 'product 1',
            ),
        );

        $products = $this->repo->appendCombinations($products, array());

        foreach($products as $product) {
            $this->assertFalse(isset($product['combinations']));
            $this->assertFalse($product['sku_combinations']);
        }
    }

    public function testAppendCombinations_MultipleSku()
    {
        $products = array(
            'product 1' => array(
                'id' => 'product 1',
            ),
        );
        $rawCombinations = array(
            array(
                'id' => 'combination 1',
                'product_id' => 'product 1',
                'stock_level' => 10,
                'low_stock_level' => 2,
                'options' => array(
                    array(
                        'display_name' => 'option 1',
                        'label' => 'option value 1',
                    ),
                    array(
                        'display_name' => 'option 2',
                        'label' => 'option value 2',
                    ),
                ),
            ),
        );

        $products = $this->repo->appendCombinations($products, $rawCombinations);

        foreach ($products as $product) {
            $this->assertTrue($product['sku_combinations']);
            $combination = $product['combinations'][0];
            $this->assertEquals(10, $combination['stock_level']);
            $this->assertEquals(2, $combination['low_stock_level']);
            $this->assertEquals(array(
                array(
                    'display_name' => 'option 1',
                    'label' => 'option value 1',
                ),
                array(
                    'display_name' => 'option 2',
                    'label' => 'option value 2',
                )
            ), $combination['options']);
        }
    }

    public function testAppendInventoryPercentage_Empty()
    {
        $products = $this->repo->appendInventoryPercentage($this->makeProductsWithInventory(0, 0));

        foreach($products as $product) {
            $this->assertEquals(0, $product['inventory_percent']);
        }
    }

    public function testAppendInventoryPercentage_Full()
    {
        $products = $this->repo->appendInventoryPercentage($this->makeProductsWithInventory(1, 0));

        foreach($products as $product) {
            $this->assertEquals(100, $product['inventory_percent']);
        }
    }

    public function testAppendInventoryPercentage_Half()
    {

        $products = $this->repo->appendInventoryPercentage($this->makeProductsWithInventory(5, 5));

        foreach($products as $product) {
            $this->assertEquals(50, $product['inventory_percent']);
        }
    }

    private function makeProductsWithInventory($current, $low)
    {
        return array(
            'product 1' => array(
                'id' => 'product 1',
                'inventory_tracking' => 'simple',
                'inventory_level' => $current,
                'inventory_warning_level' => $low,
            ),
        );
    }
}
