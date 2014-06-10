<?php

require_once dirname(__FILE__) . '/Base.php';

class Unit_Lib_Store_ValueAdjuster_Relative extends Unit_Lib_Store_ValueAdjuster_Base
{
	public function setUp ()
	{
		parent::setUp();
		$this->_adjuster = new Store_ValueAdjuster_Relative;
	}

	public function dataProviderAdjuster ()
	{
		$data = array(
			// float-as-string test
			array('5', '100', 105),

			// zero-original-value tests
			array(0, 0, 0),
			array(0, 5, 5),
			array(0, -5, -5),

			// others
			array(5, 5, 10),
			array(5, -5, 0),
			array(-5, 5, 0),
			array(-5, -5, -10),

			// basic decimals
			array(1.23, 4.56, 5.79),
		);

		return $data;
	}
}
