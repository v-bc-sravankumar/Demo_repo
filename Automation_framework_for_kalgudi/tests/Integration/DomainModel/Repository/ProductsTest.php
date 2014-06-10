<?php

use Repository\Products;

class DomainModel_Repository_ProductsTest extends Interspire_IntegrationTest
{
	public function testDeleteRules()
	{
		$ruleIds = array(999, 998);
		$repository = new \Repository\Products();
		$this->assertEquals($repository->deleteRules($ruleIds), array());
	}

	public function testFindSkusEmpty()
	{
		$repository = new \Repository\Products();
		$pager = new DomainModel\Query\Pager(1, 1);
		$filter = new DomainModel\Query\Filter(array());
		$sorter = new DomainModel\Query\Sorter('id', 'asc');

		//product id
		$skus = $repository->findSkus(123, $filter, $pager, $sorter, false);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);

		$this->assertEquals(0, $skus->getTotalItems());
		$this->assertEquals(0, $skus->getTotalPages());

		//product hash
		$skus = $repository->findSkus('abcde', $filter, $pager, $sorter, true);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);

		$this->assertEquals(0, $skus->getTotalItems());
		$this->assertEquals(0, $skus->getTotalPages());
	}

	public function testFindSkusNotEmpty()
	{
		$repository = new \Repository\Products();

		//product id
		$data =array(
				'prodname' => 'test',
				'prodcatids' => '',
				'proddateadded' => time(),
				'prodlastmodified' => time(),
		);
		$productId = $this->fixtures->InsertQuery('products', $data);

		$skuWithId =array(
				'id' => 9999,
				'product_id' => (int)$productId,
		);

		$result = $this->createDummySku($skuWithId);

		$pager = new DomainModel\Query\Pager(1, 1);
		$filter = new DomainModel\Query\Filter(array());
		$sorter = new DomainModel\Query\Sorter('id', 'asc');


		$skus = $repository->findSkus($productId, $filter, $pager, $sorter, false);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(1, $skus->getTotalItems());
		$this->assertEquals(1, $skus->getTotalPages());

		//product hash
		$skuWithHash =array(
				'id' => 9998,
				'product_hash' => 'abcdef',
		);

		$result = $this->createDummySku($skuWithHash);

		$skus = $repository->findSkus('abcdef', $filter, $pager, $sorter, true);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(1, $skus->getTotalItems(), 'items');
		$this->assertEquals(1, $skus->getTotalPages(), 'pages');

		//cleanup
		$this->fixtures->DeleteQuery('products', "WHERE productid = ".$productId);
		$this->deleteDummySku(9999);
		$this->deleteDummySku(9998);
	}

	public function testFindSkusMultipleProductId()
	{
		$repository = new \Repository\Products();

		//product id
		$data =array(
				'prodname' => 'test',
				'prodcatids' => '',
				'proddateadded' => time(),
				'prodlastmodified' => time(),
		);
		$productId = (int)$this->fixtures->InsertQuery('products', $data);

		for ($i=900; $i<1000; $i++)
		{
			$skuWithId =array(
					'id' => $i,
					'product_id' => $productId,
			);
			$result = $this->createDummySku($skuWithId);
		}

		$pager = new DomainModel\Query\Pager(1, 100);
		$filter = new DomainModel\Query\Filter(array());
		$sorter = new DomainModel\Query\Sorter('id', 'asc');

		$skus = $repository->findSkus($productId, $filter, $pager, $sorter, false);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(100, $skus->getTotalItems());
		$this->assertEquals(1, $skus->getTotalPages());

		$pager = new DomainModel\Query\Pager(1, 50);
		$skus = $repository->findSkus($productId, $filter, $pager, $sorter, false);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(100, $skus->getTotalItems());
		$this->assertEquals(2, $skus->getTotalPages());

		//cleanup
		$this->fixtures->DeleteQuery('products', "WHERE productid = ".$productId);
		for ($i=900; $i<1000; $i++)
		{
			$result = $this->deleteDummySku($i);
		}
	}

	public function testFindSkusMultipleProductHash()
	{
		$repository = new \Repository\Products();

		$hash = "aldkjasdlkj";

		for ($i=900; $i<1000; $i++)
		{
			$skuWithHash =array(
				'id' => $i,
				'product_hash' => $hash,
			);
			$result = $this->createDummySku($skuWithHash);
		}

		$pager = new DomainModel\Query\Pager(3, 10);
		$filter = new DomainModel\Query\Filter(array());
		$sorter = new DomainModel\Query\Sorter('id', 'asc');

		$skus = $repository->findSkus($hash, $filter, $pager, $sorter, true);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(100, $skus->getTotalItems());
		$this->assertEquals(10, $skus->getTotalPages());

		$pager = new DomainModel\Query\Pager(1, 50);
		$skus = $repository->findSkus($hash, $filter, $pager, $sorter, true);
		$this->assertTrue($skus instanceof \DomainModel\PagedCollection);
		$this->assertEquals(100, $skus->getTotalItems());
		$this->assertEquals(2, $skus->getTotalPages());

		//cleanup
		for ($i=900; $i<1000; $i++)
		{
			$result = $this->deleteDummySku($i);
		}
	}

	public function testFilterExistentProducts()
	{
		$products = array(
			$this->createDummyProduct(),
			$this->createDummyProduct(),
			$this->createDummyProduct(),
		);

		$repo = new Products();

		$idsToCheck = array_merge($products, array(1000, 2000));

		$foundIds = $repo->filterExistentProducts($idsToCheck);

		$this->assertEquals($products, $foundIds);

		foreach ($products as $id) {
			$this->deleteDummyProduct($id);
		}
	}

	private function createDummyProduct()
	{
		$data = array(
			'prodname' => uniqid('test product '),
			'prodcatids' => '',
		);

		return $this->fixtures->InsertQuery('products', $data);
	}

	private function deleteDummyProduct($id)
	{
		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . (int)$id);
	}

	private function createDummySku($data)
	{
		$sku = array_merge(
			array(
					'id' => 9999,
					'product_id' => null,
					'product_hash' => '',
					'sku' => 2,
					'cost_price' => 10.00,
					'upc' => '',
					'stock_level' => 0,
					'low_stock_level' => 0,
					'bin_picking_number' => '',
			), $data);

		$result = $this->fixtures->InsertQuery('product_attribute_combinations', $sku);
	}

	private function deleteDummySku($id)
	{
		$this->fixtures->DeleteQuery('product_attribute_combinations', "WHERE id = ".$id);
	}
}
