<?php

/**
 * A testing framework for products with bundle options (ie: a product within a product)
 *
 * @see BundledProductTest::sampleProductBundleUsage for examples
 * @author qamal.kosim-satyaputra
 *
 */
class BundledProductTest extends Interspire_IntegrationTest
{

	const NAME_PREFIX = "TestBundled";
	const OPTIONS_NAME_PREFIX = 'TestBundledOption';
	const OPTIONSET_NAME_PREFIX = 'TestBundledOptionSet';

	// rule constants
	const RULE_PRICE_ADJUSTMENT = 1;

	const RULE_DIRECTION_ADD = '1';
	const RULE_DIRECTION_REMOVE = '-1';
	const RULE_DIRECTION_FIXED = '0';

	const RULE_ADJUSTER_PRICE = 'Relative';
	const RULE_ADJUSTER_PERCENTAGE = 'Percentage';

	protected $products = array();
	protected $options = array();
	protected $optionsSets = array();

	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function tearDown ()
	{
		parent::tearDown();
		$this->removeTestOptionRules();
		$this->removeTestOptions();
		$this->removeTestOptionSets();
		$this->removeTestProducts();
	}

	/**
	 * Remove the test products we created
	 */
	protected function removeTestProducts()
	{
		$products = new Store_Product_Gateway();
		$results = $this->fixtures->db->Query("SELECT productid as id FROM [|PREFIX|]products WHERE prodname LIKE '".self::NAME_PREFIX."%'");
		$productIds = array();
		while ($row = $this->fixtures->db->Fetch($results)) {
			$productIds[] = (int) $row['id'];
		}

		$products->multiDelete($productIds);
		$this->products = array();
	}

	/**
	 * Remove the test optionsets we created
	 */
	protected function removeTestOptionSets()
	{
		$attributes = Store_Product_Type::find("name LIKE '".self::NAME_PREFIX."%'");
		$optionSetIds = array();
		while ($attribute = $attributes->current()) {
			$optionSetIds[] = $attribute->getId();
			$attributes->next();
		}
		Store_Product_Type::find('id IN('.implode(',', $optionSetIds).')')->deleteAll();
		$this->optionsSets = array();
	}

	/**
	 * Remove the test options we created
	 */
	protected function removeTestOptions()
	{
		$results = $this->fixtures->Query("SELECT id FROM [|PREFIX|]attributes WHERE name LIKE '".self::NAME_PREFIX."%'");
		$ids = array();
		while ($row = $this->fixtures->Fetch($results)) {
			$ids[] = $row['id'];
		}
		Store_Attribute::find('id IN('.implode(',', $ids).')')->deleteAll();
		$this->options = array();
	}


	protected function removeTestOptionRules()
	{
		$products = new Store_Product_Gateway();

		$query = "SELECT pr.id as id
				FROM [|PREFIX|]products p, [|PREFIX|]product_attribute_rules pr
				WHERE p.prodname LIKE '".self::NAME_PREFIX."%' AND p.productid = pr.product_id";

		$results = $this->fixtures->db->Query($query);
		$productIds = array();
		while ($row = $this->fixtures->db->Fetch($results)) {
			$rule = new Store_Product_Attribute_Rule;
			$rule->load($row['id']);
			$rule->delete();
		}
	}


	/**
	 * Get a unique name with the defined name prefix
	 * @param string $prefix
	 */
	protected function getUniqueName($prefix = self::NAME_PREFIX)
	{
		return $prefix . '_' . uniqid('');
	}

	protected function assignOptionSet(ISC_PRODUCT &$product, Store_Product_Type $optionSet)
	{

		// grab the product
		$product = new ISC_PRODUCT($product->GetProductId());

		$products = new Store_Product_Gateway();
		$products->assignProductType($product->GetProductId(), $optionSet);

	}

