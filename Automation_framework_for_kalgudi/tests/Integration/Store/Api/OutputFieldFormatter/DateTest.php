<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class DateTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_Date('field', array());
		$this->assertSame('', $formatter->format(null));

		$time = time();
		$this->assertSame(date('r', $time), $formatter->format($time));
	}
}
