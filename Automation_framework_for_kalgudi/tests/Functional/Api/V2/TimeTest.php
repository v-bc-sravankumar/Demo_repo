<?php

require_once __DIR__ . '/../TestCase.php';

class Functional_Api_V2_TimeTest extends Functional_Api_TestCase
{

	public function testUnauthorizedWhenNotAuthenticated()
	{
		$this->authenticate('baduser', 'badpass');
		$this->get($this->makeUrl('/api/v2/time.json'));
		$this->assertStatus(401);
	}

	public function testUnsupportedFormatNotAcceptable()
	{
		$this->get($this->makeUrl('/api/v2/time.html'));
		$this->assertStatus(406);
	}

	public function testUnsupportedContentTypeNotAcceptable()
	{
		$this->setHeader("Accept", "text/csv");
		$this->get($this->makeUrl('/api/v2/time.html'));
		$this->assertStatus(406);
	}

	public function testAcceptJson()
	{
		$this->setHeader("Accept", "application/json");
		$this->get($this->makeUrl('/api/v2/time'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testAcceptXml()
	{
		$this->setHeader("Accept", "application/xml");
		$this->get($this->makeUrl('/api/v2/time'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
	}

	public function testGetAsJson()
	{
		$this->get($this->makeUrl('/api/v2/time.json'));
		$this->assertStatus(200);
		$this->assertContentType("application/json");
	}

	public function testGetAsXml()
	{
		$this->get($this->makeUrl('/api/v2/time.xml'));
		$this->assertStatus(200);
		$this->assertContentType("application/xml");
		$this->assertText("<time>");
		//$this->assertXml(array('tag'=>'time', 'content'=>'/[0-9]+/'));
	}

}