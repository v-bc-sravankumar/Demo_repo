<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Bigcommerce\SearchClient\Document\PageDocument;
use Content\Page;
use Store_CustomUrl;
use Store\Search\Provider\Local\DocumentMapper\PageDocumentMapper;

class PageDocumentMapperTest extends DocumentMapperTestCase
{
    public function testMapToDocument()
    {
        $pageData = array(
            'pageid'                => '9',
            'pagetitle'             => 'My Page',
            'pagecontent'           => 'Some content',
            'pagetype'              => '0',
            'pagelink'              => 'http://google.com',
            'pagedesc'              => 'Meta description',
            'pagemetatitle'         => 'Page Title',
            'pagestatus'            => '1',
            'pagecustomersonly'     => '0',
            'pagesearchkeywords'    => 'key,word',
            'url'                   => '/my-page',
        );

        $mapper = new PageDocumentMapper();
        $document = $mapper->mapToDocument($pageData);

        $this->validateDocument($document);

        $this->assertEquals(9, $document->getId());
        $this->assertEquals('My Page', $document->getName());
        $this->assertEquals('Some content', $document->getContent());
        $this->assertEquals('content', $document->getPageType());
        $this->assertEquals('http://google.com', $document->getLinkUrl());
        $this->assertEquals('Meta description', $document->getMetaDescription());
        $this->assertTrue($document->getIsVisible());
        $this->assertFalse($document->getCustomersOnly());
        $this->assertEquals(array('key', 'word'), $document->getKeywords());
        $this->assertEquals('/my-page', $document->getUrl());
    }

    public function testMapToDocumentForPageInstanceWithJoinedCustomUrl()
    {
        $url = new Store_CustomUrl();
        $url
            ->setId(5)
            ->setUrl('/foobar');

        $iterator = $this
            ->getMockBuilder('\DataModel_QueryIterator')
            ->disableOriginalConstructor()
            ->getMock();

        $iterator
            ->expects($this->once())
            ->method('hydrateAs')
            ->with($this->isInstanceOf('\Store_CustomUrl'))
            ->will($this->returnValue($url));

        $page = new Page();
        $page
            ->setId(55)
            ->setTitle('Page Title')
            ->setMetaTitle('Meta Title')
            ->setLink('http://www.bigcommerce.com')
            ->setContent('My content')
            ->setStatus('1')
            ->setSearchKeywords('search,keywords')
            ->setDescription('meta description')
            ->setType('0')
            ->setMetaKeywords('meta,keywords')
            ->setCustomersOnly('1');

        $mapper = new PageDocumentMapper();
        $mapper->setIterator($iterator);
        $document = $mapper->mapToDocument($page);

        $this->validateDocument($document);

        $this->assertEquals(55, $document->getId());
        $this->assertEquals('Page Title', $document->getName());
        $this->assertEquals('My content', $document->getContent());
        $this->assertEquals('content', $document->getPageType());
        $this->assertEquals('http://www.bigcommerce.com', $document->getLinkUrl());
        $this->assertEquals('meta description', $document->getMetaDescription());
        $this->assertTrue($document->getIsVisible());
        $this->assertTrue($document->getCustomersOnly());
        $this->assertEquals(array('search', 'keywords'), $document->getKeywords());
        $this->assertEquals('/foobar', $document->getUrl());
    }

    public function testMapToDocumentForPageInstanceWithSetCustomUrl()
    {
        $page = new Page();
        $page
            ->setId(44)
            ->setTitle('Title')
            ->setMetaTitle('Meta')
            ->setLink('http://www.example.com')
            ->setContent('Some content')
            ->setStatus('0')
            ->setSearchKeywords('search,key,word')
            ->setDescription('description')
            ->setType('1')
            ->setMetaKeywords('meta,key,word')
            ->setCustomersOnly('0')
            ->setCustomUrl('/customurl');

        $mapper = new PageDocumentMapper();
        $document = $mapper->mapToDocument($page);

        $this->validateDocument($document);

        $this->assertEquals(44, $document->getId());
        $this->assertEquals('Title', $document->getName());
        $this->assertEquals('Some content', $document->getContent());
        $this->assertEquals('link', $document->getPageType());
        $this->assertEquals('http://www.example.com', $document->getLinkUrl());
        $this->assertEquals('description', $document->getMetaDescription());
        $this->assertFalse($document->getIsVisible());
        $this->assertFalse($document->getCustomersOnly());
        $this->assertEquals(array('search', 'key', 'word'), $document->getKeywords());
        $this->assertEquals('/customurl', $document->getUrl());
    }

    public function testMapToDocumentWithNullContentAndDescription()
    {
        $page = new Page();
        $page
            ->setId(44)
            ->setTitle('Title')
            ->setMetaTitle('Meta')
            ->setLink('http://www.example.com')
            ->setStatus('0')
            ->setSearchKeywords('search,key,word')
            ->setType('1')
            ->setMetaKeywords('meta,key,word')
            ->setCustomersOnly('0')
            ->setCustomUrl('/customurl');

        $mapper = new PageDocumentMapper();
        $document = $mapper->mapToDocument($page);

        $this->validateDocument($document);

        $this->assertEquals(44, $document->getId());
        $this->assertEquals('Title', $document->getName());
        $this->assertEquals('', $document->getContent());
        $this->assertEquals('link', $document->getPageType());
        $this->assertEquals('http://www.example.com', $document->getLinkUrl());
        $this->assertEquals('', $document->getMetaDescription());
        $this->assertFalse($document->getIsVisible());
        $this->assertFalse($document->getCustomersOnly());
        $this->assertEquals(array('search', 'key', 'word'), $document->getKeywords());
        $this->assertEquals('/customurl', $document->getUrl());
    }

    public function testMapFromDocument()
    {
        $document = new PageDocument();
        $document
            ->setId(7)
            ->setName('A Page')
            ->setContent('Content here')
            ->setPageType(PageDocument::PAGE_TYPE_BLOG)
            ->setLinkUrl('http://www.bigcommerce.com')
            ->setMetaDescription('Meta content')
            ->setPageTitle('Title')
            ->setIsVisible(false)
            ->setCustomersOnly(true)
            ->setKeywords(array('hello', 'world'))
            ->setUrl('/a-page');

        $mapper = new PageDocumentMapper();
        $pageData = $mapper->mapFromDocument($document);

        $this->assertEquals(7, $pageData['pageid']);
        $this->assertEquals('A Page', $pageData['pagetitle']);
        $this->assertEquals('Content here', $pageData['pagecontent']);
        $this->assertEquals(4, $pageData['pagetype']);
        $this->assertEquals('http://www.bigcommerce.com', $pageData['pagelink']);
        $this->assertEquals('Meta content', $pageData['pagedesc']);
        $this->assertEquals('Title', $pageData['pagemetatitle']);
        $this->assertEquals(0, $pageData['pagestatus']);
        $this->assertEquals(1, $pageData['pagecustomersonly']);
        $this->assertEquals('hello,world', $pageData['pagesearchkeywords']);
        $this->assertEquals('/a-page', $pageData['url']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Document is not an instance of PageDocument.
     */
    public function testMapFromDocumentThrowsExceptionForNonPageDocument()
    {
        $document = $this->getMock('\Bigcommerce\SearchClient\Document\AbstractDocument');

        $mapper = new PageDocumentMapper();
        $mapper->mapFromDocument($document);
    }
}
