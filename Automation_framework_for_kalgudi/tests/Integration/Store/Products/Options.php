<?php

class Unit_Products_Options extends Interspire_IntegrationTest
{
	protected $productId;

	protected $productTypeId;

	public function setUp()
	{
		parent::setUp();

		$this->fixtures->loadData('options');

		$this->productTypeId = 1;
		$this->productId = $this->createTestProduct();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function createTestProduct()
	{
		$this->removeTestProduct();

		$products = new Store_Product_Gateway();

		// this is based off a var_export of $Data before it hits Store_Product_Gateway->add in ISC_ADMIN_PRODUCT->_CommitProduct
		$data = array(
			'productid' => 0,
			'prodhash' => md5(uniqid('', true)),
			'prodname' => 'TEST_OPTIONS',
			'prodcats' => array('2'),
			'prodtype' => '1',
			'prodcode' => '',
			'productVariationExisting' => '',
			'proddesc' => 'TEST_OPTIONS',
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
			'prodpreorder' => 0,
			'prodreleasedate' => 0,
			'prodreleasedateremove' => 0,
			'prodpreordermessage' => '',
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
			'prodeventdatefieldname' => '',
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

	public function removeTestProduct()
	{
		$products = new Store_Product_Gateway();

		$productId = (int)$products->search(array('prodname' => 'TEST_OPTIONS'));
		if ($productId) {
			$this->assertTrue($products->delete($productId), $products->getError());
		}
	}

	/**	*
	*
	* @param mixed $productId
	* @param mixed $productTypeId
	* @return array
	*/
	public function doRequest($action, $data = array())
	{
		$this->assertTrue($this->login(), "doRequest: login failed");

		$productId = $this->productId;
		$productTypeId = $this->productTypeId;

		$postData = array(
			'productHash' 		=> $productId,
			'loadFromProductId' => false,
			'optionSetId' 		=> $productTypeId,
			'trackInventory' 	=> 0,
		);

		$postData += $data;

		$request = new Interspire_Request(null,
			$postData,
			array(
				'w'	=> $action,
			)
		);

		$remote = new ISC_ADMIN_REMOTE_PRODUCT_OPTIONS();
		$result = $remote->HandleToDo($request, true);

		$this->assertTrue($this->logout(), "doRequest: logout failed");

		return $result;
	}

	public function testLoadOptionSetForProduct()
	{
		$result = $this->doRequest('loadOptionSetForProduct');
		$this->assertSame(1, $result['success']);
	}

	public function testGetSKUBuilderTab()
	{
		$result = $this->doRequest('getSKUBuilderTab');
		$this->assertTrue(!empty($result['skuBuilderTab']));
	}

	public function testSaveSKU()
	{
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => 2,
			2 => 6,
			3 => 13,
		);

		$skuName = 'TEST SKU ' . rand(1000000, 9999999);

		$result = $this->doRequest('saveSKU',
			array(
				'sku'     => $skuName,
				'upc'     => '1234567890',
				'options' => $options,
			)
		);

		$this->assertSame(1, $result['success'], "saveSKU failed: " . print_r($result, true));

		// test duplicate sku
		$result = $this->doRequest('saveSKU',
			array(
				'sku'     => $skuName,
				'upc'     => '1234567890',
				'options' => $options,
			)
		);

		$this->assertSame(0, $result['success'], "saveSKU should have failed for duplicate sku but didn't");

		$skuName = 'TEST SKU ' . rand(1000000, 9999999);

		// test duplicate options
		$result = $this->doRequest('saveSKU',
			array(
				'sku' 		=> $skuName,
				'upc' 		=> '1234567890',
				'options'	=> $options,
			)
		);

		$this->assertSame(0, $result['success'], "saveSKU should have failed for duplicate options but didn't");
	}

	public function testGetSKUDetails()
	{
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => 2,
			2 => 6,
			3 => 13,
		);

		$skuName = 'TEST SKU ' . rand(1000000, 9999999);

		$result = $this->doRequest('saveSKU',
			array(
				'sku' 		=> $skuName,
				'upc' 		=> '1234567890',
				'options'	=> $options,
			)
		);

		$this->assertTrue(is_array($result), "doRequest result is not an array: " . print_r($result, true));
		$this->assertSame(1, $result['success'], "doRequest result was not successful: " . print_r($result, true));

		$combinationId = $result['savedSKUId'];

		$result = $this->doRequest('getSKUDetails', array('combinationId' => $combinationId));
	}

	public function testGetRulesTab()
	{
		$result = $this->doRequest('getRulesTab');
		$this->assertTrue(!empty($result['rulesTab']));

	}

	public function testGetSKUGrid()
	{
		$result = $this->doRequest('getSKUGrid');
		$this->assertTrue(!empty($result['skuTable']));
	}

	public function testBulkDeleteSKU()
	{
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => 2,
			2 => 6,
			3 => 13,
		);

		$skuName = 'TEST SKU ' . rand(1000000, 9999999);

		$result = $this->doRequest('saveSKU',
			array(
				'sku' 		=> $skuName,
				'upc' 		=> '1234567890',
				'options'	=> $options,
			)
		);

		$combinationId = $result['savedSKUId'];
		$this->assertGreaterThan(0, $combinationId, "saveSKU failed");

		$result = $this->doRequest('bulkSKUDelete', array(
			'skus' => array($combinationId)
		));

		$this->assertTrue(!empty($result['skuTable']));
	}

