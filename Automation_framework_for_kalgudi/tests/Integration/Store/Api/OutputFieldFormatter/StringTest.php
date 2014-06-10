<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class StringTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_String('field', array());
		$this->assertSame('foo', $formatter->format('foo'));
		$this->assertSame('5', $formatter->format('5'));
		$this->assertSame('1', $formatter->format(true));
	}
}
