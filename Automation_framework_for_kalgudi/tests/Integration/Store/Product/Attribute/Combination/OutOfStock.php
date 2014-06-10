<?php
use Store\Settings\InventorySettings;

class Unit_Lib_Store_Product_Attribute_Combination_OutOfStock extends PHPUnit_Framework_TestCase
{
    const NUM_OPTION_VALUES = 3;
    const NUM_OPTIONS = 4;

    private $productId = null;

    // {attribute_id => [attribute_value_id]
    private $attributeToValue = array();

    // {attribute_value_id => attribute_id}
    private $valueToAttribute = array();

    // {attribute_id => product_attribute_id}
    private $attributeToProduct = array();

    // keep track of SKU we created, so we can change them to cover different oos scenarios
    private $sku = array();

    public function setUp()
    {
        $this->cleanup();
        $this->productId = $this->createTestProduct();
        $this->generateSKU();

        Store_Config::override('OptionOutOfStockBehavior', InventorySettings::OPTION_OUT_OF_STOCK_HIDE);
    }

    public function tearDown()
    {
        $this->cleanup();
    }

    private function cleanup()
    {
        $this->attributeToValue = array();
        $this->valueToAttribute= array();
        $this->attributeToProduct = array();
        $this->sku = array();

        Store_Product_Type_Attribute::find()->deleteAll();
        Store_Product_Attribute_Combination_Value::find()->deleteAll();
        Store_Product_Attribute_Combination::find()->deleteAll();
        Store_Product_Attribute::find()->deleteAll();
        Store_Attribute_Value::find()->deleteAll();
        Store_Attribute::find()->deleteAll();
        Store_Product_Type::find()->deleteAll();

        $this->removeTestProduct();
    }

    public function testFindSKUAttributeValues()
    {
        $values = Store_Product_Attribute_Combination::findSKUAttributeValues($this->productId);
        $this->assertEquals(self::NUM_OPTIONS * self::NUM_OPTION_VALUES, count($values));
    }

    public function testOptionValueSoldOutCompletely()
    {
        $totalOptions = self::NUM_OPTION_VALUES * self::NUM_OPTIONS;

        // sold out a random option value
        $soldOutOptionValue = $this->pickRandomOptionValue();
        $this->soldOutOptionValueCompletely($soldOutOptionValue);

        // check the option is not in the available list when nothing is selected
        Store_Product_Attribute_Combination::clearInStockAttributeCombinations();
        $availableOptionValues = Store_Product_Attribute_Combination::findAvailableAttributeValuesForSelection($this->productId, array());
        $this->assertEquals($totalOptions - 1, count($availableOptionValues));
        $this->assertFalse(in_array($soldOutOptionValue, $availableOptionValues));

        // check the option is not available when explicitly selected
        Store_Product_Attribute_Combination::clearInStockAttributeCombinations();
        $availableOptionValues = Store_Product_Attribute_Combination::findAvailableAttributeValuesForSelection($this->productId, $this->normalizeToArrayOfProductAttributes($soldOutOptionValue));

        $this->assertFalse(in_array($soldOutOptionValue, $availableOptionValues));
        $this->assertEquals(2, count($availableOptionValues));
    }

    public function normalizeToArrayOfProductAttributes($values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }

