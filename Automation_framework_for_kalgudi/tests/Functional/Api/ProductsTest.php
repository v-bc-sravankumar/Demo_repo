<?php

class Api_ProductsTest extends Interspire_FunctionalTest
{

	public function testGetCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
	}

	public function testGetCollectionCountAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("28");
	}

	public function testGetCollectionCountAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<products>");
		$this->assertText("<count>28</count>");
	}

	public function testGetCollectionWithPagingAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products?limit=10&page=2'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertNoText("Elgato EyeTV");
		$this->assertText("Office 2008 for Mac");
	}

	public function testGetCollectionWithPagingAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products?limit=10&page=2'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertNoText("Elgato EyeTV");
		$this->assertText("Office 2008 for Mac");
	}

	public function testUpdateWithNoContentTypeReturnsError()
	{
		$this->setHeader("Accept", "application/json");
		$body = '{"name":"My New Product","price":"99.99"}';
		$this->authenticate()->put($this->makeUrl('/api/v1/products/22'), $body);
		$this->assertStatus(400);
		$this->assertContentType("application/json");
		$this->assertText("An actual error message");
	}

	public function testUpdateWithInvalidFieldsAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/11'));
		$body = json_decode($this->getBody());
		$this->authenticate()->put($this->makeUrl('/api/v1/products/11'), json_encode($body));
		$this->assertStatus(400);
		$this->assertContentType("application/json");
		$this->assertText("The field 'date_modified' is invalid.");
	}

	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/22'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Logitech Pure-Fi Anywhere Speakers");
		$this->assertText("sample-product-logitech-pure-fi-anywhere-speakers");
	}

	public function testUpdateAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$body = '{"name":"My New Product","price":"99.99"}';
		$this->authenticate()->put($this->makeUrl('/api/v1/products/22'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("My New Product");
		$this->assertText("99.99");
	}

	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/11'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>[Sample Product] Office 2008 for Mac - Special Media Edition</name>");
		$this->assertText("<custom_url>/sample-product-office-2008-for-mac-special-media-edition/</custom_url>");
	}

	public function testUpdateAsXml()
	{
		$body = '<?xml version="1.0" encoding="UTF-8"?>
				 <product>
				 	<name>Hello Kitty Lunchbox</name>
				 </product>';

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->put($this->makeUrl('/api/v1/products/11'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Hello Kitty Lunchbox</name>");
	}

	public function testDeleteAsJson()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/products/22.json'));
		$this->assertStatus(204);
		$this->assertContentType("application/json");
	}

	/**
	 * @depends testDeleteAsJson
	 */
	public function testGetDeletedAsJson()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/products/22.json'));
		$this->assertStatus(404);
		$this->assertContentType("application/json");
	}

	public function testDeleteAsXml()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/products/11.xml'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

	/**
	 * @depends testDeleteAsXml
	 */
	public function testGetDeletedAsXml()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/products/22.xml'));
		$this->assertStatus(404);
		$this->assertContentType("application/xml");
	}

}