	public function testSaveRule()
	{
		$this->markTestIncomplete('this needs updating for new RULE endpoints');
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => array(2),
			2 => array(6),
			3 => array(13),
		);

		$result = $this->doRequest('saveRule',
			array(
				'enabled' 	=> 1,
				'stop' 		=> 0,
				'purchasing_disabled'	=> 0,
				'adjust_price'	=> 1,
				'price_adjuster' => 'Relative',
				'price_adjustment' => 5,
				'price_adjustment_direction' => 0,
				'adjust_weight'	=> 1,
				'weight_adjuster' => 'Relative',
				'weight_adjustment' => 6,
				'weight_adjustment_direction' => 0,
				'attribute_values'	=> $options,
			)
		);

		$this->assertSame(1, $result['success'], 'saveRule failed: ' . print_r($result, true));
	}

	public function testEditRule()
	{
		$this->markTestIncomplete('this needs updating for new RULE endpoints');
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => array(2),
			2 => array(6),
			3 => array(13),
		);

		$result = $this->doRequest('saveRule',
			array(
				'enabled' 	=> 1,
				'stop' 		=> 0,
				'purchasing_disabled'	=> 0,
				'adjust_price'	=> 1,
				'price_adjuster' => 'Relative',
				'price_adjustment' => 5,
				'price_adjustment_direction' => 0,
				'adjust_weight'	=> 1,
				'weight_adjuster' => 'Relative',
				'weight_adjustment' => 6,
				'weight_adjustment_direction' => 0,
				'attribute_values'	=> $options,
			)
		);

		$this->assertSame(1, $result['success'], "saveRule (insert) failed");

		$ruleId = $result['rule'];

		$options = array(
			1 => array(2),
			2 => array(4),
			3 => array(13),
		);

		$result = $this->doRequest('saveRule',
			array(
				'rule'		=> $ruleId,
				'enabled' 	=> 1,
				'stop' 		=> 0,
				'purchasing_disabled'	=> 0,
				'adjust_price'	=> 1,
				'price_adjuster' => 'Relative',
				'price_adjustment' => 10,
				'price_adjustment_direction' => 1,
				'adjust_weight'	=> 1,
				'weight_adjuster' => 'Relative',
				'weight_adjustment' => 6,
				'weight_adjustment_direction' => 0,
				'attribute_values'	=> $options,
			)
		);

		$this->assertSame(1, $result['success'], "saveRule (update) failed");

		$rule = new Store_Product_Attribute_Rule;
		$this->assertTrue($rule->load($ruleId), "load failed");

		$this->assertEquals(10, $rule->getPriceAdjuster()->getAdjustment(), "adjustment value mismatch");
	}

	public function testBulkRuleDelete()
	{
		$this->markTestIncomplete('this needs updating for new RULE endpoints');
		$result = $this->doRequest('loadOptionSetForProduct');

		$options = array(
			1 => array(2),
			2 => array(6),
			3 => array(13),
		);

		$result = $this->doRequest('saveRule',
			array(
				'enabled' 	=> 1,
				'stop' 		=> 0,
				'purchasing_disabled'	=> 0,
				'adjust_price'	=> 1,
				'price_adjuster' => 'Relative',
				'price_adjustment' => 5,
				'price_adjustment_direction' => 0,
				'adjust_weight'	=> 1,
				'weight_adjuster' => 'Relative',
				'weight_adjustment' => 6,
				'weight_adjustment_direction' => 0,
				'attribute_values'	=> $options,
			)
		);

		$ruleId = $result['rule'];
		$this->assertGreaterThan(0, $ruleId, "rule id mismatch");

		$result = $this->doRequest('bulkRuleDelete',
			array(
				'rules' => array($ruleId),
			)
		);

		$this->assertSame(1, $result['success']);

		$rule = new Store_Product_Attribute_Rule;
		$this->assertFalse($rule->load($ruleId), "load worked, but should have failed");
	}

	protected function _createTestRule ()
	{
		$options = array(
			1 => array(2),
			2 => array(6),
			3 => array(13),
		);

		$adjustment = rand(1,9999);

		$result = $this->doRequest('saveRule', array(
			'enabled' 	=> 1,
			'stop' 		=> 0,
			'purchasing_disabled'	=> 0,
			'adjust_price'	=> 1,
			'price_adjuster' => 'Relative',
			'price_adjustment' => $adjustment,
			'price_adjustment_direction' => 1,
			'adjust_weight'	=> 1,
			'weight_adjuster' => 'Relative',
			'weight_adjustment' => 6,
			'weight_adjustment_direction' => 1,
			'attribute_values'	=> $options,
		));

		$ruleId = $result['rule'];
		$this->assertGreaterThan(0, $ruleId);

		/** @var Store_Product_Attribute_Rule */
		$rule = Store_Product_Attribute_Rule::find((int)$ruleId)->first();
		$this->assertInstanceOf('Store_Product_Attribute_Rule', $rule);
		$this->assertEquals($ruleId, $rule->getId());
		$this->assertEquals($adjustment, $rule->getPriceAdjuster()->getAdjustment(), "adjustment value mismatch");
		$this->assertTrue($rule->getEnabled() == true, "new rule is not enabled");

		return $ruleId;
	}

	public function testToggleRule ()
	{
		$this->markTestIncomplete('this needs updating for new RULE endpoints');
		$result = $this->doRequest('loadOptionSetForProduct');

		$ruleId = $this->_createTestRule();

		$result = $this->doRequest('toggleRule');
		$this->assertNull($result, "toggleRule should have failed with no rule specified");

		$result = $this->doRequest('toggleRule', array(
			'rule' => 999999999,
		));
		$this->assertNull($result, "toggleRule should have failed with invalid rule specified");

		$result = $this->doRequest('toggleRule', array(
			'rule' => $ruleId,
		));
		$this->assertSame(1, $result['success'], "toggleRule failed");

		$rule = Store_Product_Attribute_Rule::find($ruleId)->first();
		$this->assertFalse($rule->getEnabled() == true, "rule not toggled to disabled");

		$result = $this->doRequest('toggleRule', array(
			'rule' => $ruleId,
		));
		$this->assertSame(1, $result['success'], "toggleRule failed");

		$this->assertTrue($rule->load(), "load failed");
		$this->assertTrue($rule->getEnabled() == true, "rule not toggled back to enabled");
	}

	/**
	* This is a basic / high level test of product and product type rules to ensure mainly that they are functioning
	* via getDetailsForAttributeValues and are processed in the correct order.
	*
	*/
	public function testProductTypeAndProductRules ()
	{
		// @todo would love to turn this into something more automated using data providers but couldn't figure out an
		// easy implementation straight away

		// create a product
		$entity = new Store_Product_Gateway;
		$productId = $this->createTestProduct();
		$product = new ISC_PRODUCT($productId, true);

		// control test
		$base = $product->getBaseProductDetails();
		$this->assertEquals(5.0, $base->getStoreFrontPrice(), 'control price mismatch', 0.0001);
		$this->assertEquals(5.0, $base->getWeight(), 'control weight mismatch', 0.0001);

		// create some attributes and values
		$attributes = array();
		$values = array();
		$productTypeAttributes = array();

		$type = new Store_Attribute_Type_Configurable_PickList_Set;
		$type->setView(new Store_Attribute_View_Select);

		$attribute = new Store_Attribute;
		$attribute
			->setName('colour')
			->setDisplayName('colour')
			->setType($type);
		$this->assertTrue($attribute->save(), 'colour save failed');

		$attributes['colour'] = $attribute;

			$value = $attribute->createAttributeValue();
			$value
				->setLabel('red')
				->setSortOrder(0);
			$this->assertTrue($value->save(), 'colour red save failed');

			$values['red'] = $value;

			$value = $attribute->createAttributeValue();
			$value
				->setLabel('blue')
				->setSortOrder(1);
			$this->assertTrue($value->save(), 'colour blue save failed');

			$values['blue'] = $value;

		$attribute = new Store_Attribute;
		$attribute
			->setName('size')
			->setDisplayName('size')
			->setType($type);
		$this->assertTrue($attribute->save(), 'size save failed');

		$attributes['size'] = $attribute;

			$value = $attribute->createAttributeValue();
			$value
				->setLabel('small')
				->setSortOrder(0);
			$this->assertTrue($value->save(), 'size small save failed');

			$values['small'] = $value;

			$value = $attribute->createAttributeValue();
			$value
				->setLabel('medium')
				->setSortOrder(1);
			$this->assertTrue($value->save(), 'size medium save failed');

			$values['medium'] = $value;

			$value = $attribute->createAttributeValue();
			$value
				->setLabel('large')
				->setSortOrder(2);
			$this->assertTrue($value->save(), 'size large save failed');

			$values['large'] = $value;

		// create a product type
		$type = new Store_Product_Type;
		$type->setName('test type');
		$this->assertTrue($type->save(), 'type save failed');

		foreach ($attributes as $attributeName => $attribute) {
			$productTypeAttribute = $type->addAttribute($attribute);
			$this->assertTrue($productTypeAttribute->save(), 'type->addAttribute->save failed');
			$productTypeAttributes[$attributeName] = $productTypeAttribute;
		}

		// assign the product type to the product
		$this->assertTrue($entity->assignProductType($productId, $type), 'assignProductType failed');

		// test count of products using an attribute
		$this->assertEquals(1, $attribute->getProductCount());
		$attributeCopy = $attribute->copy();
		$this->assertEquals(0, $attributeCopy->getProductCount());

		// test getCurrentOptionSetIds
		$this->assertEquals(array($type->getId()), $attribute->getCurrentOptionSetIds());

		$product = new ISC_PRODUCT($productId, true);

		// this will create product attribute ids which is what we need to test rules via getDetails... methods
		$productAttributes = array();
		foreach ($product->getProductAttributes(false) as $productAttribute) {
			$productAttributes[$productAttribute->getDisplayName()] = $productAttribute;
		}

		// create some product type rules
		$adjuster = new Store_ValueAdjuster_Relative;
		$adjuster->setAdjustment(10);

		$rule = new Store_Product_Type_Rule;
		$rule
			->setProductTypeId($type->getId())
			->setEnabled(true)
			->setSortOrder(0)
			->setPriceAdjuster($adjuster);
		$this->assertTrue($rule->save(), 'type rule(1) save failed');

		$this->assertInstanceOf('Store_Product_Type_Rule_Condition', $rule->createCondition(
			$productTypeAttributes['colour']->getId(),
			$values['blue']->getId()
		), 'create condition(1) failed: ' . $rule->getDb()->GetErrorMsg());

		$rule = new Store_Product_Type_Rule;
		$rule
			->setProductTypeId($type->getId())
			->setEnabled(true)
			->setSortOrder(1)
			->setWeightAdjuster($adjuster);
		$this->assertTrue($rule->save(), 'type rule(2) save failed');

		$this->assertInstanceOf('Store_Product_Type_Rule_Condition', $rule->createCondition(
			$productTypeAttributes['size']->getId(),
			$values['medium']->getId()
		), 'create condition(2) failed');

		// test for expected results - product type rules only

		// no matching rules

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'no rules: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'no rules: weight mismatch', 0.0001);

		// price rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(15, $details->getStoreFrontPrice(), 'price rule: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'price rule: weight mismatch', 0.0001);

		// weight rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'weight rule: price mismatch', 0.0001);
		$this->assertEquals(15, $details->getWeight(), 'weight rule: weight mismatch', 0.0001);

		// both rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(15, $details->getStoreFrontPrice(), 'both rules: price mismatch', 0.0001);
		$this->assertEquals(15, $details->getWeight(), 'both rules: weight mismatch', 0.0001);

		// create some product rules

		$adjuster = new Store_ValueAdjuster_Percentage;
		$adjuster->setAdjustment(10);

		$rule = new Store_Product_Attribute_Rule();
		$rule
			->setProductId($productId)
			->setEnabled(true)
			->setSortOrder(0)
			->setPriceAdjuster($adjuster);
		$this->assertTrue($rule->save(), 'product rule(1) save failed');

		$this->assertInstanceOf('Store_Product_Attribute_Rule_Condition', $rule->createCondition(
			$productAttributes['colour']->getId(),
			$values['blue']->getId()
		), 'create product rule condition(1) failed: ' . $rule->getDb()->GetErrorMsg());

		$rule = new Store_Product_Attribute_Rule();
		$rule
			->setProductId($productId)
			->setEnabled(true)
			->setSortOrder(1)
			->setWeightAdjuster($adjuster);
		$this->assertTrue($rule->save(), 'product rule(1) save failed');

		$this->assertInstanceOf('Store_Product_Attribute_Rule_Condition', $rule->createCondition(
			$productAttributes['size']->getId(),
			$values['medium']->getId()
		), 'create product rule condition(1) failed: ' . $rule->getDb()->GetErrorMsg());

		// test for expected results - product type and product rules

		// no matching rules

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'no rules: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'no rules: weight mismatch', 0.0001);

		// price rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(16.5, $details->getStoreFrontPrice(), 'price rule: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'price rule: weight mismatch', 0.0001);

		// weight rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'price rule: price mismatch', 0.0001);
		$this->assertEquals(16.5, $details->getWeight(), 'price rule: weight mismatch', 0.0001);

		// both rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(16.5, $details->getStoreFrontPrice(), 'both rules: price mismatch', 0.0001);
		$this->assertEquals(16.5, $details->getWeight(), 'both rules: weight mismatch', 0.0001);

		// remove the product type rules and test only product rules

		$this->assertTrue($type->getProductTypeRules(null)->deleteAll(), 'type rule delete failed');

		// no matching rules

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'no rules: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'no rules: weight mismatch', 0.0001);

		// price rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['small']->getId(),
		));

		$this->assertEquals(5.5, $details->getStoreFrontPrice(), 'price rule: price mismatch', 0.0001);
		$this->assertEquals(5, $details->getWeight(), 'price rule: weight mismatch', 0.0001);

		// weight rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['red']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(5, $details->getStoreFrontPrice(), 'price rule: price mismatch', 0.0001);
		$this->assertEquals(5.5, $details->getWeight(), 'price rule: weight mismatch', 0.0001);

		// both rule match

		$details = $product->getDetailsForAttributeValues(array(
			$productAttributes['colour']->getId() => $values['blue']->getId(),
			$productAttributes['size']->getId() => $values['medium']->getId(),
		));

		$this->assertEquals(5.5, $details->getStoreFrontPrice(), 'both rules: price mismatch', 0.0001);
		$this->assertEquals(5.5, $details->getWeight(), 'both rules: weight mismatch', 0.0001);
	}
}
