<?php

class Api_CategoriesTest extends Interspire_FunctionalTest
{

	public function testGetCollectionAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetCollectionAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<categories>");
		$this->assertText("<name>Shop Mac</name>");
		$this->assertText("<is_visible>true</is_visible>");
	}

	public function testGetCollectionCountAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("9");
	}

	public function testGetCollectionCountAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories/count'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<categories>");
		$this->assertText("<count>9</count>");
	}

	public function createAsJson()
	{
		$cat = new stdClass;
		$cat->name = "My Category";
		$body = json_encode($cat);

		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->post($this->makeUrl('/api/v1/categories'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/json");
		$this->assertText("My Category");
	}

	/**
	 * @depends createAsJson
	 */
	public function testGetAsJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories/10'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("My Category");
	}

	/**
	 * @depends testGetAsJson
	 */
	public function testUpdateAsJson()
	{
		$cat = new stdClass;
		$cat->name = "My Changed Category";
		$body = json_encode($cat);

		$this->setHeader("Accept", "application/json");
		$this->setHeader("Content-Type", "application/json");
		$this->authenticate()->put($this->makeUrl('/api/v1/categories/10'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("My Changed Category");
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

	public function createAsXml()
	{
		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				 <category>
				 	<name>New Category</name>
				 </category>";

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->post($this->makeUrl('/api/v1/categories'), $body);
		$this->assertStatus(201);
		$this->assertContentType("application/xml");
		$this->assertText("<name>New Category</name>");
		$this->assertText("<is_visible>true</is_visible>");
	}

	/**
	 * @depends createAsXml
	 */
	public function testGetAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->get($this->makeUrl('/api/v1/categories/11'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Shop Mac</name>");
		$this->assertText("<is_visible>true</is_visible>");
	}

	/**
	 * @depends testGetAsXml
	 */
	public function testUpdateAsXml()
	{
		$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				 <optionset>
				 	<name>Updated Category</name>
				 	<is_visible>false</is_visible>
				 </optionset>";

		$this->setHeader("Accept", "application/xml");
		$this->setHeader("Content-Type", "application/xml");
		$this->authenticate()->put($this->makeUrl('/api/v1/categories/14'), $body);
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<name>Updated Option Set</name>");
		$this->assertText("<is_visible>false</is_visible>");
	}

	/**
	 * @depends testUpdateAsXml
	 */
	public function testDeleteAsXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->authenticate()->delete($this->makeUrl('/api/v1/categories/14'));
		$this->assertStatus(204);
		$this->assertContentType("application/xml");
	}

	// @todo can't test this without isolating data fixtures as verifying resource
	// conflicts and removing products/categories will currently cause other tests
	// that depend on the sample data to fail
	/*public function testDeleteCollection()
	{
		$this->authenticate()->delete($this->makeUrl('/api/v1/categories'));
		print_r($this->getBody());
		$this->assertStatus(204);
	}*/

	/**
	 * @depends testDeleteCollection
	 */
	/*public function testGetEmptyCollection()
	{
		$this->authenticate()->get($this->makeUrl('/api/v1/categories'));
		$this->assertStatus(204);
	}*/
}