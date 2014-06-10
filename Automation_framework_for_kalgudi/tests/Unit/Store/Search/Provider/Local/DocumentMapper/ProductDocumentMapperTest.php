<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Bigcommerce\SearchClient\Document\ProductDocument;
use Store\Search\Provider\Local\DocumentMapper\ProductDocumentMapper;

class ProductDocumentMapperTest extends DocumentMapperTestCase
{
    private function mapToDocument($data)
    {
        $mapper = $this->getMock('\Store\Search\Provider\Local\DocumentMapper\ProductDocumentMapper', array('getBrandName'));
        return $mapper->mapToDocument($data);
    }

    private function mapFromDocument($document)
    {
        $mapper = new ProductDocumentMapper();
        return $mapper->mapFromDocument($document);
    }

    public function testMapToDocument()
    {
        $dateImported = time();
        $dateAdded = time() - 50;
        $dateModified = time() - 1000;

        $taxZonePrices = array(
            'tax_zone_id_1' => 14,
            'tax_zone_id_2' => 20,
        );

        $attributes = array(
            array(
                'id'   => 1,
                'name' => 'Color',
            ),
            array(
                'id'   => '2',
                'name' => 'Size',
            ),
        );

        $productData = array(
            'productid'             => '4',
            'prodname'              => 'My Product',
            'prodcode'              => 'MYPROD',
            'upc'                   => '1234567890',
            'proddesc'              => 'My product description',
            'prodsearchkeywords'    => 'key,,word',

            'prodratingtotal'       => '20',
            'prodnumratings'        => '5',

            'prodconfigfields'      => 'foo',
            'prodeventdaterequired' => '1',
            'prodallowpurchases'    => '1',
            'prodtype'              => PT_DIGITAL,

            'prodcatids'            => '1,5,8,,0',

            'imagefile'             => 'product.png',
            'imageid'               => '12',

            'url'                   => '/my-product',
            'prodcondition'         => 'New',

            'proddateadded'         => $dateAdded,
            'prodlastmodified'      => $dateModified,
            'last_import'           => $dateImported,

            'attributes'            => $attributes,

            'product_type_id'       => '5',
            'tax_class_id'          => '0',
            'prodbrandid'           => '30',
            'brandname'             => 'Test Brand',
            'prodcurrentinv'        => '87',
            'prodlowinv'            => '3',
            'prodinvtrack'          => '1',
            'prodnumsold'           => '56',
            'prodsortorder'         => '8',

            'prodprice'             => '123.4',
            'prodcostprice'         => '456.7',
            'prodretailprice'       => '789.1',
            'prodsaleprice'         => '987.6',
            'prodcalculatedprice'   => '654.3',

            'tax_zone_prices'       => $taxZonePrices,

            'prodvisible'           => '1',
            'prodfeatured'          => '0',
            'prodfreeshipping'      => '1',
            'prodhideprice'         => '0',
        );

        $document = $this->mapToDocument($productData);

        $this->validateDocument($document);

        $this->assertEquals(4, $document->getId());
        $this->assertEquals('My Product', $document->getName());
        $this->assertEquals('m', $document->getNameFirstLetter());
        $this->assertEquals('MYPROD', $document->getSku());
        $this->assertEquals('1234567890', $document->getUpc());
        $this->assertEquals('My product description', $document->getDescription());
        $this->assertEquals(array('key','word'), $document->getKeywords());
        $this->assertEquals(123.4, $document->getPrice());
        $this->assertEquals(456.7, $document->getCostPrice());
        $this->assertEquals(789.1, $document->getRetailPrice());
        $this->assertEquals(987.6, $document->getSalePrice());
        $this->assertEquals(654.3, $document->getCalculatedPrice());
        $this->assertEquals($taxZonePrices, $document->getTaxZonePrices());
        $this->assertFalse($document->getHidePrice());
        $this->assertEquals(0, $document->getTaxClassId());
        $this->assertEquals(30, $document->getBrandId());
        $this->assertEquals('Test Brand', $document->getBrandName());
        $this->assertEquals(array(1,5,8), $document->getCategoryIds());
        $this->assertEquals(87, $document->getInventoryLevel());
        $this->assertEquals(3, $document->getLowInventoryLevel());
        $this->assertEquals(ProductDocument::INVENTORY_TRACKING_PRODUCT, $document->getInventoryTracking());
        $this->assertEquals(56, $document->getQuantitySold());
        $this->assertEquals(ProductDocument::CONDITION_NEW, $document->getCondition());
        $this->assertTrue($document->getIsVisible());
        $this->assertFalse($document->getIsFeatured());
        $this->assertTrue($document->getIsConfigurable());
        $this->assertTrue($document->getIsDigital());
        $this->assertTrue($document->getHasFreeShipping());
        $this->assertTrue($document->getHasEventDate());
        $this->assertTrue($document->getHasConfigurableFields());
        $this->assertEquals('/my-product', $document->getUrl());
        $this->assertEquals(ProductDocument::AVAILABILITY_AVAILABLE, $document->getAvailability());
        $this->assertEquals(5, $document->getProductTypeId());
        $this->assertEquals(4, $document->getAverageRating());
        $this->assertEquals(8, $document->getSortOrder());
        $this->assertEquals($attributes, $document->getAttributes());
        $this->assertEquals(12, $document->getThumbnailImageId());
        $this->assertEquals('product.png', $document->getThumbnailImagePath());
        $this->assertEquals(date('c', $dateAdded), $document->getDateCreated());
        $this->assertEquals(date('c', $dateModified), $document->getDateUpdated());
        $this->assertEquals(date('c', $dateImported), $document->getDateImported());
    }

