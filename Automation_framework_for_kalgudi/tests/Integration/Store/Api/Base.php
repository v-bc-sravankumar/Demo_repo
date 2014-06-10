<?php

abstract class Unit_Lib_Store_Api_Base extends Interspire_IntegrationTest
{
	/**
	* @param bool $authenticated
	* @return Store_Api
	*/
	protected function _getMockApi ($authenticated)
	{
		// setup an api which is always authenticated for the purpose of these tests
		$api = $this->getMock('Store_Api', array('authenticate','checkPermission'));

		if ($authenticated) {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->returnValue((bool)$authenticated));
		} else {
			$api->expects($this->any())
				->method('authenticate')
				->will($this->throwException(new Store_Api_Exception_Authentication_CredentialsNotSupplied));
		}

		$api->expects($this->any())
			->method('checkPermission')
			->will($this->returnValue((bool)$authenticated));

		return $api;
	}

	/**
	* @param string $url
	* @param string $method
	* @param array $constructorParams
	* @return Interspire_Request
	*/
	protected function _getMockRequest ($url, $method = 'GET', $constructorParams = array())
	{
		$response = $this->getMock('Interspire_Response', array('sendResponse'));

		$response->expects($this->any())
			->method('sendResponse')
			->will($this->returnValue(true));

		/** @var Interspire_Request */
		$request = $this->getMock('Interspire_Request', array('getAbsolutePath', 'getMethod'), $constructorParams);
		$request->setUrlParser(new Store_UrlParser_RootApp);
		$request->setResponse($response);

		$request->expects($this->any())
			->method('getMethod')
			->will($this->returnValue($method));

		$request->expects($this->any())
			->method('getAbsolutePath')
			->will($this->returnValue($url));

		$this->assertSame($url, $request->getAppPath());

		return $request;
	}
}
