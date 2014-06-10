<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class AvailabilityTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$formatter = new \Store_Api_OutputFieldFormatter_Availability('field', array());
		$this->assertSame('disabled', $formatter->format(array('allow_purchases' => 0, 'preorder' => 0)));
		$this->assertSame('disabled', $formatter->format(array('allow_purchases' => 0, 'preorder' => 1)));
		$this->assertSame('available', $formatter->format(array('allow_purchases' => 1, 'preorder' => 0)));
		$this->assertSame('preorder', $formatter->format(array('allow_purchases' => 1, 'preorder' => 1)));
	}
}
