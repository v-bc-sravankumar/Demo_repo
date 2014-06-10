<?php

namespace Unit\Library\Encoding;

use Store_Api_OutputDataWrapper;

class OutputDataWrapperTest extends \PHPUnit_Framework_TestCase
{
	public function testDataWrapperSmokeTest()
	{
		$wrapper = new Store_Api_OutputDataWrapper();

		$wrapper->setSingularName('customer');
		$this->assertEquals('customer', $wrapper->getSingularName());

		$wrapper->setPluralName('customers');
		$this->assertEquals('customers', $wrapper->getPluralName());

		$wrapper->setData(array(1,2));
		$this->assertEquals(array(1,2), $wrapper->getData());

		$this->assertEquals('customers', $wrapper->getName());

		$wrapper->setData(array(1));
		$this->assertEquals(1, $wrapper->getData(true));

		$this->assertEquals('customer', $wrapper->getName());

		$wrapper->forcePlural(true);
		$this->assertEquals('customers', $wrapper->getName());
	}
}