<?php

namespace Unit\App\Store\Ebay;

use Store\Ebay\Item;
use PHPUnit_Framework_TestCase;

class ItemTest extends PHPUnit_Framework_TestCase
{
    public function testProductId()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setProductId(45));
        $this->assertEquals(45, $item->getProductId());
    }

    public function testItemId()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setItemId('1039'));
        $this->assertEquals('1039', $item->getItemId());
    }

    public function testTitle()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setTitle('My Ebay Item'));
        $this->assertEquals('My Ebay Item', $item->getTitle());
    }

    public function testStartTime()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setStartTime('2013-08-27T06:24:09.295Z'));
        $this->assertEquals('2013-08-27T06:24:09.295Z', $item->getStartTime());
    }

    public function testEndTime()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setEndTime('2013-09-03T06:24:09.295Z'));
        $this->assertEquals('2013-09-03T06:24:09.295Z', $item->getEndTime());
    }

    public function testListingTime()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setListingTime(1377584650));
        $this->assertEquals(1377584650, $item->getListingTime());
    }

    public function testListingType()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setListingType('FixedPriceItem'));
        $this->assertEquals('FixedPriceItem', $item->getListingType());
    }

    public function testListingStatus()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setListingStatus('active'));
        $this->assertEquals('active', $item->getListingStatus());
    }

    public function testCurrentPriceCurrencyCode()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setCurrentPriceCurrencyCode('USD'));
        $this->assertEquals('USD', $item->getCurrentPriceCurrencyCode());
    }

    public function testCurrentPrice()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setCurrentPrice(112.00));
        $this->assertEquals(112.00, $item->getCurrentPrice());
    }

    public function testBuyItNowPriceCurrencyCode()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setBuyItNowPriceCurrencyCode('AUD'));
        $this->assertEquals('AUD', $item->getBuyItNowPriceCurrencyCode());
    }

    public function testBuyItNowPrice()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setBuyItNowPrice(96.88));
        $this->assertEquals(96.88, $item->getBuyItNowPrice());
    }

    public function testSiteId()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setSiteId(1));
        $this->assertEquals(1, $item->getSiteId());
    }

    public function testItemUrl()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setItemUrl('http://www.ebay.com/my-item/12345'));
        $this->assertEquals('http://www.ebay.com/my-item/12345', $item->getItemUrl());
    }

    public function testQuantityRemaining()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setQuantityRemaining(13));
        $this->assertEquals(13, $item->getQuantityRemaining());
    }

    public function testBidCount()
    {
        $item = new Item();
        $this->assertEquals($item, $item->setBidCount(9));
        $this->assertEquals(9, $item->getBidCount());
    }
}
