<?php

namespace Integration\Analytics\Metrics;

use Analytics\Metrics\Orders;
use Analytics\Metrics;

class OrdersTest extends \PHPUnit_Framework_TestCase
{
	public function testGetOrdersSummaryFor7DaysIncludesToday()
	{
		$summary = Orders::getSummary(Metrics::LAST7DAYS);
		$this->assertArrayHasKey('Today', $summary);
	}

	public function testGetOrderStatsForPeriod()
	{
		$to = $from = time();

		$summary = Orders::getOrderStatsForPeriod($from, $to);
		$this->assertArrayHasKey('revenue', $summary);
		$this->assertArrayHasKey('orders', $summary);

		$this->assertEquals($summary['orders'], 0);
		$this->assertEquals($summary['revenue'], 0);
	}
}
