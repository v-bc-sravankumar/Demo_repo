<?php

use DataModel\IdentityMap;
use Repository\Products;
use Store\Product;

class Unit_DomainModel_Repository_ProductsTest extends PHPUnit_Framework_TestCase
{
    public function testFindByIdForCopyingBlanksSubResourceIds()
    {
        $data = array(
            'id' => 987,
            'configurable_fields' => array(
                array('id' => 1, 'product_id' => 987),
                array('id' => 2, 'product_id' => 987),
            ),
            'custom_fields' => array(
                array('id' => 3, 'product_id' => 987),
                array('id' => 4, 'product_id' => 987),
            ),
            'discount_rules' => array(
                array('id' => 5, 'product_id' => 987),
                array('id' => 6, 'product_id' => 987),
            ),
            'rules' => array(
                array('id' => 7, 'product_id' => 987),
                array('id' => 8, 'product_id' => 987),
            ),
            'options' => array(
                array('id' => 9, 'product_id' => 987),
                array('id' => 10, 'product_id' => 987),
            ),
        );

        IdentityMap::putEntity(new Product($data));

        $expected = array(
            'id' => 987,
            'configurable_fields' => array(
                array('id' => 0, 'product_id' => 0),
                array('id' => 0, 'product_id' => 0),
            ),
            'custom_fields' => array(
                array('id' => 0, 'product_id' => 0),
                array('id' => 0, 'product_id' => 0),
            ),
            'discount_rules' => array(
                array('id' => 0, 'product_id' => 0),
                array('id' => 0, 'product_id' => 0),
            ),
            'rules' => array(
                array('id' => 0, 'product_id' => 0),
                array('id' => 0, 'product_id' => 0),
            ),
            'options' => array(
                array('id' => 0, 'product_id' => 0),
                array('id' => 0, 'product_id' => 0),
            ),
        );

        $repository = new Products();
        $product = $repository->findByIdForCopying(987);

        $this->assertEquals($expected, $product);

        IdentityMap::deleteEntity('Store\Product', 987);
    }


    public function testUpdateImage()
    {
        $productId = 986;
        $imageId = 985;

        $imageData = new stdClass();
        $imageData->image_file = 'http://www.google.com.au/intl/en_ALL/images/logos/images_logo_lg.gif';
        $imageData->is_thumbnail = true;
        $imageData->sort_order = 0;
        $imageData->description = 'some description';

        // should be unset by updateImage
        $imageData->id = 9999;
        $imageData->product_id = 8888;
        $imageData->date_created = 1362613072;
        $imageData->image_url = 'http://www.example.com/something.gif';
        $imageData->image_orig = 'http://www.example.com/original.gif';

        $repository = new ProductsRepositoryMock1();
        $result = $repository->updateImage($productId, $imageId, $imageData);

        $this->assertEquals('image updated', $result);
    }

    public function testUpdateImageWithNoDescription()
    {
        $productId = 986;
        $imageId = 985;

        $imageData = new stdClass();
        $imageData->image_file = 'http://www.google.com.au/intl/en_ALL/images/logos/images_logo_lg.gif';
        $imageData->is_thumbnail = false;
        $imageData->sort_order = 1;
        //$imageData->description = 'No Description';

        $repository = new ProductsRepositoryMock2();
        $result = $repository->updateImage($productId, $imageId, $imageData);

        $this->assertEquals('image updated', $result);
    }

    public function testUpdateImageResizedFileName()
    {
        $productId = 986;
        $imageId = 985;

        $imageData = new stdClass();
        $imageData->image_file = 'q/999/Jellyfish__54329.jpg';
        $imageData->is_thumbnail = false;
        $imageData->sort_order = 1;

        $repository = new ProductsRepositoryMock3();
        $result = $repository->updateImage($productId, $imageId, $imageData);

        $this->assertArrayHasKey('image_file', $result);
        $this->assertArrayHasKey('image_orig', $result);
    }
}

class ProductsRepositoryMock1 extends Repository\Products
{
    //override to decouple Products_Images updateObject
    //but test the method was actually called.
    protected function updateImageObject($productId, $imageId, $imageData)
    {
        $test = new Unit_DomainModel_Repository_ProductsTest();
        $test->assertEquals($imageData->description, 'some description');
        $test->assertEquals($imageData->image_file, 'http://www.google.com.au/intl/en_ALL/images/logos/images_logo_lg.gif');

        return 'image updated';
    }

    protected function getResizedImage($image, $size)
    {
        return $image;
    }
}

class ProductsRepositoryMock2 extends Repository\Products
{
    //override to decouple Products_Images updateObject
    //but test the method was actually called.
    protected function updateImageObject($productId, $imageId, $imageData)
    {
        $test = new Unit_DomainModel_Repository_ProductsTest();
        $test->assertEquals($imageData->description, '');
        $test->assertEquals($imageData->is_thumbnail, false);
        $test->assertEquals($imageData->sort_order, 1);

        return 'image updated';
    }

    protected function getResizedImage($image, $size)
    {
        return $image;
    }
}

class ProductsRepositoryMock3 extends Repository\Products
{
    //override to decouple Products_Images updateObject
    //but test the method was actually called.
    protected function updateImageObject($productId, $imageId, $imageData)
    {
        $test = new Unit_DomainModel_Repository_ProductsTest();
        $test->assertEquals($imageData->image_file, 'q/999/Jellyfish__54329.jpg');

        //return array with image_file
        $image = array ('id' => 985,'product_id' => 986, 'image_file' => $imageData->image_file,
                'is_thumbnail' => false, 'sort_order' => 1, 'description' => '',
                'date_created' => '',
                );

        return $image;
    }
}
