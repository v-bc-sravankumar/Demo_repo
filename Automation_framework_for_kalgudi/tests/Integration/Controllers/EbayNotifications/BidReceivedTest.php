<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class BidReceivedTest extends AbstractNotificationTest
{
    public function testBidReceivedSucceeds()
    {
        $itemId = uniqid();

        $item = new Item();
        $item
            ->setItemId($itemId)
            ->save();

        $replacements = array(
            'ItemId' => $itemId,
            'CurrentPrice' => 123.45,
            'CurrentPriceCurrency' => 'AUD',
            'BidCount' => 17
        );

        $result = $this->postNotification('BidReceived', 'BidReceivedSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals($replacements['CurrentPrice'], $item->getCurrentPrice());
        $this->assertEquals($replacements['CurrentPriceCurrency'], $item->getCurrentPriceCurrencyCode());
        $this->assertEquals($replacements['BidCount'], $item->getBidCount());

        $item->delete();
    }
}
