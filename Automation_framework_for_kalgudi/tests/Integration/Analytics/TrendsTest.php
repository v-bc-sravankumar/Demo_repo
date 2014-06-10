<?php

namespace Integration\Analytics;

use Analytics\Trends;
use Analytics\Metrics;
use Analytics\DateRange;
use DateTime;

class TrendsTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->trends = new Trends();
	}

	public function testGetTrends()
	{
		$this->checkTrends(null);
		$this->checkTrends(Metrics::DAILY);
		$this->checkTrends(Metrics::WEEKLY);
		$this->checkTrends(Metrics::MONTHLY);
		$this->checkTrends(Metrics::YEARLY);
	}

	public function testConversionStats()
	{
		$orders = array("title" =>"Orders",
				"current"=> 10,
				"previous"=> 2,
				"previous_totals"=> 99,
		);

		$visitors = array("title" =>"Visitors",
				"current"=> 100,
				"previous"=> 2,
				"previous_totals"=> 100,
		);

		$conversion = $this->trends->getConversionStats($orders, $visitors);
		$this->assertEquals($conversion['current'], '10.0%');
		$this->assertEquals($conversion['previous'], '100%');
		$this->assertEquals($conversion['previous_totals'], '99.0%');
	}


	private function checkTrends($period)
	{
		$trends = $this->trends->getTrends($period);

		foreach ($trends as $trend) {
			if (isset($trend['title'])) {
				$this->checkStats($trend, $period);
			} else {
				//date ranges
				$this->checkDateRanges($trend, $period);
			}
		}
	}

	private function checkStats($trend)
	{
		$this->assertNotNull($trend['title']);
		$this->assertArrayHasKey('current', $trend);
		$this->assertArrayHasKey('previous', $trend);
		$this->assertArrayHasKey('previous_totals', $trend);
	}

	private function checkDateRanges($trend, $period)
	{
		$currentPeriodNames = $this->trends->getCurrentPeriodNames();
		$previousPeriodNames = $this->trends->getPreviousPeriodNames();

		$this->assertEquals($trend['current_period_name'], $currentPeriodNames[$period]);
		$this->assertEquals($trend['previous_period_name'], $previousPeriodNames[$period]);

		$date = new DateTime();
		//check valid timestamps are available
		$this->assertTrue($date->setTimestamp($trend['current']->start) !== false);
		$this->assertTrue($date->setTimestamp($trend['current']->end) !== false);

		$this->assertTrue($date->setTimestamp($trend['previous']->start) !== false);
		$this->assertTrue($date->setTimestamp($trend['previous']->end) !== false);

		$this->assertTrue($date->setTimestamp($trend['previous_totals']->start) !== false);
		$this->assertTrue($date->setTimestamp($trend['previous_totals']->end) !== false);

		//check formatted dates
		if ($period == Metrics::DAILY) {
			//ensure format is 12 Jun (2:45pm)
			$dailyRegExp = "/[0-9]{2}[ ]{1}[A-Z]{1}[a-z]{2}[ ]{1}\([0-9]+:[0-9]{2}[ap]m\)/";
			$this->assertRegExp($dailyRegExp, $trend['current_period']);
			$this->assertRegExp($dailyRegExp, $trend['previous_period']);
			$this->assertRegExp($dailyRegExp, $trend['previous_totals_period']);
		} else {
			//ensure format
			$this->assertRegExp($this->getNonDailyRegExp($trend['current']), $trend['current_period']);
			$this->assertRegExp($this->getNonDailyRegExp($trend['previous']), $trend['previous_period']);
			$this->assertRegExp($this->getNonDailyRegExp($trend['previous_totals']), $trend['previous_totals_period']);
		}
	}

	private function getNonDailyRegExp($date)
	{
		$dayMonth = "[0-9]{2}[ ]{1}[A-Z]{1}[a-z]{2}[ ]{1}";
		//ex: 01 Jan - 12 Dec '13 OR 01-07 Jun '13
		if (date('m', $date->start) == date('m', $date->end)) {
			$otherRegExp = "/[0-9]{2}\-".$dayMonth."\'[0-9]{2}/";
		} else {

			$otherRegExp = "/".$dayMonth."\-[ ]{1}".$dayMonth."\'[0-9]{2}/";
		}

		return $otherRegExp;
	}

	/**
	 * Test localized time
	 */
	public function testLocalizedTimeGMT()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$date = new DateRange();
		$start = new DateTime("midnight sunday this week");
		$end = new DateTime("midnight monday this week");

		$date->start = $start->getTimestamp();
		$date->end = $end->getTimestamp();

		$value = $this->trends->formatTrendsDate(Metrics::DAILY, $date);

		$regExp = "/.*\(12.00am\)/";
		$this->assertRegExp($regExp, $value);
	}

	public function testLocalizedTimeCT()
	{
		\Store_Config::override('StoreTimeZone', '-6');
		\Store_Config::override('StoreDSTCorrection', 0);

		$date = new DateRange();
		$start = new DateTime("midnight sunday this week");
		$end = new DateTime("midnight monday this week");

		$date->start = $start->getTimestamp();
		$date->end = $end->getTimestamp();

		$value = $this->trends->formatTrendsDate(Metrics::DAILY, $date);

		$regExp = "/.*\(6.00pm\)/";
		$this->assertRegExp($regExp, $value);
	}

	public function tearDown()
	{
		\Store_Config::override('StoreTimeZone', \Store_Config::getOriginal('StoreTimeZone'));
		\Store_Config::override('StoreDSTCorrection', \Store_Config::getOriginal('StoreDSTCorrection'));
	}

}
