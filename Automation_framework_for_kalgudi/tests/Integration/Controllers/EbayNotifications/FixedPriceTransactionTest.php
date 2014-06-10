<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class FixedPriceTransactionTest extends AbstractNotificationTest
{
    public function testFixedPriceTransactionSucceeds()
    {
        $itemId = uniqid();

        $item = new Item();
        $item
            ->setItemId($itemId)
            ->save();

        $replacements = array(
            'ItemId' => $itemId,
            'Quantity' => 9,
            'QuantitySold' => 5,
        );

        $result = $this->postNotification('FixedPriceTransaction', 'FixedPriceTransactionSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals('sold', $item->getListingStatus());
        $this->assertEquals(4, $item->getQuantityRemaining());

        $item->delete();
    }
}
