<?php

require_once dirname(__FILE__) . '/Base.php';

class Unit_Lib_Store_ValueAdjuster_Percentage extends Unit_Lib_Store_ValueAdjuster_Base
{
	public function setUp ()
	{
		parent::setUp();
		$this->_adjuster = new Store_ValueAdjuster_Percentage;
	}

	public function dataProviderAdjuster ()
	{
		$data = array(
			// float-as-string test
			array('5', '100', 10),

			// zero-original-value tests
			array(0, 0, 0),
			array(0, 5, 0),
			array(0, -5, 0),

			// others
			array(5, 5, 5.25),
			array(5, -5, 4.75),
			array(-5, 5, -5.25),
			array(-5, -5, -4.75),
		);

		return $data;
	}
}
