<?php

class Unit_Ebay_Notifications extends Interspire_IntegrationTest
{
	const TIMESTAMP = '2010-05-28T04:38:02.811Z';

	/**
	 * Function to send eBay notification to store's listener
	 */
	public function sendNotification($soapAction, $notificationFeed)
	{
		$listener = new ISC_ADMIN_EBAY_NOTIFICATIONS_LISTENER();
	 	$result = $listener->handleRequest(new Interspire_Request(), $soapAction, $notificationFeed);
		return $result;
	}

	/**
	 * Generate signature
	 * @param string $timestamp
	 * @return string
	 */
	private function buildNotificationSignature($timestamp)
	{
		$signature = base64_encode(md5(self::TIMESTAMP . GetConfig("EbayDevId") . GetConfig("EbayAppId") . GetConfig("EbayCertId"), true));
		return $signature;
	}

	/**
	 * Function to create test items to our ebay item table
	 */
	protected function createTestItem($title, $itemId = '111111111111', $listingType = 'Chinese', $status = 'Pending', $quantityRemaining = 5, $bidCount = 9, $currentPriceCurrency = 'USD', $currentPrice = 100.00)
	{
		$this->removeTestItems($title);
		$this->fixtures->InsertQuery('ebay_items', array(
			'product_id' => "1",
			'ebay_item_id' => $itemId,
			'title' => $title,
			'start_time' => "",
			'end_time' => "",
			'datetime_listed' => 0,
			'listing_type' => $listingType,
			'listing_status' => $status,
			'current_price_currency' => "USD",
			'current_price' => "5",
			'buyitnow_price_currency' => "USD",
			'buyitnow_price' => "0",
			'site_id' => "0",
			'ebay_item_link' => "",
			'quantity_remaining' => $quantityRemaining,
			'bid_count' => $bidCount,
		));
	}

	/**
	 * Function to remove the created test items
	 */
	private function removeTestItems($title)
	{
		//$this->fixtures->DeleteQuery('ebay_items', "WHERE title like '" . $title . "%'");
		$this->fixtures->Query("TRUNCATE TABLE [|PREFIX|]ebay_items");
	}

