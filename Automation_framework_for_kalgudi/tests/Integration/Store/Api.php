<?php

class Unit_Lib_Store_Api extends Interspire_IntegrationTest
{
	public function testGetVersions()
	{
		$api = new Store_Api();
		$versions = $api->getVersions();
		$this->assertNotEmpty($versions);
	}

	public function testGetLatestVersion()
	{
		$api = new Store_Api();
		$versions = $api->getVersions();
		$latestVersion = $api->getLatestVersion();
		$this->assertSame($latestVersion, end($versions));
	}

	public function testValidVersion()
	{
		$api = new Store_Api();
		$latestVersion = $api->getLatestVersion();
		$this->assertSame($api->validateVersion($latestVersion), $latestVersion);
	}

	/**
	* @expectedException Store_Api_Exception_Request_InvalidVersion
	*/
	public function testInvalidVersion()
	{
		$api = new Store_Api();
		$version = $api->getLatestVersion() + 1;
		$api->validateVersion($version);
	}

	public function invalidLimitAndPageDataProvider()
	{
		return array(
			array('0'),
			array('-1'),
			array('3.56'),
			array('foo'),
			array('1"LOL'),
		);
	}

	/**
	 * @dataProvider invalidLimitAndPageDataProvider
	 * @expectedException Store_Api_Exception_Request_InvalidPage
	 */
	public function testInvalidPage($page)
	{
		$request = new Interspire_Request(array(
			'page' => $page,
		));
		$api = new Store_Api();
		$api->executeRequest($request, 'Store_Api_Version_2_Resource_Products', '');
	}

	/**
	 * @dataProvider invalidLimitAndPageDataProvider
	 * @expectedException Store_Api_Exception_Request_InvalidLimit
	 */
	public function testInvalidLimit($limit)
	{
		$request = new Interspire_Request(array(
			'page' => '1',
			'limit' => $limit,
		));
		$api = new Store_Api();
		$api->executeRequest($request, 'Store_Api_Version_2_Resource_Products', '');
	}

	/**
	* @expectedException Store_Api_Exception_Request_LimitExceedsMaximum
	*/
	public function testLimitExceedsMaximum()
	{
		$request = new Interspire_Request(array(
			'page' => '1',
			'limit' => (string)(Store_Api::MAX_ITEMS_PER_PAGE + 1),
		));
		$api = new Store_Api();
		$api->executeRequest($request, 'Store_Api_Version_2_Resource_Products', '');
	}

	public function testCountUsageForThisRequestReturnsFalseForHawkAuth()
	{
		$api = new Store_Api();
		$api->setAuthType('Hawk');

		$this->assertFalse($api->countUsageForThisRequest());
	}

	public function testCountUsageForThisRequestReturnsTrueForBasicAuth()
	{
		$api = new Store_Api();
		$api->setAuthType('Basic');

		$this->assertTrue($api->countUsageForThisRequest());
	}

	public function testRequestShouldBypassThrottlingReturnsTrueForHawkAuth()
	{
		$api = new Store_Api();
		$api->setAuthType('Hawk');

		$this->assertTrue($api->requestShouldBypassThrottling());
	}

	public function testRequestShouldBypassThrottlingReturnsFalseForBasicAuth()
	{
		$api = new Store_Api();
		$api->setAuthType('Basic');

		$this->assertFalse($api->requestShouldBypassThrottling());
	}

	/**
	 * @expectedException Store_Api_Exception_Request_FieldNotWritable
	 */
	public function testParseInputFailsWhenWritingToReadOnlyField()
	{
		$headers = array('CONTENT_TYPE' => 'application/json', 'REQUEST_METHOD' => 'POST');
		$body = json_encode(array(
			'name' => 'Test Category', // required
			'parent_category_list' => array(1,2,3), // read only
		));
		$request = new Interspire_Request(array(), array(), array(), $headers, $body);

		Store_Api::parseInput($request, new Store_Api_Version_2_Resource_Categories());
	}

	public function testParseInputSucceedsWhenWritingToReadOnlyFieldWithInternalRequest()
	{
		$headers = array('CONTENT_TYPE' => 'application/json', 'REQUEST_METHOD' => 'POST');
		$body = json_encode(array(
			'name' => 'Test Category', // required
			'parent_category_list' => array(1,2,3), // read only
		));
		$request = new Interspire_Request(array(), array(), array(), $headers, $body);
		$request->setUserParam('internal_request', true);

		Store_Api::parseInput($request, new Store_Api_Version_2_Resource_Categories());
	}
}
