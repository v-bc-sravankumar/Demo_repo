<?php

namespace Integration\Store;

use Store_News;
use PHPUnit_Framework_TestCase;

class NewsTest extends PHPUnit_Framework_TestCase
{
    public function testSetMetaDescription()
    {
        $news = new Store_News();
        $this->assertEquals($news, $news->setMetaDescription('foo'));
    }

    public function testGetMetaDescription()
    {
        $news = new Store_News();
        $this->assertNull($news->getMetaDescription());

        $news->setMetaDescription('foo');
        $this->assertEquals('foo', $news->getMetaDescription());
    }

    public function testSaveSucceeds()
    {
        $time = time();

        $news = new Store_News();
        $news
            ->setTitle('my post')
            ->setContent('my news content')
            ->setSearchKeywords('here,there')
            ->setMetaDescription('meta description')
            ->setVisible(true)
            ->setCreationDate($time);

        if (!$news->save()) {
            $this->fail('Failed to save news post.');
        }

        $id = $news->getId();

        $news = new Store_News();
        if (!$news->load($id)) {
            $this->fail('Failed to load news post: ' . $id);
        }

        $this->assertEquals('my post', $news->getTitle());
        $this->assertEquals('my news content', $news->getContent());
        $this->assertEquals('here,there', $news->getSearchKeywords());
        $this->assertEquals('meta description', $news->getMetaDescription());
        $this->assertEquals(true, $news->getVisible());
        $this->assertEquals($time, $news->getCreationDate());

        $news->delete();
    }
}
