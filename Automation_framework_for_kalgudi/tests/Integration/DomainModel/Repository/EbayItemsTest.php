<?php

namespace Integration\DomainModel\Repository;

use Repository\EbayItems;
use Store\Ebay\Item;
use DomainModel\Query;
use PHPUnit_Framework_TestCase;

class EbayItemsTest extends PHPUnit_Framework_TestCase
{
    public function testFindMatching()
    {
        $item = new Item();
        $item
            ->setProductId(5)
            ->setItemId('1234567')
            ->save();

        $repo = new EbayItems();
        $filter = new Query\Filter(array(
            'product_id' => 5,
            'ebay_item_id' => '1234567',
        ));

        $collection = $repo->findMatching($filter, new Query\Pager(), new Query\Sorter());
        $current = $collection->current();

        $this->assertEquals(1, $collection->count());
        $this->assertEquals($item->toArray(), $current);

        $item->delete();
    }

    public function testFindById()
    {
        $item = new Item();
        $item
            ->setProductId(1)
            ->save();

        $repo = new EbayItems();
        $findItem = $repo->findById($item->getId());
        $this->assertEquals($item, $findItem);

        $item->delete();
    }

    public function testFindByItemId()
    {
        $itemId = uniqid();
        $item = new Item();
        $item
            ->setItemId($itemId)
            ->save();

        $repo = new EbayItems();
        $findItem = $repo->findByItemId($itemId);
        $this->assertEquals($item, $findItem);

        $item->delete();
    }
}
