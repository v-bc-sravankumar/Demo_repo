<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Bigcommerce\SearchClient\Document\PostDocument;
use Store\Search\Provider\Local\DocumentMapper\PostDocumentMapper;
use Content\Blog\Post;

class PostDocumentMapperTest extends DocumentMapperTestCase
{
    public function testMapToDocument()
    {
        $time = time();

        $post = new Post();
        $post
            ->setId(12)
            ->setTitle('Blog Post')
            ->setBody('Blog content')
            ->setAuthor('Bob')
            ->setSearchKeywords('hello,world')
            ->setVisible(true)
            ->setCustomUrl('/my-blog')
            ->setPublishedDate($time);

        $mapper = new PostDocumentMapper();
        $document = $mapper->mapToDocument($post);

        $this->validateDocument($document);

        $this->assertEquals(12, $document->getId());
        $this->assertEquals('Blog Post', $document->getTitle());
        $this->assertEquals('Blog content', $document->getContent());
        $this->assertEquals('Bob', $document->getAuthor());
        $this->assertEquals(array('hello', 'world'), $document->getKeywords());
        $this->assertTrue($document->getIsVisible());
        $this->assertEquals(date('c', $time), $document->getDatePublished());
    }

    public function testMapToDocumentWithNullContentIsEmptyString()
    {
        $time = time();

        $post = new Post();
        $post
            ->setId(14)
            ->setTitle('Blog Post')
            ->setAuthor('Bob')
            ->setSearchKeywords('hello,world')
            ->setVisible(true)
            ->setCustomUrl('/my-blog')
            ->setPublishedDate($time);

        $mapper = new PostDocumentMapper();
        $document = $mapper->mapToDocument($post);

        $this->validateDocument($document);

        $this->assertEquals(14, $document->getId());
        $this->assertEquals('Blog Post', $document->getTitle());
        $this->assertEquals('', $document->getContent());
        $this->assertEquals('Bob', $document->getAuthor());
        $this->assertEquals(array('hello', 'world'), $document->getKeywords());
        $this->assertTrue($document->getIsVisible());
        $this->assertEquals(date('c', $time), $document->getDatePublished());
    }

    public function testMapFromDocument()
    {
        $time = time();

        $document = new PostDocument();
        $document
            ->setId(15)
            ->setTitle('An Article')
            ->setContent('Article content')
            ->setAuthor('Jimbo')
            ->setKeywords(array('key', 'word'))
            ->setIsVisible(false)
            ->setDatePublished(date('c', $time));

        $mapper = new PostDocumentMapper();
        $post = $mapper->mapFromDocument($document);

        $this->assertEquals(15, $post->getId());
        $this->assertEquals('An Article', $post->getTitle());
        $this->assertEquals('Article content', $post->getBody());
        $this->assertEquals('Jimbo', $post->getAuthor());
        $this->assertEquals('key,word', $post->getSearchKeywords());
        $this->assertFalse($post->getVisible());
        $this->assertEquals($time, $post->getPublishedDate()->format('U'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Document is not an instance of PostDocument.
     */
    public function testMapFromDocumentThrowsExceptionForNonPostDocument()
    {
        $document = $this->getMock('\Bigcommerce\SearchClient\Document\AbstractDocument');

        $mapper = new PostDocumentMapper();
        $mapper->mapFromDocument($document);
    }
}