	/**
	 * Create a new test product with the given price
	 *
	 * @param int|float $price
	 * @param array $productData
	 */
	protected function createProduct($price, $productData = array())
	{
		$products = new Store_Product_Gateway();

		$name = $this->getUniqueName();
		// this is based off a var_export of $Data before it hits Store_Product_Gateway->add in ISC_ADMIN_PRODUCT->_CommitProduct
		$data = array (
		    'productid' => 0,
		    'prodhash' => md5(uniqid('', true)),
		    'prodname' => $name,
		    'url' => '',
		    'create_redirect' => false,
		    'is_customized' => '0',
		    'prodcats' => array ('2'),
		    'prodtype' => '1',
		    'prodcode' => '',
		    'productVariationExisting' => '0',
		    'proddesc' => '<p>'.$name.'</p>',
		    'prodpagetitle' => '',
		    'prodsearchkeywords' => '',
		    'prodavailability' => '',
		    'prodprice' => ''.number_format($price, 2),
		    'prodcostprice' => '0.00',
		    'prodretailprice' => '0.00',
		    'prodsaleprice' => '0.00',
		    'prodsortorder' => 0,
		    'tax_class_id' => '0',
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
		    'bin_picking_number' => '',
		    'prodwarranty' => '',
		    'prodmetakeywords' => '',
		    'prodmetadesc' => '',
		    'prodfreeshipping' => 0,
		    'prodoptionsrequired' => 0,
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
		    'product_videos' => array (),
		    'product_images' => array (),
		    'product_enable_optimizer' => 0,
		    'google_ps_enabled' => 0,
		    'prodminqty' => 0,
		    'prodmaxqty' => 0,
		    'opengraph_type' => 'product',
		    'opengraph_use_product_name' => true,
		    'opengraph_title' => '',
		    'opengraph_use_meta_description' => true,
		    'opengraph_description' => '',
		    'opengraph_use_image' => '1',
		    'upc' => '',
		    'disable_google_checkout' => '0',
		    'product_type_id' => '',
		    'product_type_display' => '0',
		  );

		$data = array_merge($data, $productData);

		$productId = (int)$products->add($data);
		$this->assertGreaterThan(0, $productId, $products->getError());

		$product = new ISC_PRODUCT($productId);
		$this->products[$product->GetProductId()] = $product;

		// category
		foreach ($data['prodcats'] as $cat) {
			$newAssociation = array(
				"productid" => $product->GetProductId(),
				"categoryid" => $cat
			);
			$this->fixtures->db->InsertQuery("categoryassociations", $newAssociation);
		}

		return $product;
	}

	/**
	 * Get a test quote object
	 *
	 * @return ISC_QUOTE
	 */
	protected function getTestQuote()
	{
		$quote = new ISC_QUOTE;

		$billing = $quote->getBillingAddress();
		$billing->setFirstName('first');
		$billing->setLastName('last');
		$billing->setAddress1('1');
		$billing->setCity('city');
		$billing->setPhone('12345678');
		$billing->setCountryByName('Australia');
		$billing->setStateByName('New South Wales');
		$billing->setZip('2010');
		$this->assertTrue($billing->isComplete(), "Billing address is not complete");

		$shipping = $quote->createShippingAddress();
		$shipping->setFirstName('first');
		$shipping->setLastName('last');
		$shipping->setAddress1('1');
		$shipping->setCity('city');
		$shipping->setPhone('12345678');
		$shipping->setCountryByName('Australia');
		$shipping->setStateByName('New South Wales');
		$shipping->setZip('2010');
		$shipping->setHandlingCost(1.1);
		$shipping->setShippingMethod(2.2, '.', 'peritem');
		$this->assertTrue($shipping->isComplete(), "Shipping address is not complete");
		$this->assertTrue($shipping->hasShippingMethod(), "Shipping address has no shipping method");

		return $quote;
	}

	/**
	 * Get an ISC_PRODUCT object given product ID
	 *
	 * @param ISC_PRODUCT $productId
	 */
	protected function getProduct($productId)
	{
		$product = new ISC_PRODUCT($productId);

		return $product;
	}

	/**
	 * Create a quote item from the given quote and product ID
	 *
	 * @param ISC_QUOTE $quote
	 * @param ISC_PRODUCT $productId
	 * @return ISC_QUOTE_ITEM
	 */
	protected function getQuoteItem(ISC_QUOTE $quote, ISC_PRODUCT $product)
	{
		$item = new ISC_QUOTE_ITEM;
		$item->setQuote($quote);
		$item->setProductId($product->GetProductId());
		$quote->addItem($item);

		return $item;
	}

	/**
	 * Create a test option with <n> number of products
	 *
	 * @param int $numProducts			Number of random products to go in this picklist
	 * @param array $optionData			Data override
	 * @return Store_Attribute|NULL
	 */
	protected function createOptionProductPickList($numProducts = 3, $optionData = array())
	{
		$success = true;
		$name = $this->getUniqueName(self::OPTIONS_NAME_PREFIX);
		$attribute = new Store_Attribute;

		$db = $attribute->getDb();
		$db->StartTransaction();

		$attributeType = new Store_Attribute_Type_Configurable_PickList_Product;

		$requestData = array(
			'OptionName' => $name,
		    'DisplayName' => 'Optional Stuff',
		    'OptionDisplayType' => 'Store_Attribute_Type_Configurable_PickList_Product',
		    'Configurable_PickList_Set_View' => 'Product_PickList',
			'AutomaticInventory' => '1',
		    'AutomaticPricing' => '1',
		    'Yes_AutomaticShippingOn' => '1',
			'values' => array(
				'labels' => array(),
				'productIds' => array(),
				'valueIds' => array(),
			),
			'OptionId' => '',
		);

		// grab a few random products from the DB
		if (empty($optionData['values'])) {
			$randomProducts = $this->getOptionProducts($numProducts);
			foreach ($randomProducts as $productId => $label) {
				$requestData['values']['productIds'][] = $productId;
				$requestData['values']['labels'][] = $label;
			}
		}

		$requestData = array_merge($requestData, $optionData);

		$attribute->setName($requestData['OptionName']);
		$attribute->setDisplayName($requestData['DisplayName']);

		// fake request
		$request = new Interspire_Request(null, $requestData);

		$attribute->setType($attributeType);
		try {
			$attributeType->setConfigurationSettings($attribute, $request);
			$success = $success && $attribute->save();
			if ($success) {
				$success = $success && $attributeType->processPostedAttributeValues($request, $attribute, false);
			}
		} catch(Exception $e) {
			$db->RollbackTransaction();
		}

		if ($success) {
			$db->CommitTransaction();
			$this->options[$attribute->getId()] = $requestData['values']['productIds'];
			return $attribute;
		} else {
			$db->RollbackTransaction();
		}

		return null;

	}

