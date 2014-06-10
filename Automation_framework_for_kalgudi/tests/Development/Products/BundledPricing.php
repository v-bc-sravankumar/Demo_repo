<?php

require_once 'BundledProductTest.php';

class BundledPricing extends BundledProductTest
{

	public function setUp()
	{
		parent::setUp();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Creates 2 products with different prices ("productA" and "productB")
	 * Then create a product picklist option with "productB" inside within an optionset and assign to "productA"
	 * Associate a price adjustment rule to when "productB" is selected within "productA"
	 *
	 * @param float $priceA				Price of product A
	 * @param float $priceB				Price of product B
	 * @param string $ruleDirection		See const in BundledProductTest with 'RULE_DIRECTION_' prefix
	 * @param float $adjustment			Value of adjustment
	 * @param string $adjuster			See const in BundledProductTest with 'RULE_ADJUSTER_' prefix
	 * @return array
	 */
	protected function createSimpleBundledProductWithPriceRule($priceA, $priceB, $ruleDirection, $adjustment, $adjuster)
	{
		// create a random product
		$productA = $this->createProduct($priceA);
		// create another random product
		$productB = $this->createProduct($priceB);
		// create a new option with product B in it
		$option = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));
		// value for this option (we can use this in a rule)
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
			$ruleDirection, $adjustment, $adjuster);

		// build up options
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;

		// attributes
		$attributeValues = array(
			$productAttribute->getId() => $optionValue->getId()
		);

		return array(
			'productA' => $productA,
			'productB' => $productB,
			'option' => $option,
			'optionValue' => $optionValue,
			'optionSet' => $optionSet,
			'productTypeAttribute' => $productTypeAttribute,
			'productAttribute' => $productAttribute,
			'rule' => $rule,
		);
	}


	/**
	 * Create a simple two product bundle and calculate pricing from product details and quote
	 *
	 * @param float $priceA				Price of product A
	 * @param float $priceB				Price of product B
	 * @param string $ruleDirection		See const in BundledProductTest with 'RULE_DIRECTION_' prefix
	 * @param float $adjustment			Value of adjustment
	 * @param string $adjuster			See const in BundledProductTest with 'RULE_ADJUSTER_' prefix
	 * @param int $qty					Quantity in the cart (default 1)
	 * @return array
	 */
	protected function getSimpleTwoProductWithQuote($priceA, $priceB, $ruleDirection, $adjustment, $adjuster, $qty = 1)
	{
		$bundle = $this->createSimpleBundledProductWithPriceRule($priceA, $priceB, $ruleDirection, $adjustment, $adjuster);

		$productA = $bundle['productA'];
		$productB = $bundle['productB'];
		$option = $bundle['option'];
		$optionValue = $bundle['optionValue'];
		$optionSet = $bundle['optionSet'];
		$productTypeAttribute = $bundle['productTypeAttribute'];
		$productAttribute = $bundle['productAttribute'];
		$rule = $bundle['rule'];
		$quote = $this->getTestQuote();

		// build up options
		$detailsOptions = $this->getDefaultDetailsOptions();

		// attributes
		$attributeValues = array(
			$productAttribute->getId() => $optionValue->getId()
		);

		// product details
		$productA_ = new ISC_PRODUCT($productA->GetProductId());
		$details = $productA_->getDetailsForAttributeValues($attributeValues, $detailsOptions);
		$priceFromProductDetails = $details->getPrice();

		// quote
		$quoteItem = $this->getQuoteItem($quote, $productA);
		$quoteItem->setProductTypeId($productA->getProductTypeId());
		$quoteItem->applyProductAttributeValues($attributeValues, $qty);
		$priceFromQuote = $quote->getBaseSubTotal();

		return array(
			'priceFromProductDetails' => $priceFromProductDetails,
			'priceFromQuote' => $priceFromQuote
		);

	}


	/**
	 * Get the default details options
	 *
	 * @return Store_Product_Details_GetOptions
	 */
	protected function getDefaultDetailsOptions()
	{
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;
		return $detailsOptions;
	}


	// --- BEGIN TESCASES ---

	/**
	 * Check that we are calculating the reduction properly
	 */
	public function testRemovedPercentage()
	{

		$bundle = $this->getSimpleTwoProductWithQuote(50, 100, self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);
		$priceFromProductDetails = $bundle['priceFromProductDetails'];
		$priceFromQuote = $bundle['priceFromQuote'];

		// assert the the calculation is : (0.5 * priceof(productA)) + priceof(productB)
		$expectedPrice = 125;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Check that we are calculating the addition properly
	 */
	public function testAddPercentage()
	{

		$bundle = $this->getSimpleTwoProductWithQuote(50, 100, self::RULE_DIRECTION_ADD, 50, self::RULE_ADJUSTER_PERCENTAGE);

		$priceFromProductDetails = $bundle['priceFromProductDetails'];
		$priceFromQuote = $bundle['priceFromQuote'];

		// assert the the calculation is : (1.5 * priceof(productA)) + priceof(productB)
		$expectedPrice = 175;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Check that we are calculating the addition properly
	 */
	public function testFixedPercentage()
	{

		$bundle = $this->getSimpleTwoProductWithQuote(50, 100, self::RULE_DIRECTION_FIXED, 10, self::RULE_ADJUSTER_PRICE);

		$priceFromProductDetails = $bundle['priceFromProductDetails'];
		$priceFromQuote = $bundle['priceFromQuote'];

		// assert the the calculation is : (0.1 * priceof(productA)) + priceof(productB)
		$expectedPrice = 110;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Check that we are calculating the reduction properly
	 */
	public function testRemovedPercentageMultipleItems()
	{

		$qty = 3;
		$bundle = $this->getSimpleTwoProductWithQuote(50, 100, self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE, $qty);
		$priceFromProductDetails = $bundle['priceFromProductDetails'];
		$priceFromQuote = $bundle['priceFromQuote'];

		// assert that the calculation is : (0.5 * priceof(productA)) + priceof(productB)
		$expectedPrice = 125;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($qty * $priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Mixed up percentage adjustment and price adjustment
	 */
	public function testMixedRules1()
	{
		// create a random product
		$productA = $this->createProduct(50);
		// create another random product
		$productB = $this->createProduct(100);
		// create another random product
		$productC = $this->createProduct(100);

		// create a new option with product B in it
		$optionB = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));

		// create a new option with product C in it
		$optionC = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productC->GetProductName()),
				'productIds' => array($productC->GetProductId()),
				'valueIds' => array('')
			)
		));

		// value for optionB (we can use this in a rule)
		$optionValueB = $optionB->getValues()->current();

		// value for optionC (we can use this in a rule)
		$optionValueC = $optionC->getValues()->current();

		// create an optionset and attach to productA
		$optionSet = $this->createOptionSet(array($optionB, $optionC), $productA);

		// get product type attribute
		$productTypeAttributeB = $this->getProductTypeAttribute($optionSet, $optionB);
		$productTypeAttributeC = $this->getProductTypeAttribute($optionSet, $optionC);

		// get product attribute
		$productAttributeB = $this->getProductAttribute($productA, $optionSet, $optionB);
		$productAttributeC = $this->getProductAttribute($productA, $optionSet, $optionC);

		// create a rule: when $optionValueB is selected remove 50% of the product price
		$ruleB = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueB->getId()),
			self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);

		// create a rule: when $optionValueC is selected remove 50% of the product price
		$ruleC = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueC->getId()),
			self::RULE_DIRECTION_ADD, 10, self::RULE_ADJUSTER_PRICE);

		// build up options
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;

		// attributes
		$attributeValues = array(
			$productAttributeB->getId() => $optionValueB->getId(),
			$productAttributeC->getId() => $optionValueC->getId(),
		);

		// product details
		$productA_ = new ISC_PRODUCT($productA->GetProductId());
		$details = $productA_->getDetailsForAttributeValues($attributeValues, $detailsOptions);
		$priceFromProductDetails = $details->getPrice();

		// quote
		$quote = $this->getTestQuote();
		$quoteItem = $this->getQuoteItem($quote, $productA);
		$quoteItem->setProductTypeId($productA->getProductTypeId());
		$quoteItem->applyProductAttributeValues($attributeValues, 1);
		$priceFromQuote = $quote->getBaseSubTotal();

		// assert that the calculation is : (0.5 * priceof(productA)) + 10 + priceof(productB) + priceof(productC)
		$expectedPrice = 235;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Mixed up price adjustment and percentage adjustment
	 */
	public function testMixedRules2()
	{
		// create a random product
		$productA = $this->createProduct(50);
		// create another random product
		$productB = $this->createProduct(100);
		// create another random product
		$productC = $this->createProduct(100);

		// create a new option with product B in it
		$optionB = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));

		// create a new option with product C in it
		$optionC = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productC->GetProductName()),
				'productIds' => array($productC->GetProductId()),
				'valueIds' => array('')
			)
		));

		// value for optionB (we can use this in a rule)
		$optionValueB = $optionB->getValues()->current();

		// value for optionC (we can use this in a rule)
		$optionValueC = $optionC->getValues()->current();

		// create an optionset and attach to productA
		$optionSet = $this->createOptionSet(array($optionB, $optionC), $productA);

		// get product type attribute
		$productTypeAttributeB = $this->getProductTypeAttribute($optionSet, $optionB);
		$productTypeAttributeC = $this->getProductTypeAttribute($optionSet, $optionC);

		// get product attribute
		$productAttributeB = $this->getProductAttribute($productA, $optionSet, $optionB);
		$productAttributeC = $this->getProductAttribute($productA, $optionSet, $optionC);

		// create a rule: when $optionValueC is selected remove 50% of the product price
		$ruleC = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueC->getId()),
			self::RULE_DIRECTION_ADD, 10, self::RULE_ADJUSTER_PRICE);

		// create a rule: when $optionValueB is selected remove 50% of the product price
		$ruleB = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueB->getId()),
			self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);

		// build up options
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;

		// attributes
		$attributeValues = array(
			$productAttributeB->getId() => $optionValueB->getId(),
			$productAttributeC->getId() => $optionValueC->getId(),
		);

		// product details
		$productA_ = new ISC_PRODUCT($productA->GetProductId());
		$details = $productA_->getDetailsForAttributeValues($attributeValues, $detailsOptions);
		$priceFromProductDetails = $details->getPrice();

		// quote
		$quote = $this->getTestQuote();
		$quoteItem = $this->getQuoteItem($quote, $productA);
		$quoteItem->setProductTypeId($productA->getProductTypeId());
		$quoteItem->applyProductAttributeValues($attributeValues, 1);
		$priceFromQuote = $quote->getBaseSubTotal();

		// assert the the calculation is : (priceof(productA) + 10)*0.5 + priceof(productB) + priceof(productC)
		$expectedPrice = 230;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

	}

	/**
	 * Mixed up percentage and percentage adjustment on an odd price
	 * with multiple quantity.
	 */
	public function testMixedRules3()
	{
		// create a random product
		$productA = $this->createProduct(13.44);
		// create another random product
		$productB = $this->createProduct(10);
		// create another random product
		$productC = $this->createProduct(10);

		// create a new option with product B in it
		$optionB = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));

		// create a new option with product C in it
		$optionC = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productC->GetProductName()),
				'productIds' => array($productC->GetProductId()),
				'valueIds' => array('')
			)
		));

		// value for optionB (we can use this in a rule)
		$optionValueB = $optionB->getValues()->current();

		// value for optionC (we can use this in a rule)
		$optionValueC = $optionC->getValues()->current();

		// create an optionset and attach to productA
		$optionSet = $this->createOptionSet(array($optionB, $optionC), $productA);

		// get product type attribute
		$productTypeAttributeB = $this->getProductTypeAttribute($optionSet, $optionB);
		$productTypeAttributeC = $this->getProductTypeAttribute($optionSet, $optionC);

		// get product attribute
		$productAttributeB = $this->getProductAttribute($productA, $optionSet, $optionB);
		$productAttributeC = $this->getProductAttribute($productA, $optionSet, $optionC);

		// create a rule: when $optionValueC is selected remove 30% of the product price
		$ruleC = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueC->getId()),
			self::RULE_DIRECTION_REMOVE, 30, self::RULE_ADJUSTER_PERCENTAGE);

		// create a rule: when $optionValueB is selected remove 50% of the product price
		$ruleB = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueB->getId()),
			self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);

		// build up options
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;

		// attributes
		$attributeValues = array(
			$productAttributeB->getId() => $optionValueB->getId(),
			$productAttributeC->getId() => $optionValueC->getId(),
		);

		// product details
		$productA_ = new ISC_PRODUCT($productA->GetProductId());
		$details = $productA_->getDetailsForAttributeValues($attributeValues, $detailsOptions);
		$priceFromProductDetails = $details->getPrice();

		// quote
		$quote = $this->getTestQuote();
		$quoteItem = $this->getQuoteItem($quote, $productA);
		$quoteItem->setProductTypeId($productA->getProductTypeId());
		$quoteItem->applyProductAttributeValues($attributeValues, 1);
		$priceFromQuote = $quote->getBaseSubTotal();

		// assert the the calculation is : round(round(priceof(productA) * 0.7) * 0.5) + priceof(productB) + priceof(productC)
		$expectedPrice = 24.7;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

		$quantity = 3;
		$quoteItem->setQuantity($quantity);
		$priceFromQuote = $quote->getBaseSubTotal();
		// check that the cart is getting the same result
		$this->assertEquals($quantity * $priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match on multiple quantities.");

	}

	/**
	 * Mixed up add pricing, percentage and percentage adjustment on an odd price
	 * with multiple quantity.
	 */
	public function testMixedRules4()
	{
		// create a random product
		$productA = $this->createProduct(13.44);
		// create another random product
		$productB = $this->createProduct(10);
		// create another random product
		$productC = $this->createProduct(10);
		// create another random product
		$productD = $this->createProduct(10);

		// create a new option with product B in it
		$optionB = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productB->GetProductName()),
				'productIds' => array($productB->GetProductId()),
				'valueIds' => array('')
			)
		));

		// create a new option with product C in it
		$optionC = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productC->GetProductName()),
				'productIds' => array($productC->GetProductId()),
				'valueIds' => array('')
			)
		));

		// create a new option with product D in it
		$optionD = $this->createOptionProductPickList(1, array(
			'values' => array(
				'labels' => array($productD->GetProductName()),
				'productIds' => array($productD->GetProductId()),
				'valueIds' => array('')
			)
		));

		// value for optionB (we can use this in a rule)
		$optionValueB = $optionB->getValues()->current();

		// value for optionC (we can use this in a rule)
		$optionValueC = $optionC->getValues()->current();

		// value for optionD (we can use this in a rule)
		$optionValueD = $optionD->getValues()->current();

		// create an optionset and attach to productA
		$optionSet = $this->createOptionSet(array($optionB, $optionC, $optionD), $productA);

		// get product type attribute
		$productTypeAttributeB = $this->getProductTypeAttribute($optionSet, $optionB);
		$productTypeAttributeC = $this->getProductTypeAttribute($optionSet, $optionC);
		$productTypeAttributeD = $this->getProductTypeAttribute($optionSet, $optionD);

		// get product attribute
		$productAttributeB = $this->getProductAttribute($productA, $optionSet, $optionB);
		$productAttributeC = $this->getProductAttribute($productA, $optionSet, $optionC);
		$productAttributeD = $this->getProductAttribute($productA, $optionSet, $optionD);


		// create a rule: when $optionValueD is selected add $7 to the product price
		$ruleD = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueD->getId()),
			self::RULE_DIRECTION_ADD, 7, self::RULE_ADJUSTER_PRICE);

		// create a rule: when $optionValueC is selected remove 30% of the product price
		$ruleC = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueC->getId()),
			self::RULE_DIRECTION_REMOVE, 30, self::RULE_ADJUSTER_PERCENTAGE);

		// create a rule: when $optionValueB is selected remove 50% of the product price
		$ruleB = $this->createProductOptionPriceAdjustmentRule($productA,
			array($optionValueB->getId()),
			self::RULE_DIRECTION_REMOVE, 50, self::RULE_ADJUSTER_PERCENTAGE);


		// build up options
		$detailsOptions = new Store_Product_Details_GetOptions;
		$detailsOptions->bypassRequiredChecks = true;

		// attributes
		$attributeValues = array(
			$productAttributeB->getId() => $optionValueB->getId(),
			$productAttributeC->getId() => $optionValueC->getId(),
			$productAttributeD->getId() => $optionValueD->getId(),
		);

		// product details
		$productA_ = new ISC_PRODUCT($productA->GetProductId());
		$details = $productA_->getDetailsForAttributeValues($attributeValues, $detailsOptions);
		$priceFromProductDetails = $details->getPrice();

		// quote
		$quote = $this->getTestQuote();
		$quoteItem = $this->getQuoteItem($quote, $productA);
		$quoteItem->setProductTypeId($productA->getProductTypeId());
		$quoteItem->applyProductAttributeValues($attributeValues, 1);
		$priceFromQuote = $quote->getBaseSubTotal();

		// assert the the calculation is : round(round((priceof(productA) + 7) * 0.7) * 0.5) + priceof(productB) + priceof(productC) + priceof(productD)
		$expectedPrice = 37.15;
		$this->assertEquals($expectedPrice, $priceFromProductDetails, "Incorrect product details calculation");
		// check that the cart is getting the same result
		$this->assertEquals($priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match.");

		$quantity = 3;
		$quoteItem->setQuantity($quantity);
		$priceFromQuote = $quote->getBaseSubTotal();
		// check that the cart is getting the same result
		$this->assertEquals($quantity * $priceFromProductDetails, $priceFromQuote, "Product Details price and Quote price doesn't match on multiple quantities.");

	}


}