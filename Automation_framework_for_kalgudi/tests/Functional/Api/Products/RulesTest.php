<?php

class Api_Products_RulesTest extends Interspire_FunctionalTest
{

	public function testGetCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("is_purchasing_disabled");
		$this->assertText("Sorry, the 2.0 Ghz only comes with a 160 GB disk");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<rules>");
		$this->assertText("<purchasing_disabled_message>Sorry, the 2.0 Ghz only comes with a 160 GB disk");
	}

	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules/14'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("is_purchasing_disabled");
		$this->assertText("Sorry, the 2.0 Ghz only comes with a 160 GB disk");
	}

	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules/14'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<rule>");
		$this->assertText("<purchasing_disabled_message>Sorry, the 2.0 Ghz only comes with a 160 GB disk");
	}

	public function testGetConditionsCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules/conditions'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("attribute_value_id");
	}

	public function testGetConditionsCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/products/5/rules/conditions'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<conditions>");
		$this->assertText("<attribute_value_id>");
	}

}