	/**
	 * Get a bunch of products for the option picklist.
	 * The returned products will not have options.
	 *
	 * @param int $num
	 * @return array products
	 */
	protected function getOptionProducts($num = 3)
	{
		$query = "SELECT productid, prodname FROM [|PREFIX|]products WHERE prodname NOT LIKE '".self::NAME_PREFIX."%' AND product_type_id IS NULL LIMIT ".$num;
		$results = $this->fixtures->Query($query);
		$products = array();
		while ($row = $this->fixtures->Fetch($results)) {
			$products[$row['productid']] = $row['prodname'];
		}
		return $products;
	}

	/**
	 * Returns a new optionset
	 *
	 * @param array $attributes
	 * @param ISC_PRODUCT $product		OPTIONAL the product this will be assigned to
	 * @return NULL|Store_Product_Type
	 */
	protected function createOptionSet(array $attributes, ISC_PRODUCT $product = null)
	{
		// create a bunch of options
		$optionSet = new Store_Product_Type;
		$optionSet->setName($this->getUniqueName(self::OPTIONSET_NAME_PREFIX));
		$db = $optionSet->getDb();
		$db->StartTransaction();
		try {
			if ($optionSet->save()) {

				$attributeIds = array();
				foreach ($attributes as $attribute) {
					if ($optionSet->addAttribute($attribute)->save()) {
						$attributeIds[] = $attribute->getId();
					} else {
						$db->RollbackTransaction();
						return null;
					}
				}
				$db->CommitTransaction();
				$this->optionsSets[$optionSet->getId()] = $attributeIds;

				// assign to product if supplied
				if (!empty($product)) {
					$this->assignOptionSet($product, $optionSet);
				}

				return $optionSet;
			}
		} catch (Exception $e) {
			$db->RollbackTransaction();
		}
		return null;
	}

	/**
	 * Create a product option rule
	 *
	 * @param array $data
	 * @return Store_Product_Attribute_Rule
	 */
	protected function createProductOptionRule($data)
	{
		$defaults = array(
			'ajaxSubmit' => '1',
			'loadFromProductId' => '1',
			'enabled' => '1',
		);
		$data = array_merge($defaults, $data);

		$request = new Interspire_Request(null, $data);
		$ruleManager = new ISC_ADMIN_REMOTE_PRODUCT_RULESMANAGER();
		$resp = $ruleManager->saveRuleAction($request);

		// if success return rule
		if (!empty($resp['success']) && !empty($resp['rule'])) {
			return Store_Product_Attribute_Rule::find((int) $resp['rule']);
		}
		return null;
	}


	/**
	 * Create a product option price adjustment rule
	 *
	 * @param ISC_PRODUCT $product
	 * @param array $values
	 * @param string $direction
	 * @param float $adjustmentValue
	 * @param string $type
	 * @param array $data
	 *
	 * @return Store_Product_Attribute_Rule
	 */
	protected function createProductOptionPriceAdjustmentRule(ISC_PRODUCT $product, array $values, $direction, $adjustmentValue, $type, $data = array())
	{
		// build request data
		$requestData = array(
			'productHash' => ''.$product->GetProductId(),
			'adjust_price' => '1',
		    'price_adjustment_direction' => $direction,
		    'price_adjustment' => ''.number_format($adjustmentValue, 2),
		    'price_adjuster' => $type,
		    'attribute_values' => array(),
		);

		if (!empty($values)) {
			$query = "SELECT pa.id AS id, av.id AS value
					 FROM [|PREFIX|]attribute_values av, [|PREFIX|]product_attributes pa
					 WHERE av.id IN(".implode(',', $values).")
							 AND av.attribute_id = pa.attribute_id
							 AND pa.product_id = ".(int) $product->GetProductId();
			$results = $this->fixtures->db->Query($query);
			while ($row = $this->fixtures->db->Fetch($results)) {
				if (!isset($requestData['attribute_values'][$row['id']])) {
					$requestData['attribute_values'][$row['id']] = array();
				}
				$requestData['attribute_values'][$row['id']][] = $row['value'];
			}
		}

		// merge in custom data
		$requestData = array_merge($requestData, $data);
		$rule = $this->createProductOptionRule($requestData);
		if (!empty($rule)) {
			return $rule->first();
		}

		return null;
	}

