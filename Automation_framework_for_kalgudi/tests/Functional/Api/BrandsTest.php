<?php

class Api_BrandsTest extends Interspire_FunctionalTest
{

	public function testGetCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/brands'));
		print_r($this->getBody());
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/brands'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<brands>");
	}

	public function testGetCollectionCountAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/brands/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("9");
	}

	public function testGetCollectionCountAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/brands/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<brands>");
		$this->assertText("<count>9</count>");
	}

	public function testGetAsJson()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/brands/1.json'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Apple");
	}

	public function testGetAsXml()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/brands/1.xml'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<id>1</id>");
		$this->assertText("<name>Apple</name>");
	}

	public function testCreateAsJson()
	{
		$brand = new stdClass;
		$brand->name = "Blackberry";
		$body = json_encode($brand);

		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->post($this->makeUrl('/api/v1/brands.json'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/json");
		$this->assertText("Blackberry");
	}

	/**
	 * @depends testCreateAsJson
	 */
	public function testCreateWithDuplicateName()
	{
		$brand = new stdClass;
		$brand->name = "Blackberry";
		$body = json_encode($brand);

		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->post($this->makeUrl('/api/v1/brands.json'), $body);
		$this->assertStatus(400);
		$this->assertContentType("application/json");
		$this->assertText("The field 'name' is invalid."); // @todo nicer duplicate field message perhaps
	}

	public function testCreateWithInvalidField()
	{
		$brand = new stdClass;
		$brand->id = 9;
		$brand->name = "Palmpilot";
		$body = json_encode($brand);

		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->post($this->makeUrl('/api/v1/brands.json'), $body);
		$this->assertStatus(400);
		$this->assertContentType("application/json");
		$this->assertText("The field 'id' cannot be written to.");
	}

	public function testUpdateAsJson()
	{
		$brand = new stdClass;
		$brand->name = "Samsung";
		$body = json_encode($brand);

		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->put($this->makeUrl('/api/v1/brands/9.json'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("Samsung");
	}

	public function testCreateAsXml()
	{
		$body = '<?xml version="1.0" encoding="UTF-8"?>
				<brand>
  					<name>Dell</name>
				</brand>';

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->post($this->makeUrl('/api/v1/brands'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Dell</name>");
	}

	public function testUpdateAsXml()
	{
		$body = '<?xml version="1.0" encoding="UTF-8"?>
				 <brand>
				 	<name>Apple Computer</name>
				 </brand>';

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->put($this->makeUrl('/api/v1/brands/1'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Apple Computer</name>");
	}

	public function testDeleteAsJson()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/brands/2.json'));
		$this->assertStatus(204);
		$this->assertContentType("application/json");
	}

	public function testDeleteAsXml()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/brands/1.xml'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

	public function testDeleteCollection()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/brands'));
		$this->assertStatus(204);
	}

	/**
	 * @depends testDeleteCollection
	 */
	public function testGetEmptyCollection()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/brands'));
		$this->assertStatus(204);
	}

}