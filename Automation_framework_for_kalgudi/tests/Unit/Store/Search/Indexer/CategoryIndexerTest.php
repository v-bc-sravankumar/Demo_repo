<?php

namespace Unit\Store\Search\Indexer;

require_once __DIR__ . '/IndexerTestCase.php';

class CategoryIndexerTest extends IndexerTestCase
{
    protected $indexerName = 'Category';

    protected function getIdFromData($data)
    {
        return array_key_exists('id', $data) ? $data['id'] : $data['categoryid'];
    }

    protected function getData()
    {
        return array(
            array(
                'id'                => 8,
                'name'              => 'A Category',
                'description'       => 'Category Description',
                'page_title'        => 'Page Title',
                'parent_id'         => 7,
                'is_visible'        => true,
                'search_keywords'   => 'hello,world',
                'image_file'        => 'category.png',
                'url'               => '/a-category',
            ),
            array(
                'categoryid'        => '5',
                'catname'           => 'My Category',
                'catdesc'           => 'Description here',
                'catpagetitle'      => 'Page Title',
                'catparentid'       => '4',
                'catvisible'        => '1',
                'catsearchkeywords' => 'foo,bar',
                'catimagefile'      => 'category.gif',
                'url'               => '/my-category',
            ),
        );
    }
}
