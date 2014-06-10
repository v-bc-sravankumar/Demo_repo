<?php

/**
 * @group remote
 */
class Unit_EmailIntegration_EmailMarketer_XmlApi extends Interspire_IntegrationTest
{
	const TEST_URL = 'http://beast/~gwilym.evans/bamboo/iem/xml.php';
	const TEST_USERNAME = 'admin';
	const TEST_USERTOKEN = 'f13c2ebede977e010e9129c90c7dbc2d92e716d0';
	const TEST_LIST_ID = 1;
	const TEST_VALID_EMAIL = 'gwilym.evans@interspire.com';

	/** @var Interspire_EmailIntegration_EmailMarketer */
	protected $_api;

	public function setUp ()
	{
		parent::setUp();
		$this->_api = new Interspire_EmailIntegration_EmailMarketer(self::TEST_URL, self::TEST_USERNAME, self::TEST_USERTOKEN);
	}

	public function tearDown ()
	{
		parent::tearDown();
		$this->_api = null;
	}

	/**
	* @param Interspire_EmailIntegration_EmailMarketer_XmlApiResponse $response
	* @param string $message
	*/
	public function assertXmlApiResponseSuccess ($response, $message = '')
	{
		if (!$message) {
			$message = 'Expected a successful XmlApi response';
		}

		$response->getResponseBody();

		$this->assertInstanceOf('Interspire_EmailIntegration_EmailMarketer_XmlApiResponse', $response, $message);
		$this->assertTrue($response->isSuccess(), $message . ' (' . $response->getErrorMessage() . ')');
	}

	public function assertXmlApiResponseError ($error, $response, $message = '')
	{
		if (!$message) {
			$message = 'Expected an unsuccessful XmlApi response';
		}

		$this->assertInstanceOf('Interspire_EmailIntegration_EmailMarketer_XmlApiResponse', $response, $message);
		$this->assertFalse($response->isSuccess(), $message . "\n\n" . $response->getResponseBody());
		$this->assertEquals($response->getErrorMessage(), $error, $message);
	}

	public function testConnectionFailureException ()
	{
		$this->setExpectedException('Interspire_EmailIntegration_EmailMarketer_Exception_ConnectionFailure');
		$api = new Interspire_EmailIntegration_EmailMarketer('http://invalid.hostname/', 'foo', 'bar');
		$api->xmlApiTest();
	}

	public function testInvalidResponseException ()
	{
		$this->setExpectedException('Interspire_EmailIntegration_EmailMarketer_Exception_InvalidResponse');
		$api = new Interspire_EmailIntegration_EmailMarketer('http://beast/~gwilym.evans/bamboo/iem/', 'foo', 'bar');
		$api->xmlApiTest();
	}

	public function testAddSubscriberToList ()
	{
		$this->_api->deleteSubscriber(self::TEST_LIST_ID, self::TEST_VALID_EMAIL);
		$this->assertXmlApiResponseSuccess($this->_api->addSubscriberToList(self::TEST_VALID_EMAIL, self::TEST_LIST_ID));
	}

// no point in testing this because IEM accepts subscriptions with invalid email addresses
//	public function testAddSubscriberToListWithInvalidEmail ()
//	{
//		$this->assertXmlApiResponseError('edit me', $this->_api->addSubscriberToList('derp', self::TEST_LIST_ID));
//	}
}
