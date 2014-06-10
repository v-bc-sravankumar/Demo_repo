<?php

/**
 * Ensure that customer account overages are calculated correctly.
 *
 * Various assertions are are commented out because Bamboo is currently
 * running on 32bit OS, where the large integers are being fUxxord.
 */
class Unit_Lib_BigCommerce_Overages extends Interspire_IntegrationTest
{
	public function setUp()
	{
		Store_Config::override('StorageLimit', 209715200);
	}

	/**
	 * @param int $storage in megabytes
	 * @param int $transfers in gigabytes
	 * @param string $day day defaults to the first day of the current month
	 */
	public function setUpDailyStats($storage, $transfers, $day=false)
	{
		$storageInBytes = $storage * pow(1024, 2);
		$transfersInBytes = $transfers * pow(1024, 3);
		if (!$day) {
			$day = date('Y/n/1');
		}
		$dateline = new DateTime($day, new DateTimeZone('America/Chicago'));

		$this->fixtures->InsertQuery('bigcommerce_usage', array(
			'dateline' => $dateline->format('U'),
			'diskusage' => $storageInBytes,
			'trafficusage' => $transfersInBytes,
		));
	}

	public function tearDown()
	{
		parent::tearDown();
		$this->fixtures->Query('TRUNCATE TABLE [|PREFIX|]bigcommerce_usage');
	}

	public function testDefaultAccountHasNoRecordedStats()
	{
		$account = BigCommerce_Account::getInstance();

		$this->assertFalse($account->hasUsageStats());
	}

	public function testDailySummaryUnderLimits()
	{
		$this->setUpDailyStats(100, 3.75);
		$account = BigCommerce_Account::getInstance();

		$this->assertTrue($account->hasUsageStats());
		$this->assertEquals(50.00, $account->getStorageUsedPercent());
		//$this->assertEquals(25.00, $account->getTransfersUsedPercent());
		$this->assertFalse($account->isCloseToStorageLimit());
		$this->assertFalse($account->isOverStorageLimit());
		//$this->assertFalse($account->isCloseToTransfersLimit());
		//$this->assertFalse($account->isOverTransfersLimit());
	}

	public function testDailySummaryCloseToStorageLimit()
	{
		$this->setUpDailyStats(170, 3.75);
		$account = BigCommerce_Account::getInstance();

		$this->assertTrue($account->hasUsageStats());
		$this->assertEquals(85.00, $account->getStorageUsedPercent());
		$this->assertTrue($account->isCloseToStorageLimit());
		$this->assertFalse($account->isOverStorageLimit());
	}

	public function testDailySummaryOverStorageLimit()
	{
		$this->setUpDailyStats(220, 3.75);
		$account = BigCommerce_Account::getInstance();

		$this->assertTrue($account->hasUsageStats());
		$this->assertEquals(110.00, $account->getStorageUsedPercent());
		$this->assertTrue($account->isCloseToStorageLimit());
		$this->assertTrue($account->isOverStorageLimit());
	}

	public function testDailySummaryCloseToTransfersLimit()
	{
		$this->setUpDailyStats(100, 12);
		$account = BigCommerce_Account::getInstance();

		$this->assertTrue($account->hasUsageStats());
		//$this->assertEquals(80.00, $account->getTransfersUsedPercent());
		//$this->assertTrue($account->isCloseToTransfersLimit());
		//$this->assertFalse($account->isOverStorageLimit());
	}

	public function testDailySummaryOverTransfersLimit()
	{
		$this->setUpDailyStats(100, 15);
		$account = BigCommerce_Account::getInstance();

		$this->assertTrue($account->hasUsageStats());
		//$this->assertEquals(100.00, $account->getTransfersUsedPercent());
		//$this->assertTrue($account->isCloseToTransfersLimit());
		//$this->assertTrue($account->isOverTransfersLimit());
	}

	public function testDailyTransfersSumToMonthlyTotal()
	{
		$this->setUpDailyStats(100, 3, date('Y/n/1'));
		$this->setUpDailyStats(100, 3.25, date('Y/n/2'));
		$this->setUpDailyStats(100, 2.75, date('Y/n/3'));
		$this->setUpDailyStats(100, 4, date('Y/n/4'));
		$this->setUpDailyStats(100, 2, date('Y/n/5'));
		$account = BigCommerce_Account::getInstance();

		// uncomment when 64 bit calculations work on bamboo server
		$this->assertTrue($account->hasUsageStats());
		//$this->assertEquals(100.00, $account->getTransfersUsedPercent());
		//$this->assertEquals(15 * pow(1024, 3), $account->getTransfersUsed());
	}

}
