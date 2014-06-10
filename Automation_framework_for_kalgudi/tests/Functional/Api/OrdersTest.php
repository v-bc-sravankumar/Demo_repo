<?php

class Api_OrdersTest extends Interspire_FunctionalTest
{

	public function testGetStatusesAsJson()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/orderstatuses.json'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Pending");
		$this->assertText("Cancelled");
	}

	public function testGetStatusesAsXml()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/orderstatuses.xml'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<orderstatuses>");
		$this->assertText("<name>Pending</name>");
	}

	public function testGetEmptyListAsJson()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/orders.json'));
		$this->assertStatus(204);
		$this->assertContentType("application/json");
	}

	public function testGetEmptyListAsXml()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/orders.xml'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

	public function testGetCollectionAsJson()
	{
		$this->fixtures->loadData('customers');
		$this->fixtures->loadData('orders');
		$this->fixtures->loadData('order_products');

		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/orders'));
		print_r($this->getBody());
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Alfonso.S.Moses@spambob.com");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/orders'));
		print_r($this->getBody());
		$this->assertStatus(200);
		$this->assertContentType("application/xml");

		$this->assertText("<customers>");
		$this->assertText("<email>Alfonso.S.Moses@spambob.com</email>");
	}

	public function testGetCollectionCountAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/orders/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("30");
	}

	public function testGetCollectionCountAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/orders/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<orders>");
		$this->assertText("<count>30</count>");
	}

}