	/**
	 * Testing the success process of Item Sold for Chinese auction type
	 */
	public function testItemSoldChineseSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMSOLD';
		$soapAction = 'ItemSold';

		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemSoldChineseSuccess.xml');
		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese', 'active');
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertEquals('won', $ebayItem->getListingStatus());
		$this->assertTrue($result);
	}

	/**
	 * Testing the success process of Item Sold for Fixed Price Item auction type
	 */
	public function testItemSoldFixedPriceItemSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMSOLD';
		$soapAction = 'ItemSold';

		$soldQuantity = 1;
		$expectedQuantityRemaining = 0;

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemSoldFixedPriceItemSuccess.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'FixedPriceItem', 'active');
		$ebayItemBeforeSold = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$expectedQuantityRemaining = $ebayItemBeforeSold->getQuantityRemaining() - $soldQuantity;

		$result = $this->sendNotification($soapAction, $xml);
		$ebayItemAfterSold = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);

		$this->assertEquals('sold', $ebayItemAfterSold->getListingStatus());
		$this->assertEquals($expectedQuantityRemaining, $ebayItemAfterSold->getQuantityRemaining());
		$this->assertTrue($result);
	}

	/**
	 * Testing using invalid item id, where it doesn't match the item id of the item id in XML file
	 */
	public function testItemSoldInvalidItemId()
	{
		$ebayItemId = '222222222222'; // Invalid Item Id
		$testTitle = 'TEST_ITEMSOLD';
		$soapAction = 'ItemSold';

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemSoldChineseFailure.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese', 'active');
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertNotEquals('won', $ebayItem->getListingStatus());
		$this->assertFalse($result);
	}

	/**
	 * Testing the success process of Bid Received for Chinese auction type
	 */
	public function testBidReceivedSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_BIDRECEIVED';
		$soapAction = 'BidReceived';

		$expectedCurrentPriceCurrency = 'USD';
		$expectedCurrentPrice = 105.00;
		$expectedBidCount = 10;

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlBidReceivedSuccess.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese', 'active');
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertEquals($expectedBidCount, $ebayItem->getBidCount());
		$this->assertEquals($expectedCurrentPrice, $ebayItem->getCurrentPrice());
		$this->assertEquals($expectedCurrentPriceCurrency, $ebayItem->getCurrentPriceCurrency());
		$this->assertTrue($result);
	}

	/**
	 * Testing the Item Listed Successfully Notification
	 */
	public function testItemListedChineseSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMLISTED';
		$soapAction = 'ItemListed';

		$expectedListingStatus = 'active';
		$defaultEbayItemLink = '';

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemListedChineseSuccess.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese');
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertEquals($expectedListingStatus, $ebayItem->getListingStatus());
		$this->assertNotEquals($defaultEbayItemLink, $ebayItem->getEbayItemLink());
		$this->assertTrue($result);
	}

	/**
	 * Testing the Item Revised Sucessfully Notification
	 */
	public function testItemRevisedChineseSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMREVISED';
		$soapAction = 'ItemRevised';

		$expectedQuantityRemaining = '5';
		$expectedCurrentPriceCurrency = 'AUD';
		$expectedCurrentPrice = '10.00';
		$expectedBuyItNowPriceCurrency = 'AUD';
		$expectedBuyItNowPrice = '100.00';

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemRevisedChineseSuccess.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese');
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertEquals($expectedQuantityRemaining, $ebayItem->getQuantityRemaining());
		$this->assertEquals($expectedCurrentPriceCurrency, $ebayItem->getCurrentPriceCurrency());
		$this->assertEquals($expectedCurrentPrice, $ebayItem->getCurrentPrice());
		$this->assertEquals($expectedBuyItNowPriceCurrency, $ebayItem->getBuyItNowPriceCurrency());
		$this->assertEquals($expectedBuyItNowPrice, $ebayItem->getBuyItNowPrice());
		$this->assertTrue($result);
	}

	/**
	 * Testing the Item Unsold Successfully Notification
	 */
	public function testItemUnsoldSuccess()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMUNSOLD';
		$soapAction = 'ItemUnsold';

		$expectedListingStatus = 'unsold';
		$expectedQuantityRemaining = 0;

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlItemUnsoldSuccess.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese');
		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$expectedQuantityRemaining = $ebayItem->getQuantityRemaining();
		$result = $this->sendNotification($soapAction, $xml);

		$ebayItemAfterUnsold = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$this->assertEquals($expectedListingStatus, $ebayItemAfterUnsold->getListingStatus());
		$this->assertEquals($expectedQuantityRemaining, $ebayItemAfterUnsold->getQuantityRemaining());
		$this->assertTrue($result);
	}

	/**
	 * An AuctionCheckoutComplete notification is sent when a buyer completes
	 * the checkout process for an auction item or for a fixed price item.
	 */
	public function testAuctionCheckoutComplete()
	{
		$ebayItemId = '111111111111';
		$testTitle = 'TEST_ITEMAUCTIONCHECKOUTCOMPLETE';
		$soapAction = 'AuctionCheckoutComplete';

		$signature = $this->buildNotificationSignature(self::TIMESTAMP);
		$xml = file_get_contents(dirname(__FILE__) . '/Xml/XmlAuctionCheckoutComplete.xml');
		$xml = str_replace('%%SIGNATURE%%', $signature, $xml);
		$xml = str_replace('%%TIMESTAMP%%', self::TIMESTAMP, $xml);
		$this->createTestItem($testTitle, $ebayItemId, 'Chinese', 'active');
		$ebayItem = new ISC_ADMIN_EBAY_ITEMS($ebayItemId);
		$result = $this->sendNotification($soapAction, $xml);
		$this->assertTrue($result);
	}
}

