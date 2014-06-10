<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class ItemSoldTest extends AbstractNotificationTest
{
    private function createItem()
    {
        $item = new Item();
        $item
            ->setItemId(uniqid())
            ->save();

        return $item;
    }

    private function assertItemSold($listingType, $listingStatus, Item $item, $replacements = array())
    {
        $replacements = array_merge(array(
            'ItemId' => $item->getItemId(),
        ), $replacements);

        $result = $this->postNotification('ItemSold', $listingType . '.ItemSoldSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals($listingStatus, $item->getListingStatus());
    }

    public function testItemSoldForFixedPriceItemSucceeds()
    {
        $item = $this->createItem();

        $this->assertItemSold('FixedPriceItem', 'sold', $item, array(
            'Quantity' => 12,
            'QuantitySold' => 7,
        ));

        $this->assertEquals(5, $item->getQuantityRemaining());

        $item->delete();
    }

    public function testItemSoldForAuctionItemSucceeds()
    {
        $item = $this->createItem();

        $this->assertItemSold('Auction', 'won', $item);

        $item->delete();
    }
}
