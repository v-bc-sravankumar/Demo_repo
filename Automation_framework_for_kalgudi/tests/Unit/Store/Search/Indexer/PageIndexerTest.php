<?php

namespace Unit\Store\Search\Indexer;

require_once __DIR__ . '/IndexerTestCase.php';

use Content\Page;

class PageIndexerTest extends IndexerTestCase
{
    protected $indexerName = 'Page';

    protected function getIdFromData($data)
    {
        return $data->getId();
    }

    protected function getData()
    {
        $page1 = new Page();
        $page1
            ->setId(33)
            ->setTitle('Page Title')
            ->setMetaTitle('Meta Title')
            ->setLink('http://www.bing.com')
            ->setContent('My content')
            ->setStatus('1')
            ->setSearchKeywords('hello,world')
            ->setDescription('meta description')
            ->setType('0')
            ->setCustomersOnly('1')
            ->setCustomUrl('/foo');

        $page2 = new Page();
        $page2
            ->setId(22)
            ->setTitle('Some Title')
            ->setMetaTitle('Meta Title')
            ->setLink('http://www.google.com')
            ->setContent('My content')
            ->setStatus('0')
            ->setSearchKeywords('foo,bar')
            ->setDescription('description')
            ->setType('2')
            ->setCustomersOnly('0')
            ->setCustomUrl('/bar');

        return array(
            $page1,
            $page2,
        );
    }
}
