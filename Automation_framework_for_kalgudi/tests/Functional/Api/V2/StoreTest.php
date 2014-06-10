<?php

require_once __DIR__ . '/../TestCase.php';

class Functional_Api_V2_StoreTest extends Functional_Api_TestCase
{
	public function testGetJsonStoreInformation()
	{
		$this->get($this->makeUrl('/api/v2/store.json'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
		$this->assertText("order_email");
	}
	
	public function testGetXmlStoreInformation()
	{
		$this->get($this->makeUrl('/api/v2/store.xml'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("order_email");
	}
}