<?php

namespace Integration\Ebay;

use Store\Ebay\Item;
use PHPUnit_Framework_TestCase;

class ItemTest extends PHPUnit_Framework_TestCase
{
    private function createItem()
    {
        $item = new Item();
        $item
            ->setProductId(45)
            ->setItemId('1039')
            ->setTitle('My Ebay Item')
            ->setStartTime('2013-08-27T06:24:09.295Z')
            ->setEndTime('2013-09-03T06:24:09.295Z')
            ->setListingTime(1377584650)
            ->setListingType('FixedPriceItem')
            ->setListingStatus('active')
            ->setCurrentPriceCurrencyCode('USD')
            ->setCurrentPrice(112.00)
            ->setBuyItNowPriceCurrencyCode('AUD')
            ->setBuyItNowPrice(96.88)
            ->setSiteId(1)
            ->setItemUrl('http://www.ebay.com/my-item/12345')
            ->setQuantityRemaining(13)
            ->setBidCount(9);

        return $item;
    }

    public function testSaveItemSucceeds()
    {
        $item = $this->createItem();

        $this->assertTrue($item->save());

        $item->delete();
    }

    public function testLoadItemSucceeds()
    {
        $item = $this->createItem();
        $item->save();
        $id = $item->getId();

        $loadItem = new Item();
        $this->assertTrue($loadItem->load($id));

        $this->assertEquals($item->getProductId(), $loadItem->getProductId());
        $this->assertEquals($item->getItemId(), $loadItem->getItemId());
        $this->assertEquals($item->getTitle(), $loadItem->getTitle());
        $this->assertEquals($item->getStartTime(), $loadItem->getStartTime());
        $this->assertEquals($item->getEndTime(), $loadItem->getEndTime());
        $this->assertEquals($item->getListingTime(), $loadItem->getListingTime());
        $this->assertEquals($item->getListingType(), $loadItem->getListingType());
        $this->assertEquals($item->getListingStatus(), $loadItem->getListingStatus());
        $this->assertEquals($item->getCurrentPriceCurrencyCode(), $loadItem->getCurrentPriceCurrencyCode());
        $this->assertEquals($item->getCurrentPrice(), $loadItem->getCurrentPrice());
        $this->assertEquals($item->getBuyItNowPriceCurrencyCode(), $loadItem->getBuyItNowPriceCurrencyCode());
        $this->assertEquals($item->getBuyItNowPrice(), $loadItem->getBuyItNowPrice());
        $this->assertEquals($item->getSiteId(), $loadItem->getSiteId());
        $this->assertEquals($item->getItemUrl(), $loadItem->getItemUrl());
        $this->assertEquals($item->getQuantityRemaining(), $loadItem->getQuantityRemaining());
        $this->assertEquals($item->getBidCount(), $loadItem->getBidCount());

        $item->delete();
    }
}
