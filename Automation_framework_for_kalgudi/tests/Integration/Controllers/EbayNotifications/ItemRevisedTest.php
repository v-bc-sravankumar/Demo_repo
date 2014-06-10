<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class ItemRevisedTest extends AbstractNotificationTest
{
    private function createItem()
    {
        $item = new Item();
        $item
            ->setItemId(uniqid())
            ->save();

        return $item;
    }

    private function assertItemRevised($listingType, Item $item, $replacements)
    {
        $replacements = array_merge(array(
            'ItemId' => $item->getItemId(),
            'Title' => 'Revised Title',
            'CurrentPrice' => 44.98,
            'CurrentPriceCurrency' => 'USD',
        ), $replacements);

        $result = $this->postNotification('ItemRevised', $listingType . '.ItemRevisedSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals($replacements['Title'], $item->getTitle());
        $this->assertEquals($replacements['CurrentPrice'], $item->getCurrentPrice());
        $this->assertEquals($replacements['CurrentPriceCurrency'], $item->getCurrentPriceCurrencyCode());
    }

    public function testItemRevisedForFixedPriceItemSucceeds()
    {
        $item = $this->createItem();

        $this->assertItemRevised('FixedPriceItem', $item, array(
            'QuantityRemaining' => 4
        ));

        $this->assertEquals(4, $item->getQuantityRemaining());

        $item->delete();
    }

    public function testItemRevisedForAuctionItemSucceeds()
    {
        $item = $this->createItem();

        $this->assertItemRevised('Auction', $item, array(
            'BuyItNowPrice' => 205.01,
            'BuyItNowPriceCurrency' => 'USD',
        ));

        $this->assertEquals(205.01, $item->getBuyItNowPrice());
        $this->assertEquals('USD', $item->getBuyItNowPriceCurrencyCode());

        $item->delete();
    }
}
