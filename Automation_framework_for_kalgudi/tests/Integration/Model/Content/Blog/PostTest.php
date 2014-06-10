<?php

namespace Integration\Model\Content\Blog;

use Content\Blog\Post;

class PostTest extends \Interspire_IntegrationTest
{
    private $db;

    private function getTags($id)
    {
         $result = $this->fixtures->db->Query('SELECT * FROM news_tags WHERE newsid = ' . (int)$id);

        $tags = array();
        while ($row = $this->fixtures->db->Fetch($result)) {
            $tags[] = $row['tag'];
        }

        return $tags;
    }

    private function checkTags($expectedTags, $id)
    {
        $tags = $this->getTags($id);

        sort($expectedTags);
        sort($tags);

        $this->assertEquals($expectedTags, $tags);
    }

    public function testSavePostSavesTags()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));

        if (!$post->save()) {
            $this->fail('Failed to save post');
        }

        $this->checkTags($post->getTags(), $post->getId());

        $post->delete();
    }

    public function testSavePostWithoutTags()
    {
        $post = new Post();
        $post->setTitle('Post');

        if (!$post->save()) {
            $this->fail('Failed to save post');
        }

        $this->checkTags(array(), $post->getId());

        $post->delete();
    }

    public function testSavePostDoesntAffectTagsWhenTagsArentChanged()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));

        if (!$post->save()) {
            $this->fail('Failed to save post');
        }

        $post
            ->setTitle('My Post')
            ->save();

        $this->checkTags($post->getTags(), $post->getId());

        $post->delete();
    }

    public function testUpdatePostSavesUpdatedTags()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));

         if (!$post->save()) {
            $this->fail('Failed to save post');
        }

        $this->checkTags($post->getTags(), $post->getId());

        $post
            ->setTags(array('hello', 'world'))
            ->save();

        $this->checkTags($post->getTags(), $post->getId());

        $post->delete();
    }

    public function testDeletePostRemovesTags()
    {
        $post = new Post();
        $post->setTags(array('foo', 'bar'));

        if (!$post->save()) {
            $this->fail('Failed to save post');
        }

        $post->delete();

        $this->checkTags(array(), $post->getId());
    }

    public function testIsUrlDuplicateIsFalseForNoUrl()
    {
        $post = new Post();
        $this->assertFalse($post->isUrlDuplicate());
    }

    public function testIsUrlDuplicateIsFalseForUniqueUrl()
    {
        $url = 'foo';
        $post = new Post();
        $post->setCustomUrl(Post::generateUniqueCustomUrl($url));

        $this->assertFalse($post->isUrlDuplicate());
    }

    public function testIsUrlDuplicateIsTrueForUniqueUrl()
    {
        $post = new Post();
        $post
            ->setTitle('Test')
            ->setCustomUrl('foo');

        $this->assertTrue($post->save(), 'Failed to save post');

        $anotherPost = new Post();
        $anotherPost->setCustomUrl('foo');

        $this->assertTrue($anotherPost->isUrlDuplicate());

        $post->delete();
    }

    public function testSaveDoesntOverwriteUrlIfCustomUrlInstanceNotSet()
    {
        $post = new Post();
        $post
            ->setTitle('Test')
            ->setCustomUrl('/test-post-url');

        $this->assertTrue($post->save(), 'Failed to save post');

        $id = $post->getId();

        $post = new Post();

        if (!$post->load($id)) {
            $this->fail('Failed to load post');
        }

        $post
            ->setTitle('New Title')
            ->save();

        $this->assertEquals('/test-post-url', $post->getCustomUrl()->getUrl());

        $post->delete();
    }

    public function testSaveDoesntLoadCustomUrlIfInstanceNotSet()
    {
        $post = new TestPost();
        $post->setTitle('Test');

        $this->assertTrue($post->save(), 'Failed to save post');
        $this->assertFalse($post->returnCustomUrl());

        $post->delete();
    }

    public function testCustomUrlDoesntGetSavedTwice()
    {
        $post = new Post();
        $post
            ->setTitle('Post')
            ->setCustomUrl('/test-post-url')
            ->save();

        $id = $post->getId();

        $post = new Post();
        $post->load($id);
        $post
            ->setTitle('New Title')
            ->setCustomUrl('/test-post-url-2')
            ->save();

        $query = \Store_CustomUrl::findByContent(\Store_CustomUrl::TARGET_TYPE_NEWS, $post->getId());
        $this->assertEquals(1, $query->count());

        $post->delete();
    }

    /**
     * @see https://github.com/bigcommerce/bigcommerce/pull/2999
     */
    public function testGetCustomUrlForNewPost()
    {
        $post = new Post();
        $url = $post->getCustomUrl();
        $this->assertInstanceOf('\Store_CustomUrl', $url);
        $this->assertNull($url->getId());
    }
}

class TestPost extends Post
{
    public function returnCustomUrl()
    {
        return $this->customUrl;
    }
}
