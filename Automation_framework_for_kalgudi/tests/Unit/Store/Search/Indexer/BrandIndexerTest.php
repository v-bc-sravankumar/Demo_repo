<?php

namespace Unit\Store\Search\Indexer;

require_once __DIR__ . '/IndexerTestCase.php';

use Store_Brand;

class BrandIndexerTest extends IndexerTestCase
{
    protected $indexerName = 'Brand';

    protected function getIdFromData($data)
    {
        return $data->getId();
    }

    protected function getData()
    {
        $brand1 = new Store_Brand();
        $brand1
            ->setId(5)
            ->setName('My Brand')
            ->setPageTitle('Brand Page Title')
            ->setSearchKeywords('key,word')
            ->setImageFileName('brand.jpg');

        $brand2 = new Store_Brand();
        $brand2
            ->setId(8)
            ->setName('Another Brand')
            ->setPageTitle('Something')
            ->setSearchKeywords('foo,bar')
            ->setImageFileName('image.png');

        return array(
            $brand1,
            $brand2,
        );
    }
}
