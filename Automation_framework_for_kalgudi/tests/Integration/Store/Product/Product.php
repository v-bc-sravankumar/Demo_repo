<?php
class Integration_Store_Product_Product extends Interspire_IntegrationTest
{
	public function testInventoryLimitReachedNoMailForEmptyProduct()
	{
		$product = $this->getMock('\Store\Product', array('sendInventoryLimitEmail'));

		$product->expects($this->never())
		->method('sendInventoryLimitEmail')
		->will($this->returnValue(true));

		$event = new stdClass();
		$product->inventoryLimitReached($event);

	}

	public function testInventoryLimitReachedMailSentForProduct()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodcurrentinv' => 5,
				'prodlowinv' => 6,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);
		$this->assertTrue(isId($productId));

		$product = $this->getMock('\Store\Product', array('sendInventoryLimitEmail'));

		$product->expects($this->once())
		->method('sendInventoryLimitEmail')
		->will($this->returnArgument(0));

		$event = new stdClass();
		$event->data = array(
				"id" =>$productId,
			);

		$data = $product->inventoryLimitReached($event);

		$this->assertEquals($data['prodName'], $prodName);
		$this->assertEquals($data['stock'], 5);
		$this->assertEquals($data['lowStockLevel'], 6);

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
	}

	public function testInventoryLimitReachedMailSentForProductWithCombinations()
	{
		$prodName = 'Product_'.mt_rand(1, PHP_INT_MAX);
		$product = array(
				'prodname' => $prodName,
				'prodcatids' => '',
				'prodcurrentinv' => 5,
				'prodlowinv' => 6,
		);

		$productId = $this->fixtures->InsertQuery('products', $product);
		$this->assertTrue(isId($productId));

		$combination = array(
				'product_id' => $productId,
				'sku' => $prodName.'_1',
				'stock_level' => 2,
				'low_stock_level' => 3,
		);

		$combinationId =  $this->fixtures->InsertQuery('product_attribute_combinations', $combination);

		$product = $this->getMock('\Store\Product', array('sendInventoryLimitEmail'));

		$product->expects($this->once())
		->method('sendInventoryLimitEmail')
		->will($this->returnArgument(0));

		$event = new stdClass();
		$event->data = array(
				"id" =>$productId,
				"combinationId" => $combinationId,
		);

		$data = $product->inventoryLimitReached($event);

		$this->assertEquals($data['prodName'], $prodName.' ()');
		$this->assertEquals($data['stock'], 2);
		$this->assertEquals($data['lowStockLevel'], 3);

		$this->fixtures->DeleteQuery('products', 'WHERE productid = ' . $productId);
		$this->fixtures->DeleteQuery('product_attribute_combinations', 'WHERE id = ' . $combinationId);
	}
}