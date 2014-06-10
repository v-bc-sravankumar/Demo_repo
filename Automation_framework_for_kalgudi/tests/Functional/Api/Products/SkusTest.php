<?php

class Api_Products_SkusTest extends Interspire_FunctionalTest
{

	public function testGetCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("MB-1");
		$this->assertText("MB-2");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<skus>");
		$this->assertText("<sku>MB-1</sku>");
		$this->assertText("<sku>MB-2</sku>");
	}

	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus/1'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("MB-1");
	}

	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus/1'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<sku>");
		$this->assertText("<sku>MB-1</sku>");
	}

	public function testGetOptionsCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus/1/options'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("sku_id");
	}

	public function testGetOptionsCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/skus/1/options'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<options>");
		$this->assertText("<sku_id>1</sku_id>");
	}

}