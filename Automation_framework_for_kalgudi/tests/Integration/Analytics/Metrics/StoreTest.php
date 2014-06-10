<?php

namespace Integration\Analytics\Metrics;

use Analytics\Metrics\Store;

class StoreTest extends \PHPUnit_Framework_TestCase
{
	public function testGetVisitorsForPeriod()
	{
		$to = $from = time();
		$summary = Store::getVisitorsForPeriod($from, $to);
		$this->assertEquals($summary, 0);
	}
}

