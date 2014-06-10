<?php

require_once __DIR__ . '/../TestCase.php';

class Functional_Api_V2_CustomersTest extends Functional_Api_TestCase
{

	public function testGetEmptyCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers'));

		$this->assertStatus(204);
		$this->assertContentType("application/json");
	}

	public function testGetEmptyCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

	public function testGetCollectionAsJson()
	{
		$this->fixtures->loadData('customers');
		$this->fixtures->loadData('shipping_addresses');

		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Alfonso.S.Moses@spambob.com");
	}

	public function testGetCollectionAsXml()
	{
		$this->fixtures->loadData('customers');
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<customers>");
		$this->assertText("<email>Alfonso.S.Moses@spambob.com</email>");
	}

	public function testGetCollectionCountAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("30");
	}

	public function testGetCollectionCountAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<customers>");
		$this->assertText("<count>30</count>");
	}

	public function testGetCollectionWithPagingAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers?limit=15&page=2'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertNoText("Jose.K.Cruz@mailinator.com");
		$this->assertText("Vernon.G.Durden@dodgit.com");
	}

	public function testGetCollectionWithPagingAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers?limit=15&page=2'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertNoText("<phone>301-283-2007</phone>");
		$this->assertText("<phone>715-487-6885</phone>");
	}

	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers/22'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Latonya");
		$this->assertText("386-624-2494");
	}

	/**
	 * @depends testGetAsJson
	 */
	public function testGetAddressesAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers/22/addresses'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("652 Spirit Drive");
		$this->assertText("Port Orange");
	}

	/**
	 * @depends testGetAddressesAsJson
	 */
	public function testGetAddressAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/customers/22/addresses/22'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("652 Spirit Drive");
		$this->assertText("Port Orange");
	}

	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers/22'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<first_name>Latonya</first_name>");
		$this->assertText("<phone>386-624-2494</phone>");
	}

	/**
	 * @depends testGetAsXml
	 */
	public function testGetAddressesAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers/22/addresses'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<street_1>652 Spirit Drive</street_1>");
		$this->assertText("<city>Port Orange</city>");
	}

	/**
	 * @depends testGetAddressesAsXml
	 */
	public function testGetAddressAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/customers/22/addresses/22'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<street_1>652 Spirit Drive</street_1>");
		$this->assertText("<city>Port Orange</city>");
	}
}