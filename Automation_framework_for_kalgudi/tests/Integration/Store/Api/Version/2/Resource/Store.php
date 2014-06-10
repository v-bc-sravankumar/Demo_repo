<?php

/**
* these tests can't currently use fixtures because products data exists in sample data and would be relied upon by
* product-based tests until fixtures can be properly introduced
*
* when fixtures are utilised properly change the tests here so they don't have to manually insert / remove dummy
* products
*/

class Unit_Lib_Store_Api_Version_2_Resource_Store extends Interspire_IntegrationTest
{

	private function _getResource()
	{
		return new Store_Api_Version_2_Resource_Store();
	}

	private function _getAction()
	{
		$request = new Interspire_Request(array(), array(), array(), array(
			'CONTENT_TYPE' => 'application/json'
		));
		return $this->_getResource()->getAction($request)->getData(true);
	}

	public function testCurrencySymbol()
	{
		Store_Config::override('CurrencyToken', 'x');
		$config = $this->_getAction();
		$this->assertEquals('x', $config['currency_symbol']);
	}

	public function testDecimalSeparator()
	{
		Store_Config::override('DecimalToken', 'x');
		$config = $this->_getAction();
		$this->assertEquals('x', $config['decimal_separator']);
	}

	public function testThousandsOperator()
	{
		Store_Config::override('ThousandsToken', 'x');
		$config = $this->_getAction();
		$this->assertEquals('x', $config['thousands_separator']);
	}

	public function testDecimalPlaces()
	{
		Store_Config::override('DecimalPlaces', 10);
		$config = $this->_getAction();
		$this->assertEquals(10, $config['decimal_places']);
	}

	public function testCurrencySymbolLocation()
	{
		Store_Config::override('CurrencyLocation', 'x');
		$config = $this->_getAction();
		$this->assertEquals('x', $config['currency_symbol_location']);
	}

	public function testPlanName()
	{
		Store_Config::override('PlanName', 'x');
		$config = $this->_getAction();
		$this->assertEquals('x', $config['plan_name']);
	}

}
