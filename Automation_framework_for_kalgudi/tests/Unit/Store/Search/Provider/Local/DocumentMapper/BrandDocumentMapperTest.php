<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Bigcommerce\SearchClient\Document\BrandDocument;
use Store\Search\Provider\Local\DocumentMapper\BrandDocumentMapper;
use Store_Brand;

class BrandDocumentMapperTest extends DocumentMapperTestCase
{
    public function testMapToDocument()
    {
        $brand = new Store_Brand();
        $brand
            ->setId(4)
            ->setName('My Brand')
            ->setPageTitle('Brand Page Title')
            ->setSearchKeywords('hello,world')
            ->setImageFileName('brand.jpg');

        $mapper = new BrandDocumentMapper();
        $document = $mapper->mapToDocument($brand);

        $this->validateDocument($document);

        $this->assertEquals(4, $document->getId());
        $this->assertEquals('My Brand', $document->getName());
        $this->assertEquals('Brand Page Title', $document->getPageTitle());
        $this->assertEquals(array('hello', 'world'), $document->getKeywords());
        $this->assertEquals('brand.jpg', $document->getImageFile());
    }

    public function testMapToDocumentWithOnlyIdAndName()
    {
        $brand = new Store_Brand();
        $brand
            ->setId(5)
            ->setName('My Brand');

        $mapper = new BrandDocumentMapper();
        $document = $mapper->mapToDocument($brand);

        $this->validateDocument($document);

        $this->assertEquals(5, $document->getId());
        $this->assertEquals('My Brand', $document->getName());
        $this->assertEquals('', $document->getPageTitle());
        $this->assertEquals(array(), $document->getKeywords());
        $this->assertEquals('', $document->getImageFile());
    }

    public function testMapFromDocument()
    {
        $document = new BrandDocument();
        $document
            ->setId(6)
            ->setName('A Brand')
            ->setPageTitle('Page Title')
            ->setKeywords(array('foo', 'bar'))
            ->setImageFile('brand.gif');

        $mapper = new BrandDocumentMapper();
        $brand = $mapper->mapFromDocument($document);

        $this->assertEquals(6, $brand->getId());
        $this->assertEquals('A Brand', $brand->getName());
        $this->assertEquals('Page Title', $brand->getPageTitle());
        $this->assertEquals('foo,bar', $brand->getSearchKeywords());
        $this->assertEquals('brand.gif', $brand->getImageFileName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Document is not an instance of BrandDocument.
     */
    public function testMapFromDocumentThrowsExceptionForNonBrandDocument()
    {
        $document = $this->getMock('\Bigcommerce\SearchClient\Document\AbstractDocument');

        $mapper = new BrandDocumentMapper();
        $mapper->mapFromDocument($document);
    }
}