        $newValues = array();
        foreach ($values as $value) {
            $newValues[$this->attributeToProduct[$this->valueToAttribute[$value]]] = $value;
        }
        return $newValues;
    }

    public function testOptionValueSoldOutPartially()
    {
        $soldOutHistory = array();
        for($i = 2 ; $i < self::NUM_OPTIONS; $i++) {
            // sold out combination
            $soldOutCombinations = $this->pickRandomOptionValues($i);
            $this->soldOutOptionValuePartially($soldOutCombinations);

            // keep it for later comparison
            $soldOutHistory[] = $soldOutCombinations;

            // asserts sold out partial combinations are indeed sold out
            Store_Product_Attribute_Combination::clearInStockAttributeCombinations();
            $availableOptionValues = Store_Product_Attribute_Combination::findAvailableAttributeValuesForSelection($this->productId, $this->normalizeToArrayOfProductAttributes($soldOutCombinations));
            $this->assertEquals(0, count(array_intersect($soldOutCombinations, $availableOptionValues)));

            // others should still be in stock
            $inStockCombinations = array();
            while(true) {
                // pick a random partial combination
                $inStockCombinations = $this->pickRandomOptionValues($i);

                // check against all sold out combinations to make sure
                // our randomly picked combination is in stock
                $inStock = true;
                foreach($soldOutHistory as $soldOut) {
                    $diff = array_diff($soldOut, $inStockCombinations);
                    if(empty($diff)) {
                        $inStock = false;
                        break;
                    }
                }
                // found in stock combo
                if($inStock) {
                    break;
                }
            }

            Store_Product_Attribute_Combination::clearInStockAttributeCombinations();
            $availableOptionValues = Store_Product_Attribute_Combination::findAvailableAttributeValuesForSelection($this->productId, $this->normalizeToArrayOfProductAttributes($inStockCombinations));
            $this->assertGreaterThan(0, count($availableOptionValues));
        }
    }

    public function testCombinationSoldOut()
    {
        $soldOutSKU = $this->pickRandomSKU();
        $this->soldOutCombination($soldOutSKU);

        // get all the attributes that consist sold out SKU
        $combinationValues = Store_Product_Attribute_Combination_Value::find("product_attribute_combination_id = " . $soldOutSKU);
        $attributeValues = array();
        foreach($combinationValues as $cv) {
            $attributeValues[] = $cv->getAttributeValueId();
        }

        Store_Product_Attribute_Combination::clearInStockAttributeCombinations();
        $availableOptionValues = Store_Product_Attribute_Combination::findAvailableAttributeValuesForSelection($this->productId, $this->normalizeToArrayOfProductAttributes($attributeValues));
        $this->assertEquals(0, count(array_intersect($availableOptionValues, $attributeValues)));
    }

    public function testOutOfStockInProductDetailNonSelected()
    {
        $product = new ISC_PRODUCT($this->productId, true);
        $details = $product->getDetailsForAttributeValues(array());
        $this->assertEmpty($details->getSelectedAttributeValues());
        $this->assertEmpty(array_diff(array_keys($this->valueToAttribute), $details->getInStockAttributeValues()));
    }

    public function testOutOfStockInProductDetailSelectNone()
    {
        $product = new ISC_PRODUCT($this->productId, true);
        $details = $product->getDetailsForAttributeValues(array('productAttributeId' => null));
        $this->assertEmpty($details->getSelectedAttributeValues());
        $this->assertEmpty(array_diff(array_keys($this->valueToAttribute), $details->getInStockAttributeValues()));
    }

    public function testOutOfStockInProductDetailSelected()
    {
        // construct the selected option value array in the format {product_attribute_id => attribute_value_id}
        // as it comes from the store front
        $optionValues = $this->pickRandomOptionValues(2);
        $selectedOptionValues = array();
        foreach($optionValues as $attributeValueId) {
            $attributeId = $this->valueToAttribute[$attributeValueId];
            $productAttributeId = $this->attributeToProduct[$attributeId];
            $selectedOptionValues[$productAttributeId] = $attributeValueId;
        }

        $product = new ISC_PRODUCT($this->productId, true);
        $details = $product->getDetailsForAttributeValues($selectedOptionValues);
        $this->assertEquals($selectedOptionValues, $details->getSelectedAttributeValues());
        $this->assertGreaterThan(0, count($details->getInStockAttributeValues($selectedOptionValues)));
    }

    //findOutOfStockCombinations
    public function testFindOutOfStockCombinationsSoldOutCompletely()
    {
        // sold out a random option value
        $soldOutOptionValue = $this->pickRandomOptionValue();
        $this->soldOutOptionValueCompletely($soldOutOptionValue);

        $oosVarInvProdIds = array();
        foreach (\Store_Product_Attribute_Combination::findOutOfStockCombinations() as /** @var Store_Product_Attribute_Combination */$combination) {
            $oosVarInvProdIds[] = $combination->getProductId();
        }

        $this->assertTrue(in_array($this->productId, $oosVarInvProdIds));
    }

    public function testFindOutOfStockCombinationsSoldOutPartially()
    {
        // partially sold out a random option value
        $soldOutOptionValue = $this->pickRandomOptionValue();
        $this->soldOutOptionValueCompletely($soldOutOptionValue);

        $oosVarInvProdIds = array();
        foreach (\Store_Product_Attribute_Combination::findOutOfStockCombinations() as /** @var Store_Product_Attribute_Combination */$combination) {
            $oosVarInvProdIds[] = $combination->getProductId();
        }

        $this->assertTrue(in_array($this->productId, $oosVarInvProdIds));
    }

    public function testFindOutOfStockCombinationsNotSoldOut()
    {
        $oosVarInvProdIds = array();
        foreach (\Store_Product_Attribute_Combination::findOutOfStockCombinations() as /** @var Store_Product_Attribute_Combination */$combination) {
            $oosVarInvProdIds[] = $combination->getProductId();
        }

        $this->assertFalse(in_array($this->productId, $oosVarInvProdIds));
    }

    public function testFindOutOfStockCombinationsSoldOutSku()
    {
        $soldOutSKU = $this->pickRandomSKU();
        $this->soldOutCombination($soldOutSKU);

        $oosVarInvProdIds = array();
        foreach (\Store_Product_Attribute_Combination::findOutOfStockCombinations() as /** @var Store_Product_Attribute_Combination */$combination) {
            $oosVarInvProdIds[] = $combination->getProductId();
        }

        $this->assertTrue(in_array($this->productId, $oosVarInvProdIds));
    }

    private function pickRandomOptionValue()
    {
        $value = $this->pickRandomOptionValues(1);
        return $value[0];
    }

    private function pickRandomOptionValues($num)
    {
        if($num > self::NUM_OPTIONS) {
            throw new Exception("Can't pick " . $num ." random values from " . self::NUM_OPTIONS . " options");
        }

        $ret = array();
        $attributeIds = array_keys($this->attributeToValue);
        while($num > 0) {

            // pick a random option and remove it from future selection
            $index = array_rand($attributeIds);
            $attr = $attributeIds[$index];
            unset($attributeIds[$index]);

            // pick a random option value for the selected option
            $values = $this->attributeToValue[$attr];
            $index = array_rand($values);
            $ret[] = $values[$index];

            $num--;
        }

        return $ret;
    }

    private function pickRandomSKU()
    {
        $index = array_rand($this->sku);
        return $this->sku[$index];
    }

    private function soldOutOptionValueCompletely($attrValueId)
    {
        $query = "attribute_value_id = " . $attrValueId;
        $combinationValues = Store_Product_Attribute_Combination_Value::find($query);
        $combinationIds = array();
        foreach($combinationValues as $val) {
            $combinationId = $val->getProductAttributeCombinationId();
            $sku = Store_Product_Attribute_Combination::find($combinationId)->first();
            $sku->setStockLevel(0);
            $sku->save();
        }
    }

    private function soldOutOptionValuePartially($attributeValues)
    {
        $query = "
            SELECT DISTINCT product_attribute_combination_id,
            (
                SELECT COUNT(*) from [|PREFIX|]product_attribute_combination_values as pacv
                WHERE product_attribute_combination_id = pacv1.product_attribute_combination_id
                and pacv.attribute_value_id in (" . implode(",", $attributeValues) . ")
            ) as MatchCount
            FROM [|PREFIX|]product_attribute_combination_values pacv1
            HAVING MatchCount = " . count($attributeValues);

        $result = Store::getStoreDb()->Query($query);
        $combinationIds = array();
        while($row = Store::getStoreDb()->Fetch($result)) {
            $combinationId = $row['product_attribute_combination_id'];
            $sku = Store_Product_Attribute_Combination::find($combinationId)->first();
            $sku->setStockLevel(0);
            $sku->save();
        }
    }

    private function soldOutCombination($combinationId)
    {
        $oosSku = Store_Product_Attribute_Combination::find($combinationId)->first();
        $oosSku->setStockLevel(0);
        $oosSku->save();
    }

    // copy from tests/Integration/Store/Products/Options.php
    private function createTestProduct()
    {
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

        // now attach a newly created option set
        $products->assignProductType($productId, $this->makeOptionSet());

        return $productId;
    }

    private function removeTestProduct()
    {
        $products = new Store_Product_Gateway();

        $productId = (int)$products->search(array('prodname' => 'TEST_OPTIONS'));
        if ($productId) {
            $this->assertTrue($products->delete($productId), $products->getError());
        }
    }

    private function makeOptionSet()
    {
        $set = new Store_Product_Type();
        $set->setName('test');
        $set->save();

        // add required/madatory options
        for($i = 0 ; $i < self::NUM_OPTIONS; $i++) {
            $attr = $this->makeOption($i, true);
            $productTypeAttribute = $set->addAttribute($attr);
            $productTypeAttribute->setRequired(true);
            $productTypeAttribute->save();
        }

        // add non-required/optional options
        for($i = 0 ; $i < self::NUM_OPTIONS; $i++) {
            $attr = $this->makeOption($i, false);
            $productTypeAttribute = $set->addAttribute($attr);
            $productTypeAttribute->setRequired(false);
            $productTypeAttribute->save();
        }

        return $set;
    }

    private function makeOption($index, $required)
    {
        // use this type as default
        $attrType = new Store_Attribute_Type_Configurable_PickList_Set();

        // save the option
        $attr = new Store_Attribute();
        $attr->setName('test' . $index);
        $attr->setDisplayName('test' . $index);
        $attr->setType($attrType);
        $attr->save();

        if($required) {
            $this->attributeToValue[$attr->getId()] = array();
        }

        // add possible values to the new option
        for($i = 0 ; $i < self::NUM_OPTION_VALUES; $i++) {
            $attrValue = $attr->createAttributeValue();
            $attrValue->setLabel("label" . $i);
            $attrValue->save();
            if($required) {
                $this->attributeToValue[$attr->getId()][] = $attrValue->getId();
                $this->valueToAttribute[$attrValue->getId()] = $attr->getId();
            }
        }
        return $attr;
    }

    private function generateSKU()
    {
        // need to get {attribute_id => product_attribute_id} map since the combination_value table needs product_attribute_id
        $productAttributes = Store_Product_Attribute::findByProductId($this->productId);
        foreach($productAttributes as $pa) {
            $this->attributeToProduct[$pa->getAttributeId()] = $pa->getId();
        }

        $permutations = $this->getAllPermutations(array_values($this->attributeToValue));
        $combinations = array();
        foreach($permutations as $perm) {
            $sku = new Store_Product_Attribute_Combination();
            $sku->setProductId($this->productId);
            $sku->setSku(Interspire_String::generateRandomString(10));
            $sku->setStockLevel(10); // in-stock by default
            $sku->setLowStockLevel(2);
            $sku->save();
            $this->sku[] = $sku->getId();

            foreach($perm as $attrValueId) {
                $productAttributeId = $this->attributeToProduct[$this->valueToAttribute[$attrValueId]];
                $skuValue = new Store_Product_Attribute_Combination_Value();
                $skuValue->setProductAttributeCombinationId($sku->getId());
                $skuValue->setProductAttributeId($productAttributeId);
                $skuValue->setAttributeValueId($attrValueId);
                $skuValue->save();
            }
        }
    }

    private function getAllPermutations($list)
    {
        $indexes = array_fill(0, count($list), 0);
        $permutations = array();

        $dimensions = array();
        foreach($list as $bucket) {
            $dimensions[] = count($bucket);
        }
        $totalPermutations = array_product($dimensions);

        while($totalPermutations) {

            // grab the current permutation
            $entry = array();
            foreach($list as $i => $bucket) {
                $index = $indexes[$i];
                $entry[] = $bucket[$index];
            }
            $permutations[] = $entry;

            // increase the all the indexes correctly
            foreach($list as $i => $bucket) {
                $indexes[$i] = ($indexes[$i] + 1) % count($bucket);
                if($indexes[$i] != 0) {
                    break;
                }
            }

            $totalPermutations--;
        }

        return $permutations;
    }
}
