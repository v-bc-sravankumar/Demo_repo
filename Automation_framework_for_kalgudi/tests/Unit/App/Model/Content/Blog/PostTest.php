<?php

namespace Unit\App\Model\Content\Blog;

use Content\Blog\Post;

class PostTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetSearchKeywords()
    {
        $post = new Post();
        $this->assertEquals($post, $post->setSearchKeywords('foo,bar'));
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }

    public function testSetSearchKeywordsFiltersEmptyKeywords()
    {
        $post = new Post();
        $post->setSearchKeywords('foo,,bar, ');
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }

    public function testSetSearchKeywordsTwiceReplacesKeywords()
    {
        $post = new Post();
        $post->setSearchKeywords('foo,bar');
        $post->setSearchKeywords('hello,world');
        $this->assertEquals('hello,world', $post->getSearchKeywords());
    }

    public function testSetSearchKeywordsTrimsKeywords()
    {
        $post = new Post();
        $post->setSearchKeywords(' foo ,  bar  ');
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }

    public function testSetSearchKeywordsRemovesDuplicateKeywords()
    {
        $post = new Post();
        $post->setSearchKeywords('foo,bar,FOO,Bar,Foo');
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }

    public function testSetSearchKeywordsSetTags()
    {
        $post = new Post();
        $post->setSearchKeywords('foo,bar');
        $this->assertEquals(array('foo', 'bar'), $post->getTags());
    }

    public function testSetGetTags()
    {
        $post = new Post();
        $this->assertEquals($post, $post->setTags(array('foo', 'bar')));
        $this->assertEquals(array('foo', 'bar'), $post->getTags());
    }

    public function testSetTagsSetsSearchKeywords()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }

    public function testSetTagsTwiceReplacesTags()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));
        $post->setTags(array('hello', 'world'));
        $this->assertEquals(array('hello', 'world'), $post->getTags());
    }

    public function testSetTagsTrimsFiltersAndRemovesDuplicates()
    {
        $post = new Post();
        $post->setTags(array(' foo  ', ' bar  ', '', ' ', 'Foo', 'BAR'));
        $this->assertEquals('foo,bar', $post->getSearchKeywords());
    }
}
