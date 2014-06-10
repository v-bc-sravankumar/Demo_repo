<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class CategoriesTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_Categories('field', array());
		$this->assertSame(array(1, 3, 4, 5), $formatter->format("5,3,4,1"));
		$this->assertSame(array(8), $formatter->format("8"));
	}
}
