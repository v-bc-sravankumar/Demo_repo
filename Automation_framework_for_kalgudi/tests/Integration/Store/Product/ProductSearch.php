<?php
class Integration_Store_Products_ProductSearch extends Interspire_IntegrationTest
{
	public function testSearchProductsForNotInventoryTrackedOutOfStock()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodinvtrack' => 0,
				'prodcurrentinv' => 0,
				'prodlowinv' => 3,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);

		$vars = array(
				"outOfStock" => 1,
		);

		$count = \Store_ProductSearch::countSearchResult($vars);
		$this->assertEquals($count, 0);

		$this->assertFalse($this->searchProduct($vars));

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
	}

	public function testSearchProductsForInventoryTrackedOutOfStock()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodinvtrack' => 1,
				'prodcurrentinv' => 0,
				'prodlowinv' => 3,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);

		$vars = array(
					"outOfStock" => 1,
				);

		$count = \Store_ProductSearch::countSearchResult($vars);
		$this->assertEquals($count, 1);

		$product = $this->searchProduct($vars);
		$this->assertEquals($product['name'], $prodName);

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
	}

	public function testSearchProductsForInventoryTrackedNotOutOfStock()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodinvtrack' => 1,
				'prodcurrentinv' => 10,
				'prodlowinv' => 3,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);

		$vars = array(
				"outOfStock" => 1,
		);

		$count = \Store_ProductSearch::countSearchResult($vars);
		$this->assertEquals($count, 0);

		$this->assertFalse($this->searchProduct($vars));

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
	}

	public function testSearchProductsForOptionTrackedOutOfStock()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodinvtrack' => 2,
				'prodcurrentinv' => 10,
				'prodlowinv' => 3,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);

		$combination = array(
				'product_id' => $productId,
				'sku' => $prodName.'_1',
				'stock_level' => 0,
				'low_stock_level' => 3,
		);

		$combinationId =  $this->fixtures->InsertQuery('product_attribute_combinations', $combination);

		$vars = array(
				"outOfStock" => 1,
		);

		$count = \Store_ProductSearch::countSearchResult($vars);
		$this->assertEquals($count, 1);

		$product = $this->searchProduct($vars);
		$this->assertEquals($product['name'], $prodName);

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
		$this->fixtures->DeleteQuery('product_attribute_combinations', 'WHERE id = ' . $combinationId);
	}

	public function testSearchProductsForOptionTrackedNotOutOfStock()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodinvtrack' => 2,
				'prodcurrentinv' => 10,
				'prodlowinv' => 3,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);

		$combination = array(
				'product_id' => $productId,
				'sku' => $prodName.'_1',
				'stock_level' => 1,
				'low_stock_level' => 3,
		);

		$combinationId =  $this->fixtures->InsertQuery('product_attribute_combinations', $combination);

		$vars = array(
				"outOfStock" => 1,
		);

		$count = \Store_ProductSearch::countSearchResult($vars);
		$this->assertEquals($count, 0);

		$this->assertFalse($this->searchProduct($vars));

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
		$this->fixtures->DeleteQuery('product_attribute_combinations', 'WHERE id = ' . $combinationId);
	}

	private function searchProduct($vars)
	{
		$pager = new \DomainModel\Query\Pager(1, 20);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');

		$query = \Store_ProductSearch::searchProducts($vars, $pager, $sorter);
		$iterator = $query->getIterator();
		$product = $iterator->first();

		return $product;
	}
}