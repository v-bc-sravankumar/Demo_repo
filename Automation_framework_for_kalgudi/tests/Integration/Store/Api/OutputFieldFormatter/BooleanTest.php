<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_Boolean('field', array());
		$this->assertSame(true, $formatter->format(true));

		$this->assertSame(true, $formatter->format('true'));
	}
}
