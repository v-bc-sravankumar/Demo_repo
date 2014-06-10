<?php

use Repository\Accounting;
use DomainModel\Query\Pager;
use DomainModel\PagedCollection;

class Unit_Repositories_AccountingTest extends PHPUnit_Framework_TestCase
{
    private $repository;

    public function setUp()
    {
        $packages = array(
            'package-1' => array(
                "recommended" => "connector-1",
                "others" => array(
                    "connector-2",
                ),
            ),
            'package-2' => array(
                "recommended" => "connector-2",
                "others" => array(),
            ),
        );
        $connectors = array (
                "connector-1" => array(
                        "title" => "test title 1",
                        "description" => "test description 1",
                        "bc_integrated" => false,
                        "website" => "",
                ),
                "connector-2" => array(
                        "title" => "test title 2",
                        "description" => "test description 2",
                        "bc_integrated" => false,
                        "website" => "",
                ),
        );


        $this->repository = new Accounting();
        $this->repository->setPackages($packages);
        $this->repository->setConnectors($connectors);

    }
    public function testListPackages()
    {
        $result = $this->repository->findMatchingPackages(null, new Pager(1, 1), null);

        // make sure we get a paged collection
        $this->assertTrue($result instanceof PagedCollection);
        $this->assertEquals(2, $result->getTotalItems());
        $this->assertEquals(2, $result->getTotalPages());

        // make sure we have those attributes for front-end to correctly render
        $this->assertArrayHasKey('icon', $result->current());
        $this->assertArrayHasKey('connectors', $result->current());
    }

    public function testFindPackage()
    {
        $result = $this->repository->findPackage('package-1');

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayHasKey('connectors', $result);
        $this->assertEquals(2, $result['connectors_count']);
    }

    public function testListConnectorsForPackage()
    {
        $result = $this->repository->findMatchingConnectors(null, new Pager(1, 1), null, "package-1");

        // make sure we get a paged collection
        $this->assertTrue($result instanceof PagedCollection);
        $this->assertEquals(2, $result->getTotalItems());
        $this->assertEquals(2, $result->getTotalPages());

        // make sure we have those attributes for front-end to correctly render
        $this->assertArrayHasKey('icon', $result->current());
        $this->assertArrayHasKey('path', $result->current());
    }

    public function testGetRecommendedConnector()
    {
        $result = $this->repository->findRecommendedConnectorName('package 1');
        $this->assertEquals('connector-1', $result);
    }

    public function testFindConnector()
    {
        $result = $this->repository->findConnector('package-1', 'connector-2');

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('is_recommended', $result);
        $this->assertEquals($result['title'], 'test title 2');
    }
}
