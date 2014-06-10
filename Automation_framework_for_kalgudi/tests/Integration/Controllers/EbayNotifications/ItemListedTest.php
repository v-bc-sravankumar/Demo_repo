<?php

namespace Integration\Controllers\EbayNotifications;

use Store\Ebay\Item;

class ItemListedTest extends AbstractNotificationTest
{
    public function testItemListedSucceeds()
    {
        $itemId = uniqid();

        $item = new Item();
        $item
            ->setItemId($itemId)
            ->save();

        $replacements = array(
            'ItemId' => $itemId,
            'Title' => 'Awesome Thing',
        );

        $result = $this->postNotification('ItemListed', 'ItemListedSuccess', $replacements);

        $this->assertTrue($result);

        $item->load();

        $this->assertEquals('active', $item->getListingStatus());
        $this->assertEquals('http://cgi.sandbox.ebay.com.au/Awesome Thing/' . $itemId, $item->getItemUrl());

        $item->delete();
    }
}
