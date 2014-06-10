<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class IntTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_Int('field', array());
		$this->assertSame(1, $formatter->format(1));
		$this->assertSame(1, $formatter->format('1'));
		$this->assertSame(1, $formatter->format(1.2));
	}
}