    public function testMapFromDocument()
    {
        $dateImported = time() - 1000;
        $dateAdded = time() - 2000;
        $dateModified = time();

        $taxZonePrices = array(
            'tax_zone_id_4' => 330.09,
            'tax_zone_id_5' => 882.23,
        );

        $document = new ProductDocument();
        $document
            ->setId(33)
            ->setName('A Product')
            ->setSku('PROD')
            ->setUpc('987654321')
            ->setDescription('Description here')
            ->setKeywords(array('foo','bar'))
            ->setPrice(112.3)
            ->setCostPrice(242.89)
            ->setRetailPrice(187.20)
            ->setSalePrice(723.27)
            ->setCalculatedPrice(443.34)
            ->setTaxZonePrices($taxZonePrices)
            ->setHidePrice(false)
            ->setTaxClassId(4)
            ->setBrandId(5)
            ->setBrandName('Brand Name')
            ->setCategoryIds(array(12,93,2))
            ->setInventoryLevel(320)
            ->setLowInventoryLevel(13)
            ->setInventoryTracking(ProductDocument::INVENTORY_TRACKING_OPTIONS)
            ->setQuantitySold(109)
            ->setCondition(ProductDocument::CONDITION_REFURBISHED)
            ->setIsVisible(false)
            ->setIsFeatured(true)
            ->setIsConfigurable(true)
            ->setIsDigital(false)
            ->setHasFreeShipping(true)
            ->setHasEventDate(true)
            ->setHasConfigurableFields(false)
            ->setUrl('/some-url')
            ->setAvailability(ProductDocument::AVAILABILITY_DISABLED)
            ->setProductTypeId(0)
            ->setAverageRating(5.82)
            ->setSortOrder(2)
            ->setAttributes(array(array('id' => 2, 'name' => 'Style')))
            ->setThumbnailImageId(39)
            ->setThumbnailImagePath('prod.jpg')
            ->setDateCreated(date('c', $dateAdded))
            ->setDateUpdated(date('c', $dateModified))
            ->setDateImported(date('c', $dateImported));

        $productData = $this->mapFromDocument($document);

        $expectedData = array(
            'productid'             => 33,
            'prodname'              => 'A Product',
            'prodcode'              => 'PROD',
            'upc'                   => '987654321',
            'proddesc'              => 'Description here',
            'prodsearchkeywords'    => 'foo,bar',
            'product_type_id'       => 0,

            'prodprice'             => 112.3,
            'prodcalculatedprice'   => 443.34,
            'prodretailprice'       => 187.20,
            'prodsaleprice'         => 723.27,
            'prodhideprice'         => 0,
            'tax_class_id'          => 4,
            'tax_zone_prices'       => $taxZonePrices,

            'prodbrandid'           => 5,
            'brandname'             => 'Brand Name',

            'prodcatids'            => '12,93,2',

            'prodcurrentinv'        => 320,
            'prodlowinv'            => 13,
            'currentinv'            => 320,
            'prodinvtrack'          => 2,
            'prodnumsold'           => 109,

            'prodfeatured'          => 1,
            'prodvisible'           => 0,
            'prodfreeshipping'      => 1,

            'prodconfigfields'      => '',
            'prodeventdaterequired' => 1,

            'prodtype'              => PT_PHYSICAL,

            'imageprodid'           => 33,
            'imageid'               => 39,
            'imagefile'             => 'prod.jpg',

            'url'                   => '/some-url',
            'prodpreorder'          => 0,
            'prodallowpurchases'    => 0,

            'prodavgrating'         => 6,
            'average_rating'        => 6,

            'prodsortorder'         => 2,
            'prodcondition'         => 'Refurbished',

            'proddateadded'         => $dateAdded,
            'prodlastmodified'      => $dateModified,
            'last_import'           => $dateImported,
        );

        $this->assertEquals($expectedData, $productData);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Document is not an instance of ProductDocument.
     */
    public function testMapFromDocumentThrowsExceptionForNonProductDocument()
    {
        $document = $this->getMock('\Bigcommerce\SearchClient\Document\AbstractDocument');

        $mapper = new ProductDocumentMapper();
        $mapper->mapFromDocument($document);
    }

    private function assertFieldMappedToDocument($field, $method, $valueMap)
    {
        foreach ($valueMap as $mapDetails) {
            $fieldValue = $mapDetails['field'];
            $expectedValue = $mapDetails['document'];

            $data = array(
                $field => $fieldValue,
            );

            $document = $this->mapToDocument($data);
            $this->assertEquals($expectedValue, $document->$method(), "failed asserting that field $field with value '$fieldValue' was mapped to '$expectedValue' using method $method");
        }
    }

    private function assertFieldMappedFromDocument($field, $method, $valueMap)
    {
        foreach ($valueMap as $mapDetails) {
            $expectedValue = $mapDetails['field'];
            $documentValue = $mapDetails['document'];

            $document = new ProductDocument();
            $document->$method($documentValue);

            $data = $this->mapFromDocument($document);
            $this->assertEquals($expectedValue, $data[$field], "failed asserting that document method $method with value '$documentValue' was mapped to '$expectedValue' in field $field");
        }
    }

    public function testMapProductTypes()
    {
        $valueMap = array(
            array('field' => PT_PHYSICAL, 'document' => false),
            array('field' => PT_DIGITAL, 'document' => true),
        );

        $this->assertFieldMappedToDocument('prodtype', 'getIsDigital', $valueMap);
        $this->assertFieldMappedFromDocument('prodtype', 'setIsDigital', $valueMap);
    }

    public function testMapAvailability()
    {
        $valueMap = array(
            array('field' => '1', 'document' => ProductDocument::AVAILABILITY_PREORDER),
        );

        $this->assertFieldMappedToDocument('prodpreorder', 'getAvailability', $valueMap);
        $this->assertFieldMappedFromDocument('prodpreorder', 'setAvailability', $valueMap);

        $valueMap = array(
            array('field' => '0', 'document' => ProductDocument::AVAILABILITY_DISABLED),
            array('field' => '1', 'document' => ProductDocument::AVAILABILITY_AVAILABLE),
        );

        $this->assertFieldMappedToDocument('prodallowpurchases', 'getAvailability', $valueMap);
        $this->assertFieldMappedFromDocument('prodallowpurchases', 'setAvailability', $valueMap);
    }

    public function testMapInventoryTracking()
    {
        $valueMap = array(
            array('field' => '0', 'document' => ProductDocument::INVENTORY_TRACKING_NONE),
            array('field' => '1', 'document' => ProductDocument::INVENTORY_TRACKING_PRODUCT),
            array('field' => '2', 'document' => ProductDocument::INVENTORY_TRACKING_OPTIONS),
        );

        $this->assertFieldMappedToDocument('prodinvtrack', 'getInventoryTracking', $valueMap);
        $this->assertFieldMappedFromDocument('prodinvtrack', 'setInventoryTracking', $valueMap);
    }

    public function testInvalidTrackingMapsToNone()
    {
        $data = array(
            'prodinvtrack' => 'foo',
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(ProductDocument::INVENTORY_TRACKING_NONE, $document->getInventoryTracking());
    }

    public function testMapCondition()
    {
        $valueMap = array(
            array('field' => 'New', 'document' => ProductDocument::CONDITION_NEW),
            array('field' => 'Used', 'document' => ProductDocument::CONDITION_USED),
            array('field' => 'Refurbished', 'document' => ProductDocument::CONDITION_REFURBISHED),
        );

        $this->assertFieldMappedToDocument('prodcondition', 'getCondition', $valueMap);
        $this->assertFieldMappedFromDocument('prodcondition', 'setCondition', $valueMap);
    }

    public function testInvalidConditionMapsToNew()
    {
        $data = array(
            'prodcondition' => 'foo',
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(ProductDocument::CONDITION_NEW, $document->getCondition());
    }

    public function testMapAverageRatingDoesntDivideByZero()
    {
        $data = array(
            'prodratingtotal'   => 20,
            'prodnumratings'    => 0,
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(0, $document->getAverageRating());
    }

    public function testAverageRatingOnlyCalculatedIfMissingFromData()
    {
        $data = array(
            'average_rating'    => 12.4,
            'prodratingtotal'   => 20,
            'prodnumratings'    => 5,
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(12.4, $document->getAverageRating());
    }

    public function isConfigurableDataProvider()
    {
       return array(
            array('prodconfigfields', 'foobar', '1'),
            array('prodeventdaterequired', '1', 1),
            array('product_type_id', 4, 4),
        );
    }

    /**
     * @dataProvider isConfigurableDataProvider
     */
    public function testMapIsConfigurable($field, $value, $mapFromValue)
    {
        $data = array($field => $value);
        $document = $this->mapToDocument($data);

        $this->assertTrue($document->getIsConfigurable());

        $data = $this->mapFromDocument($document);
        $this->assertEquals($mapFromValue, $data[$field]);
    }

    public function isNotConfigurableDataProvider()
    {
       return array(
            array('prodconfigfields', ''),
            array('prodeventdaterequired', '0'),
            array('product_type_id', null),
        );
    }

    /**
     * @dataProvider isNotConfigurableDataProvider
     */
    public function testProductIsNotMarkedAsNotConfigurableForSingleConfigurableField($field, $value)
    {
        $data = array($field => $value);
        $document = $this->mapToDocument($data);

        $this->assertNull($document->getIsConfigurable());
    }

    public function testMapNotIsConfigurableIfAllConfigurableFieldsAreEmpty()
    {
        $data = array(
            'prodconfigfields'      => '',
            'prodeventdaterequired' => '0',
            'product_type_id'       => null,
        );

        $document = $this->mapToDocument($data);

        $this->assertFalse($document->getIsConfigurable());

        $data = $this->mapFromDocument($document);
        $this->assertEquals('', $data['prodconfigfields']);
        $this->assertEquals(0, $data['prodeventdaterequired']);
    }

    public function dateDataProvider()
    {
        return array(
            array('last_import', 'getDateImported', 'setDateImported'),
            array('proddateadded', 'getDateCreated', 'setDateCreated'),
            array('prodlastmodified', 'getDateUpdated', 'setDateUpdated'),
        );
    }

    /**
     * @dataProvider dateDataProvider
     */
    public function testMapImportDate()
    {
        $time = time();

        $valueMap = array(
            array('field' => '0', 'document' => null),
            array('field' => '', 'document' => null),
            array('field' => $time, 'document' => date('c', $time)),
        );

        $this->assertFieldMappedToDocument('last_import', 'getDateImported', $valueMap);

        $valueMap = array(
            array('field' => '0', 'document' => null),
            array('field' => $time, 'document' => date('c', $time)),
        );

        $this->assertFieldMappedFromDocument('last_import', 'setDateImported', $valueMap);
    }

    public function testMapDateModifiedUsesDateAddedIfEmpty()
    {
        $time = time();

        $data = array(
            'prodlastmodified'  => '0',
            'proddateadded'     => $time,
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(date('c', $time), $document->getDateUpdated());
    }

    public function testNullProductTypeIdConvertedToZero()
    {
        $data = array(
            'product_type_id'  => null,
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals(0, $document->getProductTypeId());
    }

    public function nullDefaultStringColumns()
    {
        return array(
            array('prodcode'),
            array('proddesc'),
            array('prodsearchkeywords'),
            array('upc'),
            array('brandname'),
            array('url'),
        );
    }

    /**
     * @dataProvider nullDefaultStringColumns
     */
    public function testNullDefaultStringColumnsConvertedToEmptyString($column)
    {
        $data = array(
            $column  => null,
        );

        $document = $this->mapToDocument($data);

        $this->assertEquals('', $document->getUpc());
    }
}
