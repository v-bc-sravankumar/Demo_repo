<?php

class Unit_Products_Preorder extends Interspire_IntegrationTest
{
	public function createTestProduct ($prodpreorder = 0, $prodreleasedate = 0, $prodreleasedateremove = 0, $prodpreordermessage = '')
	{
		$this->removeTestProduct();

		$products = new Store_Product_Gateway();

		// this is based off a var_export of $Data before it hits Store_Product_Gateway->add in ISC_ADMIN_PRODUCT->_CommitProduct
		$data = array(
			'productid' => 0,
			'prodhash' => md5(uniqid('', true)),
			'prodname' => 'TEST_PREORDER',
			'prodcats' => array('2'),
			'prodtype' => '1',
			'prodcode' => '',
			'productVariationExisting' => '',
			'proddesc' => 'TEST_PREORDER',
			'prodpagetitle' => '',
			'prodsearchkeywords' => '',
			'prodavailability' => '',
			'prodprice' => '5.00',
			'prodcostprice' => '0.00',
			'prodretailprice' => '0.00',
			'prodsaleprice' => '0.00',
			'prodsortorder' => 0,
			'prodistaxable' => 1,
			'prodwrapoptions' => 0,
			'prodvisible' => 1,
			'prodfeatured' => 0,
			'prodvendorfeatured' => 0,
			'prodallowpurchases' => 1,
			'prodhideprice' => 0,
			'prodcallforpricinglabel' => '',
			'prodpreorder' => $prodpreorder,
			'prodreleasedate' => $prodreleasedate,
			'prodreleasedateremove' => $prodreleasedateremove,
			'prodpreordermessage' => $prodpreordermessage,
			'prodrelatedproducts' => -1,
			'prodinvtrack' => 0,
			'prodcurrentinv' => 0,
			'prodlowinv' => 0,
			'prodtags' => '',
			'prodweight' => '5.00',
			'prodwidth' => '5.00',
			'prodheight' => '5.00',
			'proddepth' => '5.00',
			'prodfixedshippingcost' => '0.00',
			'prodwarranty' => '',
			'prodmetakeywords' => '',
			'prodmetadesc' => '',
			'prodfreeshipping' => 0,
			'prodoptionsrequired' => 1,
			'prodbrandid' => 0,
			'prodlayoutfile' => 'product.html',
			'prodeventdaterequired' => 0,
			'prodeventdatefieldname' => 'Delivery Date',
			'prodeventdatelimited' => 0,
			'prodeventdatelimitedtype' => 0,
			'prodeventdatelimitedstartdate' => 0,
			'prodeventdatelimitedenddate' => 0,
			'prodvariationid' => 0,
			'prodvendorid' => 0,
			'prodmyobasset' => '',
			'prodmyobincome' => '',
			'prodmyobexpense' => '',
			'prodpeachtreegl' => '',
			'prodcondition' => 'New',
			'prodshowcondition' => 0,
			'product_videos' => array(),
			'product_images' => array(),
			'product_enable_optimizer' => 0,
			'prodminqty' => 0,
			'prodmaxqty' => 0,
		);

		$productId = (int)$products->add($data);
		$this->assertGreaterThan(0, $productId, $products->getError());

		return $productId;
	}

	public function removeTestProduct ()
	{
		$products = new Store_Product_Gateway();

		$productId = (int)$products->search(array('prodname' => 'TEST_PREORDER'));
		if ($productId) {
			$this->assertTrue($products->delete($productId), $products->getError());
		}
	}

	public function testPreOrderWithNoReleaseDate ()
	{
		$productId = $this->createTestProduct(1);
		$product = new ISC_PRODUCT($productId);

		$this->assertTrue($product->IsPreOrder());
		$this->assertEquals(0, $product->GetReleaseDate());
		$this->assertEquals('', $product->GetPreOrderMessage());
	}

	public function testPreOrderWithReleaseDate ()
	{
		$now = time();
		$release = time() + 86400;

		$productId = $this->createTestProduct(1, $release);
		$product = new ISC_PRODUCT($productId);

		$this->assertTrue($product->IsPreOrder());
		$this->assertEquals($release, $product->GetReleaseDate());
		$this->assertNotEquals('', $product->GetPreOrderMessage());
	}

	public function testAutoRemoveAndFutureReleaseDateKeepsPreOrderStatus ()
	{
		$now = time();
		$release = time() + 86400;

		$productId = $this->createTestProduct(1, $release, 1);
		$product = new ISC_PRODUCT($productId);

		$this->assertTrue($product->IsPreOrder());
		$this->assertEquals($release, $product->GetReleaseDate());
		$this->assertNotEquals('', $product->GetPreOrderMessage());
	}

	public function testAutoRemoveAndPastReleaseDateRemovesPreOrderStatus ()
	{
		$now = time();
		$release = time() - 86400;

		$productId = $this->createTestProduct(1, $release, 1);
		$product = new ISC_PRODUCT($productId);
		$this->assertFalse($product->IsPreOrder());
		$this->assertEquals(0, $product->GetReleaseDate());
		$this->assertEquals('', $product->GetPreOrderMessage());
	}

	public function testCustomPreOrderReleaseDateMessage ()
	{
		$release = time() + 86400;
		$message = uniqid('', true);
		$productId = $this->createTestProduct(1, $release, 0, $message);
		$product = new ISC_PRODUCT($productId);
		$this->assertEquals($message, $product->GetPreOrderMessage());
	}

	public function testPreOrderReleaseDateMessageWithoutReplacingPlaceholder ()
	{
		$release = time() + 86400;
		$message = 'TEST %%DATE%% TEST';
		$productId = $this->createTestProduct(1, $release, 0, $message);
		$product = new ISC_PRODUCT($productId);
		$this->assertEquals($message, $product->GetPreOrderMessage(false));
	}

	public function testDynamicPreOrderReleaseDateMessage ()
	{
		$storedRelease = time() + 86400;
		$storedMessage = '%%DATE%%';
		$dynamicRelease = time() + 172800;
		$expected = isc_date(GetConfig('DisplayDateFormat'), $dynamicRelease);

		$productId = $this->createTestProduct(1, $storedRelease, 0, $storedMessage);
		$product = new ISC_PRODUCT($productId);
		$this->assertEquals($expected, $product->GetPreOrderMessage(true, $dynamicRelease));
	}
}
