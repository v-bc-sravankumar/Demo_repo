<?php

namespace Integration\Analytics;

use Analytics\DateTimeHelper;
use Analytics\Metrics;
use DateTime;

class DateTimeHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * The number of seconds in a day, unless that day has a
	 * {@link http://en.wikipedia.org/wiki/Leap_second leap second}.
	 */
	const SECONDS_PER_DAY = 86400;

	/**
	 * @dataProvider beginningOfDayFullTextSample
	 */
	public function testFormatDateBeginningAsFullTextWithOffsetOverride($timeZoneOffset, $timestamp, $expected)
	{
		\Store_Config::override('StoreTimeZone', $timeZoneOffset);
		\Store_Config::override('StoreDSTCorrection', 0);
		$result = DateTimeHelper::formatDate('l', $timestamp);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider endOfDayFullTextSample
	 */
	public function testFormatDateEndAsFullTextWithOffsetOverride($timeZoneOffset, $timestamp, $expected)
	{
		\Store_Config::override('StoreTimeZone', $timeZoneOffset);
		\Store_Config::override('StoreDSTCorrection', 0);
		$result = DateTimeHelper::formatDate('l', $timestamp);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider beginningOfDayFullTextSample
	 */
	public function testFormatDateBeginningAsFullTextWithOffsetArgument($timeZoneOffset, $timestamp, $expected)
	{
		$result = DateTimeHelper::formatDate('l', $timestamp, $timeZoneOffset);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider endOfDayFullTextSample
	 */
	public function testFormatDateEndAsFullTextWithOffsetArgument($timeZoneOffset, $timestamp, $expected)
	{
		$result = DateTimeHelper::formatDate('l', $timestamp, $timeZoneOffset);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider timeZoneOffsets
	 */
	public function testGetTodayText($timeZoneOffset)
	{
		\Store_Config::override('StoreTimeZone', $timeZoneOffset);
		\Store_Config::override('StoreDSTCorrection', 0);

		$this->assertEquals(GetLang('Today'), DateTimeHelper::getDayFullText(time()));
	}

	/**
	 * @dataProvider timeZoneOffsets
	 */
	public function testGetYesterdayText($timeZoneOffset)
	{
		\Store_Config::override('StoreTimeZone', $timeZoneOffset);
		\Store_Config::override('StoreDSTCorrection', 0);
		$yesterdayGmt = time() - self::SECONDS_PER_DAY;

		$this->assertEquals(GetLang('Yesterday'), DateTimeHelper::getDayFullText($yesterdayGmt));
	}


	/**
	 * Timestamp ranges for the current period : DAILY
	 */
	public function testGetCurrentTimestampRangeDaily()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$now = new DateTime("now");

		$daily = DateTimeHelper::getCurrentTimestampRange(Metrics::DAILY);
		$start = new DateTime("now");
		$start->setTime(0, 0, 0);
		$this->assertTrue($daily->start >= $start);
		$this->assertTrue($daily->end >= $now);
	}

	/**
	 * Timestamp ranges for the current period : WEEKLY
	 */
	public function testGetCurrentTimestampRangeWeekly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$now = new DateTime("now");
		$weekly = DateTimeHelper::getCurrentTimestampRange(Metrics::WEEKLY);
		//ensure start is midnight on a sunday
		$this->assertEquals(date('D H:i:s',$weekly->start), 'Sun 00:00:00');
		$this->assertTrue($weekly->end >= $now);
	}

	/**
	 * Timestamp ranges for the current period : MONTHLY
	 */
	public function testGetCurrentTimestampRangeMonthly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$now = new DateTime("now");
		$monthly = DateTimeHelper::getCurrentTimestampRange(Metrics::MONTHLY);
		//get the first day of this month
		$start = new DateTime('first day of this month');
		//ensure start is midnight
		$this->assertEquals(date('H:i:s',$monthly->start), '00:00:00');
		//ensure dates match
		$this->assertEquals(date('d D',$monthly->start), $start->format('d D'));

		$this->assertTrue($monthly->end >= $now);
	}

	/**
	 * Timestamp ranges for the current period : YEARLY
	 */
	public function testGetCurrentTimestampRangeYearly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$now = new DateTime("now");
		$yearly = DateTimeHelper::getCurrentTimestampRange(Metrics::YEARLY);

		//ensure start is midnight on the 1st day of the year
		$this->assertEquals(date('z H:i:s',$yearly->start), '0 00:00:00');
		$this->assertTrue($yearly->end >= $now);
	}

	/**
	 * Timestamp ranges for the previous period : DAILY
	 */
	public function testGetPreviousTimestampRangeDaily()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$daily = DateTimeHelper::getPreviousTimestampRange(Metrics::DAILY);

		$now = new DateTime("now");
		$yesterday = new DateTime("yesterday");

		//start is yesterday midnight
		$this->assertEquals(date('H:i:s',$daily->start), '00:00:00');
		$this->assertEquals(date('d M Y',$daily->start), $yesterday->format('d M Y'));

		//end is yesterday same time as now
		$this->assertEquals(date('d M Y',$daily->end), $yesterday->format('d M Y'));
		$this->assertEquals(date('H',$daily->end), $now->format('H'));
		$this->assertTrue((int)date('i',$daily->end) <= (int)$now->format('i'));
	}

	/**
	 * Timestamp ranges for the previous total period : DAILY
	 */
	public function testGetPreviousTotalTimestampRangeDaily()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$daily = DateTimeHelper::getPreviousTotalTimestampRange(Metrics::DAILY);

		$now = new DateTime("now");
		$yesterday = new DateTime("yesterday");

		//start is yesterday midnight
		$this->assertEquals(date('H:i:s',$daily->start), '00:00:00');
		$this->assertEquals(date('d M Y',$daily->start), $yesterday->format('d M Y'));

		//end is 23 hr 59 mins 59 secs after
		$this->assertEquals(date('H:i:s',$daily->end), '23:59:59');
		$this->assertEquals(date('d M Y',$daily->end), $yesterday->format('d M Y'));
	}

	/**
	 * Timestamp ranges for the previous total period : WEEKLY
	 */
	public function testGetPreviousTotalTimestampRangeWeekly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$weekly = DateTimeHelper::getPreviousTotalTimestampRange(Metrics::WEEKLY);

		$now = new DateTime("now");
		$lastweek = new DateTime("last week sunday");
		$lastweek->modify("-1 week");

		//start is last week sunday midnight
		$this->assertEquals(date('H:i:s',$weekly->start), '00:00:00');
		$this->assertEquals(date('d M Y',$weekly->start), $lastweek->format('d M Y'));

		//end is 1 second before the start of the current week (weeks starts sunday)
		$lastweek = new DateTime("last week sunday");
		$lastweek->modify("-1 second");
		$this->assertEquals(date('H:i:s',$weekly->end), '23:59:59');
		$this->assertEquals(date('d M Y',$weekly->end), $lastweek->format('d M Y'));
	}

	/**
	 * Timestamp ranges for the previous total period : MONTHLY
	 */
	public function testGetPreviousTotalTimestampRangeMonthly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		$monthly = DateTimeHelper::getPreviousTotalTimestampRange(Metrics::MONTHLY);

		$now = new DateTime("now");
		//get the first day of last month
		$lastmonth = new DateTime('first day of this month');
		$lastmonth->modify("-1 month");

		//ensure start is midnight
		$this->assertEquals(date('H:i:s',$monthly->start), '00:00:00');
		//ensure date is the first
		$this->assertEquals(date('d' ,$monthly->start), '01');
		//ensure dates match
		$this->assertEquals(date('d M Y',$monthly->start), $lastmonth->format('d M Y'));

		//end is 1 day before the start of the current month
		$lastmonth = new DateTime('first day of this month');
		$lastmonth->modify("-1 day");

		$this->assertEquals(date('H:i:s',$monthly->end), '23:59:59');
		$this->assertEquals(date('d M Y',$monthly->end), $lastmonth->format('d M Y'));
	}

	/**
	 * Timestamp ranges for the previous total period : YEARLY
	 */
	public function testGetPreviousTotalTimestampRangeYearly()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);


		$yearly = DateTimeHelper::getPreviousTotalTimestampRange(Metrics::YEARLY);

		$now = new DateTime("now");
		//get the first day of last year
		$lastyear = new DateTime('first day of this year');
		$lastyear->modify("-1 year");

		//ensure start is midnight
		$this->assertEquals(date('H:i:s',$yearly->start), '00:00:00');
		//ensure date is the first of Jan
		$this->assertEquals(date('d M' ,$yearly->start), '01 Jan');
		//ensure years match
		$this->assertEquals(date('Y',$yearly->start), $lastyear->format('Y'));

		//end is 31st Dec last year
		$this->assertEquals(date('H:i:s',$yearly->end), '23:59:59');
		$this->assertEquals(date('d M',$yearly->end), '31 Dec');
		//ensure years match
		$this->assertEquals(date('Y',$yearly->start), $lastyear->format('Y'));

	}

	/**
	 * getTimestampRangeForADay ending with current time or 1 second before midnight
	 */
	public function testGetTimestampRangeForADay()
	{
		\Store_Config::override('StoreTimeZone', 0);
		\Store_Config::override('StoreDSTCorrection', 0);

		//today ending with current time
		$date= DateTimeHelper::getTimestampRangeForADay(0, true);

		$now = new DateTime("now");

		$this->assertEquals(date('d M Y H',$date->end), $now->format('d M Y H'));
		$this->assertTrue(date('i',$date->end) >= $now->format('i'));


		//today ending 1 second before midnight
		$date= DateTimeHelper::getTimestampRangeForADay(0, false);

		$now = new DateTime("now");

		$this->assertEquals(date('d M Y',$date->end), $now->format('d M Y'));
		$this->assertEquals(date('H:i:s',$date->end), '23:59:59');
	}

	public function timeZoneOffsets()
	{
		$timeZoneOffsets = array();

		for ($i = -11; $i <= 12; $i++) {
			array_push($timeZoneOffsets, array($i));
		}
		return $timeZoneOffsets;
	}

	public function beginningOfDayFullTextSample()
	{
		$negativeOffsets = array();
		$nonNegativeOffsets = array();

		for ($i = -11; $i < 0; $i++) {
			$negativeOffsets[] = array($i, 501206400, 'Monday');
		}

		for ($i = 0; $i <= 12; $i++) {
			$nonNegativeOffsets[] = array($i, 501206400, 'Tuesday');
		}

		$fullTextSample = array_merge($negativeOffsets, $nonNegativeOffsets);

		return $fullTextSample;
	}

	public function endOfDayFullTextSample()
	{
		$nonPositiveOffsets = array();
		$positiveOffsets = array();

		for ($i = -11; $i <= 0; $i++) {
			$nonPositiveOffsets[] = array($i, 501292799, 'Tuesday');
		}

		for ($i = 1; $i <= 12; $i++) {
			$nonNegativeOffsets[] = array($i, 501292799, 'Wednesday');
		}

		$fullTextSample = array_merge($nonPositiveOffsets, $positiveOffsets);

		return $fullTextSample;
	}

    public function testGetPreviousTimestmapRangeWeeklyGMT()
    {
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 0);

        // normal monday
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-05 10:30:00',
            2013, 7, 28, '00:00:00',
            2013, 7, 29, '10:30:00');

        // sunday is special case because PHP treats it as end of a week
        // but we treat it as start of the week (US standard calendar)
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-04 10:30:00',
            2013, 7, 28, '00:00:00',
            2013, 7, 28, '10:30:00');
    }

    public function testGetPreviousTimestmapRangeWeeklyGMTPositive()
    {
        // GMT+12 timezone
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-05 10:00:00', // '2013-08-05 22:00:00 at GMT+12
            2013, 7, 27, '12:00:00',
            2013, 7, 29, '10:00:00');

        // forward timezone overflow (from GMT Saturday to GMT+12 Sunday)
        // which according to our US standard, is the start of next week
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-03 22:00:00', // '2013-08-04 10:00:00 at GMT+12
            2013, 7, 27, '12:00:00',
            2013, 7, 27, '22:00:00');
    }

    public function testGetPreviousTimestmapRangeWeeklyGMTNegative()
    {
        // GMT+12 timezone
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', -12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-05 22:00:00', // '2013-08-04 10:00:00 at GMT-12
            2013, 7, 28, '12:00:00',
            2013, 7, 29, '22:00:00');

        // backward timezone overflow (from GMT Sunday to GMT-12 Saturday)
        // which according to our US standard, is the end of last week
        $this->assertGetPreviousTimestampRangeHelper(Metrics::WEEKLY, '2013-08-04 10:00:00', // '2013-08-03 22:00:00 at GMT-12
            2013, 7, 21, '12:00:00',
            2013, 7, 28, '10:00:00');
    }

    public function testGetPreviousTimestampRangeMonthlyGMT()
    {
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 0);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-02-10 10:30:00',
            2013, 1, 1, '00:00:00',
            2013, 1, 10, '10:30:00');

        // possible month overflow
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-03-31 10:30:00',
            2013, 2, 1, '00:00:00',
            2013, 2, 28, '23:59:59');

        // possible year overflow
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-01-31 10:30:00',
            2012, 12, 1, '00:00:00',
            2012, 12, 31, '10:30:00');
    }

    public function testGetPreviousTimestampRangeMonthlyGMTPositive()
    {
        // GMT+12 timezone
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-02-10 10:30:00', // '2013-02-10 22:30:00' at GMT+12
            2012, 12, 31, '12:00:00',
            2013, 1, 10, '10:30:00');

        // forward timezone overflow
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-03-31 22:00:00', // '2013-04-01 10:00:00' at GMT+12
            2013, 2, 28, '12:00:00',
            2013, 2, 28, '22:00:00');
    }

    public function testGetPreviousTimestampRangeMonthlyGMTNegative()
    {
        // GMT-12 timezone
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', -12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-02-10 22:30:00', // '2013-02-10 10:30:00' at GMT-12
            2013, 1, 1, '12:00:00',
            2013, 1, 10, '22:30:00');

        // backward timezone overflow
        $this->assertGetPreviousTimestampRangeHelper(Metrics::MONTHLY, '2013-03-01 10:00:00', // '2013-02-28 22:00:00' at GMT-12
            2013, 1, 1, '12:00:00',
            2013, 1, 29, '10:00:00');
    }

    /**
     * Timestamp ranges for the previous period : YEARLY
     */
    public function testGetPreviousTimestampRangeYearlyGMT()
    {
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 0);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2013-02-10 10:30:00',
            2012, 1, 1, '00:00:00',
            2012, 2, 10, '10:30:00');

        // case of possible leap year overflow
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2016-02-29 10:30:00',
            2015, 1, 1, '00:00:00',
            2015, 2, 28, '23:59:59');
    }

    public function testGetPreviousTimestampRangeYearlyGMTPositive()
    {
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', 12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2013-02-10 10:00:00', // 2013-02-10 22:00:00 at GMT+12
            2011, 12, 31, '12:00:00',
            2012, 2, 10, '10:00:00');

        // overflow to next year
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2012-12-31 22:00:00', // 2013-01-01 10:00:00 at GMT+12
            2011, 12, 31, '12:00:00',
            2011, 12, 31, '22:00:00');
    }

    public function testGetPreviousTimestampRangeYearlyGMTNegative()
    {
        \Store_Config::override('StoreDSTCorrection', 0);
        \Store_Config::override('StoreTimeZone', -12);

        // normal
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2013-02-10 22:00:00', // 2013-02-10 10:00:00 at GMT-12
            2012, 1, 1, '12:00:00',
            2012, 2, 10, '22:00:00');

        // overflow to last year
        $this->assertGetPreviousTimestampRangeHelper(Metrics::YEARLY, '2013-01-01 10:00:00', // 2012-12-31 22:00:00 at GMT-12
            2011, 1, 1, '12:00:00',
            2012, 1, 1, '10:00:00');
    }

    private function assertGetPreviousTimestampRangeHelper($period, $now,
        $startYear, $startMonth, $startDay, $startTime,
        $endYear, $endMonth, $endDay, $endTime)
    {
        $value = DateTimeHelper::getPreviousTimestampRange($period, $now);
        $start = $value->start;
        $end = $value->end;

        // start should always be midnight of first day last month
        $this->assertEquals($startYear, gmdate('Y', $start));
        $this->assertEquals($startMonth, gmdate('n', $start));
        $this->assertEquals($startDay, gmdate('d', $start));
        $this->assertEquals($startTime, gmdate('H:i:s', $start));

        // check end timestamp
        $this->assertEquals($endYear, gmdate('Y', $end));
        $this->assertEquals($endMonth, gmdate('n', $end));
        $this->assertEquals($endDay, (int)gmdate('d', $end));
        $this->assertEquals($endTime, gmdate('H:i:s', $end));
    }

	public function tearDown()
	{
		\Store_Config::override('StoreTimeZone', \Store_Config::getOriginal('StoreTimeZone'));
		\Store_Config::override('StoreDSTCorrection', \Store_Config::getOriginal('StoreDSTCorrection'));
	}
}