	/**
	 * Create a product option weight adjustment rule
	 *
	 * @param ISC_PRODUCT $product
	 * @param array $values
	 * @param string $direction
	 * @param float $adjustmentValue
	 * @param array $data
	 *
	 * @return Store_Product_Attribute_Rule
	 */
	protected function createProductOptionWeightAdjustmentRule(ISC_PRODUCT $product, array $values, $direction, $adjustmentValue, $data = array())
	{
		// build request data
		$requestData = array(
				'productHash' => ''.$product->GetProductId(),
				'adjust_weight' => '1',
			    'weight_adjustment_direction' => $direction,
			    'weight_adjustment' => ''.number_format($adjustmentValue, 2),
			    'attribute_values' => array(),
		);

		if (!empty($values)) {
			$query = "SELECT pa.id AS id, av.id AS value
						 FROM [|PREFIX|]attribute_values av, [|PREFIX|]product_attributes pa
						 WHERE av.id IN(".implode(',', $values).")
								 AND av.attribute_id = pa.attribute_id
								 AND pa.product_id = ".(int) $product->GetProductId();
			$results = $this->fixtures->db->Query($query);
			while ($row = $this->fixtures->db->Fetch($results)) {
				if (!isset($requestData['attribute_values'][$row['id']])) {
					$requestData['attribute_values'][$row['id']] = array();
				}
				$requestData['attribute_values'][$row['id']][] = $row['value'];
			}
		}

		// merge in custom data
		$requestData = array_merge($requestData, $data);

		return $this->createProductOptionRule($requestData);
	}

	/**
	 * Get a product type attribute
	 *
	 * @param Store_Product_Type $optionSet
	 * @param Store_Attribute $option
	 * @return Store_Product_Type_Attribute
	 */
	protected function getProductTypeAttribute(Store_Product_Type $optionSet, Store_Attribute $option)
	{
		return Store_Product_Type_Attribute::find("product_type_id = ".(int) $optionSet->getId()." AND attribute_id = ".(int) $option->getId())->first();
	}

	/**
	 * Get a product attribute
	 *
	 * @param ISC_PRODUCT $product
	 * @param Store_Product_Type $optionSet
	 * @param Store_Attribute $option
	 * @return Store_Product_Attribute
	*/
	protected function getProductAttribute(ISC_PRODUCT $product, Store_Product_Type $optionSet, Store_Attribute $option)
	{
		$productTypeAttribute = $this->getProductTypeAttribute($optionSet, $option);

		return Store_Product_Attribute::find("product_type_attribute_id = ".(int) $productTypeAttribute->getId()."
							AND attribute_id = ".(int) $option->getId()."
							AND product_id = ".(int) $product->GetProductId())->first();
	}


	// ---- BEGIN SAMPLE TESTCASES ----


	/**
	 *  A sample canvas for building a testcase using this class
	 */
	public function sampleProductBundleUsage()
	{

		// skip test, this is not really a testcase
		$this->markTestSkipped('This is a sample.');

		// create a random product with price: $50
		$productA = $this->createProduct(50);

		// create another random product with price: $100
		$productB = $this->createProduct(100);

		// create a new option with product B in it
		$option = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));

		// get the value for this option (we can use this in a rule)
		$optionValue = $option->getValues()->current();

		// create an optionset
		$optionSet = $this->createOptionSet(array($option), $productA);

		// get product type attribute
		$productTypeAttribute = $this->getProductTypeAttribute($optionSet, $option);

		// get product attribute
		$productAttribute = $this->getProductAttribute($productA, $optionSet, $option);

		// assign optionset
		$this->assignOptionSet($productA, $optionSet);

		// create a rule: when $optionValue is selected remove 50% of the product price
		$rule = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValue->getId()),
			self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);

		print_r(array(
			'productA' => $productA->GetProductId(),
			'productB' => $productB->GetProductId(),
			'option' => $option->getId(),
			'optionValue' => $optionValue->getId(),
			'optionSet' => $optionSet->getId(),
			'productTypeAttribute' => $productTypeAttribute->getId(),
			'productAttribute' => $productAttribute->getId(),
			'rule' => $rule->getId(),
		));

	}

}
