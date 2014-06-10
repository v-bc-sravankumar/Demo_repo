<?php

class Api_OptionsetsTest extends Interspire_FunctionalTest
{

	public function testGetListAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetListAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
	}

	public function testGetAttributesListAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/attributes'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetAttributesListAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/attributes'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
	}

	public function testGetRulesListAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/rules'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetRulesListAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/rules'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
	}

	public function testCreateAsJson()
	{
		$optionset = new stdClass;
		$optionset->name = "My Option Set";
		$body = json_encode($optionset);

		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->post($this->makeUrl('/api/v1/optionsets'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/json");
		$this->assertText("My Option Set");
	}

	/**
	 * @depends testCreateAsJson
	 */
	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/13'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("My Option Set");
	}

	/**
	 * @depends testGetAsJson
	 */
	public function testUpdateAsJson()
	{
		$optionset = new stdClass;
		$optionset->name = "My Option Set With A Changed Name";
		$body = json_encode($optionset);

		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->put($this->makeUrl('/api/v1/optionsets/13'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("My Option Set With A Changed Name");
	}

	/**
	 * @depends testUpdateAsJson
	 */
	public function testDeleteAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->delete($this->makeUrl('/api/v1/optionsets/13'));
		$this->assertStatus(204);
		$this->assertContentType("application/json");
	}

	public function testCreateAsXml()
	{
		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				 <optionset>
				 	<name>New Option Set</name>
				 </optionset>";

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->post($this->makeUrl('/api/v1/optionsets'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/xml");
		$this->assertText("<name>New Option Set</name>");
	}

	/**
	 * @depends testCreateAsXml
	 */
	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/optionsets/14'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("New Option Set");
	}

	/**
	 * @depends testGetAsXml
	 */
	public function testUpdateAsXml()
	{
		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				 <optionset>
				 	<name>Updated Option Set</name>
				 </optionset>";

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->put($this->makeUrl('/api/v1/optionsets/14'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Updated Option Set</name>");
	}

	/**
	 * @depends testUpdateAsXml
	 */
	public function testDeleteAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->delete($this->makeUrl('/api/v1/optionsets/14'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

}