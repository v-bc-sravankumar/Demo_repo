<?php

namespace Unit\Application\Model\Platform;

use Platform\Domain;

class PrimaryDomainTest extends \PHPUnit_Framework_TestCase
{
	private function getDomainByIdResponse()
	{
		return json_decode(file_get_contents(__DIR__ . '/domain.json'));
	}

	private function getDomainByNameResponse()
	{
		return json_decode(file_get_contents(__DIR__ . '/search.json'));
	}

	public function setUp()
	{
		// configure the DNS service with a stub JSON response
		$this->primaryDomain = "bicyclestore.com.au";

        $this->mockService = $this->getMockBuilder('\PowerDns\Api\Connection')
            					  ->disableOriginalConstructor()
                                  ->getMock();

		$this->mockService->expects($this->at(0))
		  				  ->method('get')
		  				  ->with('/domains?name=' . $this->primaryDomain)
		  				  ->will($this->returnValue($this->getDomainByNameResponse()));

		$this->mockService->expects($this->at(1))
		  				  ->method('get')
		  				  ->with('/domains/5000')
		  				  ->will($this->returnValue($this->getDomainByIdResponse()));

		\PowerDns\Api\Connection::configure($this->mockService);
	}

	public function testGetName()
	{
		$domain = new Domain($this->primaryDomain);

		$this->assertEquals($this->primaryDomain, $domain->getName());
	}

	public function testGetSoaRecord()
	{
		$domain = new Domain($this->primaryDomain);
		$soa = $domain->getSoaRecord();
		$this->assertEquals("10800", $soa->refresh);
		$this->assertEquals("3600", $soa->retry);
	}

	public function testGetRecords()
	{
		$domain = new Domain($this->primaryDomain);
		$records = $domain->getRecords();

		$this->assertInternalType("array", $records);
		$this->assertEquals(8, count($records));
		$this->assertEquals("ns1.syd1bc.bigcommerce.net", $records[0]->content);
	}

	public function testGetRecordsByType()
	{
		$domain = new Domain($this->primaryDomain);
		$records = $domain->getRecordsByType(array('A'));

		$this->assertInternalType("array", $records);
		$this->assertEquals(3, count($records));
		$this->assertEquals("10.1.2.48", $records[0]->content);
	}
}