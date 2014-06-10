<?php

namespace Unit\Store\Search\Indexer;

require_once __DIR__ . '/IndexerTestCase.php';

use Content\Blog\Post;

class PostIndexerTest extends IndexerTestCAse
{
    protected $indexerName = 'Post';

    protected function getIdFromData($data)
    {
        return $data->getId();
    }

    protected function getData()
    {
        $post1 = new Post();
        $post1
            ->setId(7)
            ->setTitle('Post')
            ->setBody('Blog content')
            ->setAuthor('Jimmy')
            ->setSearchKeywords('hello,world')
            ->setVisible(true)
            ->setCustomUrl('/post')
            ->setPublishedDate(time());

        $post2 = new Post();
        $post2
            ->setId(14)
            ->setTitle('Blog Post')
            ->setBody('Content here')
            ->setAuthor('Mr Hat')
            ->setSearchKeywords('foo,bar')
            ->setVisible(false)
            ->setCustomUrl('/blog-post')
            ->setPublishedDate(time());

        return array(
            $post1,
            $post2,
        );
    }
}
