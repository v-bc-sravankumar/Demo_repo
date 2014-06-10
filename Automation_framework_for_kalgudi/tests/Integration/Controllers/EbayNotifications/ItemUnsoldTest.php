<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class ItemUnsoldTest extends AbstractNotificationTest
{
    public function testItemUnsoldSucceeds()
    {
        $itemId = uniqid();

        $item = new Item();
        $item
            ->setItemId($itemId)
            ->save();

        $replacements = array(
            'ItemId' => $itemId,
        );

        $result = $this->postNotification('ItemUnsold', 'ItemUnsoldSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals('unsold', $item->getListingStatus());

        $item->delete();
    }
}
