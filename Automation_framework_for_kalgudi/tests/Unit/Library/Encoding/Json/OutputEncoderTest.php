<?php

namespace Unit\Library\Encoding\Json;

use Store_Api_OutputDataWrapper as OutputDataWrapper;
use Store_Api_OutputEncoder_Json as OutputEncoder;
use Store_Api_Version_2_Resource_Customers as CustomersResource;
use Store_Api_Version_2_Resource_Customers_Addresses as AddressesResource;
use Store_Json;
use Store_Config;

class OutputEncoderTest extends \PHPUnit_Framework_TestCase
{
	private $originalShopPathSSL;

	public function setUp()
	{
		$this->originalShopPathSSL = Store_Config::get('ShopPathSSL');
	}

	public function tearDown()
	{
		Store_Config::override('ShopPathSSL', $this->originalShopPathSSL);
	}

	private function getRequestObject($resourceUrlOverwrite = false)
	{
		$server = array();
		if ($resourceUrlOverwrite) {
			$server[\Store_Api::RESOURCE_URL_BASE_HEADER] = $resourceUrlOverwrite;
		}

		return new \Interspire_Request(array(), array(), array(), $server, '', array());
	}

	private function getExpectedJson($filename)
	{
		return file_get_contents(__DIR__ . '/' . $filename . '.json');
	}

	public function testEncodeBasic()
	{
		$data = array(
			'string' => 'foo',
			'numeric' => 1,
			'boolean' => true,
			'null' => null,
			'array' => array(
				1,
				2,
				3,
			),
			'object' => array(
				'field1' => 'foo',
				'field2' => 'bar',
				'sub-object' => array(
					'sub-field1' => 'hello',
					'sub-field2' => 'world',
				),
			),
		);

		$expected = $this->getExpectedJson(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper->setData($data);

		$encoder = new OutputEncoder();
		$json = $encoder->encode($wrapper, true);

		$decodedJson = Store_Json::decode($json);
		$decodedExpected = Store_Json::decode($expected);

		$this->assertEquals($decodedExpected, $decodedJson);
	}

	public function testEncodeComplex()
	{
		$addresses = array(
			array(
				'street_1' => '1 Long St',
				'city' => 'Sydney',
				'country' => 'Australia',
			),
			array(
				'street_1' => '5 Big Rd',
				'city' => 'Melbourne',
				'country' => 'Australia',
			),
		);

		$addressWrapper = new OutputDataWrapper();
		$addressWrapper
			->setData($addresses)
			->setSingularName('address')
			->setPluralName('addresses');

		$data = array(
			'first_name' => 'Bob',
			'last_name' => 'Smith',
			'addresses' => $addressWrapper,
		);

		$expected = $this->getExpectedJson(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper->setData($data);

		$encoder = new OutputEncoder();
		$json = $encoder->encode($wrapper, true);

		$decodedJson = Store_Json::decode($json);
		$decodedExpected = Store_Json::decode($expected);

		$this->assertEquals($decodedExpected, $decodedJson);
	}

	public function testEncodeResourceLink()
	{
		$customers = new CustomersResource();

		$data = array(
			array(
				'resource' => $customers->getRequestRoute(array(), 'json'),
			),
		);

		Store_Config::override('ShopPathSSL', 'https://store.example.com');

		$uri = json_encode(Store_Config::get('ShopPathSSL') . '/api/v2/customers.json');

		$expected = $this->getExpectedJson(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$encoder->setRequest($this->getRequestObject());
		$json = $encoder->encode($wrapper, true);

		$decodedJson = Store_Json::decode($json);
		$decodedExpected = Store_Json::decode($expected);

		$this->assertEquals($decodedExpected, $decodedJson);
	}

	public function testEncodeResourceLinkWithBaseUrlSetOnRequest()
	{
		$customers = new CustomersResource();

		$data = array(
			array(
				'resource' => $customers->getRequestRoute(array(), 'json'),
			),
		);

		Store_Config::override('ShopPathSSL', 'https://store.example.com');

		$expected = $this->getExpectedJson(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$encoder->setRequest($this->getRequestObject('https://api-proxy.example.org/stores/xyz123abc'));
		$json = $encoder->encode($wrapper, true);

		$decodedJson = Store_Json::decode($json);
		$decodedExpected = Store_Json::decode($expected);

		$this->assertEquals($decodedExpected, $decodedJson);
	}

	public function testEncodeResourceLinkWithExtraData()
	{
		$addresses = new AddressesResource();
		$addressRoute = $addresses->getRequestRoute(array('customers' => 5, 'addresses' => 2), 'xml');
		$addressRoute->setMetaData(array(
			'street' => '23 Long St',
		));

		$customers = new CustomersResource();
		$customerRoute = $customers->getRequestRoute(array(), 'xml');
		$customerRoute->setMetaData(array(
			'id' => 5,
			'name' => 'John Smith',
			'numbers' => array(1,2,3),
			'address' => $addressRoute,
		));

		$data = array(
			array(
				'resource' => $customerRoute,
			),
		);

		Store_Config::override('ShopPathSSL', 'https://store.example.com');

		$uri = json_encode(Store_Config::get('ShopPathSSL') . '/api/v2/customers.json');

		$expected = $this->getExpectedJson(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$encoder->setRequest($this->getRequestObject());
		$json = $encoder->encode($wrapper, true);

		$decodedJson = Store_Json::decode($json);
		$decodedExpected = Store_Json::decode($expected);

		$this->assertEquals($decodedExpected, $decodedJson);
	}
}
