<?php
class Unit_Lib_Store_Api_Version_2_Resource_Products_Skus extends Interspire_IntegrationTest
{
	public function testCountEmpty()
	{
		$this->assertEquals(0, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('products' => 9999)));
		$this->assertEquals(0, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('product_hash' => 'abcde')));
	}

	public function testCountProductId()
	{
		//product id
		$data =array(
				'prodname' => 'test',
				'prodcatids' => '',
				'proddateadded' => time(),
				'prodlastmodified' => time(),
		);
		$productId = (int)$this->fixtures->InsertQuery('products', $data);

		$skuWithId1 =array(
				'id' => 9999,
				'product_id' => $productId,
		);
		$result = $this->createDummySku($skuWithId1);

		$skuWithId2 =array(
				'id' => 9998,
				'product_id' => $productId,
		);

		$result = $this->createDummySku($skuWithId2);

		$this->assertEquals(2, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('products' => $productId,)));
		$this->assertEquals(1, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('products' => $productId, 'skus' => 9998,)));

		//cleanup
		$this->fixtures->DeleteQuery('products', "WHERE productid = ".$productId);
		$this->deleteDummySku(9999);
		$this->deleteDummySku(9998);
	}

	public function testCountProductHash()
	{
		$hash = "lkjfpgosigfns";
		$skuWithHash1 =array(
				'id' => 9999,
				'product_hash' => $hash,
		);
		$result = $this->createDummySku($skuWithHash1);

		$skuWithHash2 =array(
				'id' => 9998,
				'product_hash' => $hash,
		);

		$result = $this->createDummySku($skuWithHash2);

		$skuWithHash3 =array(
				'id' => 9997,
				'product_hash' => $hash,
		);

		$result = $this->createDummySku($skuWithHash3);

		$this->assertEquals(3, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('product_hash' => $hash,)));
		$this->assertEquals(1, \DataModel_ApiFinder::findCount("Products_Skus", array(), array('product_hash' => $hash, 'skus' => 9997,)));

		//cleanup
		$this->deleteDummySku(9999);
		$this->deleteDummySku(9998);
		$this->deleteDummySku(9997);
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