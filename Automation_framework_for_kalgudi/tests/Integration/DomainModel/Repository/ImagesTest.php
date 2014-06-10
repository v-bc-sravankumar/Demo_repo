<?php

class Unit_DomainModel_Repository_ImagesTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
	}

	public function testFindMatchingNoResults()
	{
		$repository = new \Repository\Images();
		$filter = new \DomainModel\Query\Filter(
			array('searchterm' => 'foo')
		);
		$pager = new \DomainModel\Query\Pager(1, 12);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$results = $repository->findMatching($filter, $pager, $sorter);
		$this->assertEquals($results->count(), 0);
	}

	public function testFindMatchingWithResults()
	{
		$imageDir = new \ImageDir();
		$dirCount = $imageDir->CountDirItems();

		if($imageDir->CountDirItems() != 0){
			$images = $imageDir->GetImageDirFiles();

			$imageName = $images[0]['name'];

			$repository = new \Repository\Images();
			$filter = new \DomainModel\Query\Filter(
				array('searchterm' => $imageName)
			);
			$pager = new \DomainModel\Query\Pager(1, 12);
			$sorter = new \DomainModel\Query\Sorter('id', 'asc');
			$results = $repository->findMatching($filter, $pager, $sorter);
			$this->assertNotEquals($results->count(), 0);
		}
	}

	public function testFindMatchingFromProductsNoResults()
	{
		$repository = new \Repository\Images();
		$filter = new \DomainModel\Query\Filter(
				array('searchterm' => 'foo')
		);
		$pager = new \DomainModel\Query\Pager(1, 12);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$results = $repository->findMatchingFromProducts($filter, $pager, $sorter);
		$this->assertEquals($results->count(), 0);
	}

	public function testFindMatchingFromProductsWithResults()
	{
		$repository = new \Repository\Images();
		$filter = new \DomainModel\Query\Filter(
				array('searchterm' => 'apple')
		);
		$pager = new \DomainModel\Query\Pager(1, 12);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$results = $repository->findMatchingFromProducts($filter, $pager, $sorter);
		$this->assertNotEquals($results->count(), 0);
	}
}


