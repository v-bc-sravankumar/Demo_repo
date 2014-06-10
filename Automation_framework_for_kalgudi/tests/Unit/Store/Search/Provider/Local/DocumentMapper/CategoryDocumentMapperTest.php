<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Bigcommerce\SearchClient\Document\CategoryDocument;
use Store\Search\Provider\Local\DocumentMapper\CategoryDocumentMapper;

class CategoryDocumentMapperTest extends DocumentMapperTestCase
{
    public function testMapToDocumentForDbFormat()
    {
        $categoryData = array(
            'categoryid'        => '5',
            'catname'           => 'My Category',
            'catdesc'           => 'Description here',
            'catpagetitle'      => 'Page Title',
            'catparentid'       => '4',
            'catvisible'        => '1',
            'catsearchkeywords' => 'foo,bar',
            'catimagefile'      => 'category.png',
            'url'               => '/my-category',
        );

        $mapper = new CategoryDocumentMapper();
        $document = $mapper->mapToDocument($categoryData);

        $this->validateDocument($document);

        $this->assertEquals(5, $document->getId());
        $this->assertEquals('My Category', $document->getName());
        $this->assertEquals('Description here', $document->getDescription());
        $this->assertEquals('Page Title', $document->getPageTitle());
        $this->assertEquals(4, $document->getParentId());
        $this->assertTrue($document->getIsVisible());
        $this->assertEquals(array('foo', 'bar'), $document->getKeywords());
        $this->assertEquals('category.png', $document->getImageFile());
        $this->assertEquals('/my-category', $document->getUrl());
    }

    public function testMapToDocumentForApiFormat()
    {
        $categoryData = array(
            'id'                => 8,
            'name'              => 'A Category',
            'description'       => 'Category Description',
            'page_title'        => 'Page Title',
            'parent_id'         => 7,
            'is_visible'        => true,
            'search_keywords'   => 'hello,world',
            'image_file'        => 'category.jpg',
            'url'               => '/a-category',
        );

        $mapper = new CategoryDocumentMapper();
        $document = $mapper->mapToDocument($categoryData);

        $this->validateDocument($document);

        $this->assertEquals(8, $document->getId());
        $this->assertEquals('A Category', $document->getName());
        $this->assertEquals('Category Description', $document->getDescription());
        $this->assertEquals('Page Title', $document->getPageTitle());
        $this->assertEquals(7, $document->getParentId());
        $this->assertTrue($document->getIsVisible());
        $this->assertEquals(array('hello', 'world'), $document->getKeywords());
        $this->assertEquals('category.jpg', $document->getImageFile());
        $this->assertEquals('/a-category', $document->getUrl());
    }

    public function testMapFromDocument()
    {
        $document = new CategoryDocument();
        $document
            ->setId(3)
            ->setName('A Category')
            ->setDescription('Category description')
            ->setPageTitle('Page Title')
            ->setParentId(0)
            ->setIsVisible(false)
            ->setKeywords(array('hello', 'world'))
            ->setImageFile('category.gif')
            ->setUrl('/a-category');

        $mapper = new CategoryDocumentMapper();
        $categoryData = $mapper->mapFromDocument($document);

        $this->assertEquals(3, $categoryData['categoryid']);
        $this->assertEquals('A Category', $categoryData['catname']);
        $this->assertEquals('Category description', $categoryData['catdesc']);
        $this->assertEquals('Page Title', $categoryData['catpagetitle']);
        $this->assertEquals(0, $categoryData['catparentid']);
        $this->assertEquals(0, $categoryData['catvisible']);
        $this->assertEquals('hello,world', $categoryData['catsearchkeywords']);
        $this->assertEquals('category.gif', $categoryData['catimagefile']);
        $this->assertEquals('/a-category', $categoryData['url']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Document is not an instance of CategoryDocument.
     */
    public function testMapFromDocumentThrowsExceptionForNonCategoryDocument()
    {
        $document = $this->getMock('\Bigcommerce\SearchClient\Document\AbstractDocument');

        $mapper = new CategoryDocumentMapper();
        $mapper->mapFromDocument($document);
    }

    public function testMapToDocumentNullUrlConvertedToEmptyString()
    {
        $categoryData = array(
            'id'                => 8,
            'name'              => 'A Category',
            'description'       => 'Category Description',
            'page_title'        => 'Page Title',
            'parent_id'         => 7,
            'is_visible'        => true,
            'search_keywords'   => 'hello,world',
            'image_file'        => 'category.jpg',
            'url'               => null,
        );

        $mapper = new CategoryDocumentMapper();
        $document = $mapper->mapToDocument($categoryData);

        $this->validateDocument($document);

        $this->assertEquals('', $document->getUrl());
    }
}
