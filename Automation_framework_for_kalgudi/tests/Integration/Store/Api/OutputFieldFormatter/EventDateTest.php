<?php

namespace Integration\Store\Api\OutputFieldFormatter;

class EventDateTest extends \PHPUnit_Framework_TestCase
{
	public function testFormatter()
	{
		$resourceMock = $this->getMock('Store_Api_Version_2_Resource_Products', array('getFields'));
		$resourceMock->expects($this->any())
			->method('getFields')
			->will($this->returnValue(
				array(
					'event_date_type' => array(
						'values' => array(
							'none',
							'range',
							'after',
							'before',
							'required',
						)
					)
				)
			));

		$formatter = new \Store_Api_OutputFieldFormatter_EventDate('field', array(), $resourceMock);

		$this->assertSame('none', $formatter->format(array('required' => 0, 'limited_type' => 0)));
		$this->assertSame('none', $formatter->format(array('required' => 0, 'limited_type' => 1)));
		$this->assertSame('none', $formatter->format(array('required' => 0, 'limited_type' => 2)));
		$this->assertSame('none', $formatter->format(array('required' => 0, 'limited_type' => 3)));

		$this->assertSame('required', $formatter->format(array('required' => 1, 'limited_type' => 0)));
		$this->assertSame('range', $formatter->format(array('required' => 1, 'limited_type' => 1)));
		$this->assertSame('after', $formatter->format(array('required' => 1, 'limited_type' => 2)));
		$this->assertSame('before', $formatter->format(array('required' => 1, 'limited_type' => 3)));
	}
}
