<?php

namespace Unit\Library\Encoding\Xml;

use Store_Api_OutputDataWrapper as OutputDataWrapper;
use Store_Api_OutputEncoder_Xml as OutputEncoder;
use Store_Api_Version_2_Resource_Customers as CustomersResource;
use Store_Api_Version_2_Resource_Customers_Addresses as AddressesResource;
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

	private function getExpectedXml($filename)
	{
		return file_get_contents(__DIR__ . '/' . $filename . '.xml');
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

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('object')
			->setPluralName('object');

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
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

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
	}

	public function testEncodeResourceLink()
	{
		$customers = new CustomersResource();

		$data = array(
			array(
				'resource' => $customers->getRequestRoute(array(), 'xml'),
			),
		);

		Store_Config::override('ShopPathSSL', 'https://store.example.com');

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
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

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('customer')
			->setPluralName('customers');

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
	}

	public function testEncodeNestedCollectionWithSingularInflection()
	{
		$data = array(
			array(
				"id" => 999,
				"name" => 'NineNineNine',
				"objects" => array(
					array("id" => 1, "type" => "a"),
					array("id" => 2, "type" => "b"),
				),
			),
		);

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('element')
			->setPluralName('elements')
			->setInflectSingular();

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
	}

	public function testEncodeNestedCollectionDeepWithSingularInflection()
	{
		$data = array(
			array(
				"id" => 999,
				"name" => 'NineNineNine',
				"addresses" => array(
					array("id" => 1, "type" => "a"),
					array("id" => 2, "type" => "b"),
				),
				"objects" => array(
					array(
						"id" => 1,
						"type" => "a",
						"states" => array(
							array("id" => 0),
							array("id" => 1),
							array("id" => 2),
						)
					),
					array(
						"id" => 2,
						"type" => "b",
						"states" => array(
							array("id" => 3),
							array("id" => 4),
							array("id" => 5),
						)
					),
				),
			),
		);

		$expected = $this->getExpectedXml(__FUNCTION__);

		$wrapper = new OutputDataWrapper();
		$wrapper
			->setData($data)
			->setSingularName('element')
			->setPluralName('elements')
			->setInflectSingular();

		$encoder = new OutputEncoder();
		$xml = $encoder->encode($wrapper, true, true);

		$this->assertXmlStringEqualsXmlString($expected, $xml);
	}

}
