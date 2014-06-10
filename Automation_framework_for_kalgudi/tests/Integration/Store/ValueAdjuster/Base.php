<?php

require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

abstract class Unit_Lib_Store_ValueAdjuster_Base extends Interspire_IntegrationTest
{
	/**
	 * @var Store_ValueAdjuster_Absolute
	 */
	protected $_adjuster;

	abstract public function dataProviderAdjuster ();

	public function testGetPublicTypeNameReturnsString ()
	{
		$this->assertFalse(strpos($this->_adjuster->getPublicTypeName(), 'Store_ValueAdjuster_'));
	}

	/**
	 * @dataProvider dataProviderAdjuster
	 */
	public function testAdjuster ($original, $adjustment, $expected)
	{
		$this->assertEquals($expected, $this->_adjuster->setAdjustment($adjustment)->adjustValue($original), '', 0.0001);
	}
}
