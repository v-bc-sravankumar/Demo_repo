<?php

// autoloader doesn't pick up the isc_* functions
require_once __DIR__ . '/../../../lib/multibyte.php';
require_once __DIR__ . '/../../../lib/general.php';

class ApiCallbackTest extends PHPUnit_Framework_TestCase
{
	public function testOldResourcePathPointsToCorrectController()
	{
		$callback = Store_RequestRouter_StoreApi::getCallbackForRequest(2, array('orderstatuses'), 'GET');
		$this->assertEquals(array('Store_Api_Version_2_Resource_Orderstatuses', 'getAction'), $callback);
	}

	public function testNewResourcePathPointsToCorrectController()
	{
		$callback = Store_RequestRouter_StoreApi::getCallbackForRequest(2, array('order_statuses'), 'GET');
		$this->assertEquals(array('Store_Api_Version_2_Resource_Orderstatuses', 'getAction'), $callback);
	}

	public function testOldSubResourcePathPointsToCorrectController()
	{
		$callback = Store_RequestRouter_StoreApi::getCallbackForRequest(2, array('products', 'configurablefields'), 'GET');
		$this->assertEquals(array('Store_Api_Version_2_Resource_Products_Configurablefields', 'getAction'), $callback);
	}

	public function testNewSubResourcePathPointsToCorrectController()
	{
		$callback = Store_RequestRouter_StoreApi::getCallbackForRequest(2, array('products', 'configurable_fields'), 'GET');
		$this->assertEquals(array('Store_Api_Version_2_Resource_Products_Configurablefields', 'getAction'), $callback);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 */
	public function testMissingResourceThrowsNotFoundException()
	{
		$callback = Store_RequestRouter_StoreApi::getCallbackForRequest(2, array('payments', 'offline_providers'), 'GET');
	}
}
