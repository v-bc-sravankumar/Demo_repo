<?php
use Store\Product\Sku\Generator;
use Store\Product\Sku\Template;
use Store\Product\Sku\Template\Token;
use Store\Product\Sku\Template\Rule\AbstractRule;

class GeneratorTest extends PHPUnit_Framework_TestCase
{
    private $defaultType = null;
    private $defaultTemplate = null;
    private $generator = null;

    public function setUp()
    {
        $this->defaultType = new Store_Attribute_Type_Configurable_PickList_Set();
        $this->defaultTemplate = Template::loadFromArray(
            array(
                'tokens' => array(
                    array(
                        'type' => Token::TYPE_PRODUCT,
                        'data' => null,
                        'rule' => array(
                            'type' => AbstractRule::TYPE_ABBR,
                            'data' => null,
                        ),
                    ),
                ),
            ));
        $this->generator = new Generator();
    }

    public function tearDown()
    {
        Store_Product_Type_Attribute_Value::find()->deleteAll();
        Store_Product_Type_Attribute::find()->deleteAll();
        Store_Product_Attribute_Combination_Value::find()->deleteAll();
        Store_Product_Attribute_Combination::find()->deleteAll();
        Store_Product_Attribute::find()->deleteAll();
        Store_Attribute_Value::find()->deleteAll();
        Store_Attribute::find()->deleteAll();
        Store_Product_Type::find()->deleteAll();
        $this->removeSavedProduct();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetProductWithoutIdentifier()
    {
       $this->generator->setProduct(array('name' => 'test'));
    }

    public function testSetProductUseIdAsHash()
    {
        $this->generator->setProduct(array(
            'id' => 1,
            'use_hash' => 'true',
            'name' => "pending product",
            'brand_id' => 0,
        ));

        $this->assertStringStartsWith('product_hash',
            $this->generator->getProductIdentifierWhereClause());
    }

    public function testGenerateCombinationFromPendingProductShouldIgnoreNonRequiredOptions()
    {
        $this->setPendingProductWithOptions(array('optional' => array('v1', 'v2')), false);
        $this->assertEmpty($this->generator->generateSkuCombinations());
    }

    public function testGenerateCombinationFromSavedProductShouldIgnoreNonRequiredOptions()
    {
        $this->setSavedProductWithOptions(array('optional' => array('v1', 'v2')), false);
        $this->assertEmpty($this->generator->generateSkuCombinations());
    }

    public function testGenerateCombinationFromPendingProductShouldIncludeRequiredOptions()
    {
        $this->setPendingProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
                'memory' => array('2G', '4G'),
            ));

        $combinations = $this->generator->generateSkuCombinations();

        $this->assertCount(4, $combinations);
        $this->verifyCombinations($combinations);
    }

    public function testGenerateCombinationFromSavedProductShouldIncludeRequiredOptions()
    {
        $this->setSavedProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
                'memory' => array('2G', '4G'),
            ));

        $combinations = $this->generator->generateSkuCombinations();

        $this->assertCount(4, $combinations);
        $this->verifyCombinations($combinations);
    }

    public function testGenerateCombinationFromPendingProductWithOptionValueSubset()
    {
        $set = $this->setPendingProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
            ));

        // use a subset of all values
        $useValue = \Store_Attribute_Value::find('label = "1.5G"');
        $this->useCustomizedOptionValues($set, 'cpu', $useValue);

        $combinations = $this->generator->generateSkuCombinations();

        $this->assertCount(1, $combinations);
        $this->verifyCombinations($combinations);

        // check the returned value is indeed the one we customized
        $retValue = array_values($combinations[0]);
        $this->assertEquals($useValue->first()->getId(), $retValue[0]);
    }

    public function testGenerateCombinationFromSavedProductWithOptionValueSubset()
    {
        $set = $this->setSavedProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
            ));

        // use a subset of all values
        $useValue = \Store_Attribute_Value::find('label = "1.5G"');
        $this->useCustomizedOptionValues($set, 'cpu', $useValue);

        $combinations = $this->generator->generateSkuCombinations();

        $this->assertCount(1, $combinations);
        $this->verifyCombinations($combinations);

        // check the returned value is indeed the one we customized
        $retValue = array_values($combinations[0]);
        $this->assertEquals($useValue->first()->getId(), $retValue[0]);
    }

    /**
     * @expectedException \Store\Product\Sku\SkuCapReachedException
     */
    public function testGenerateCap()
    {
        \Store_Config::override('AutoSkuGenerationCap', 200);
        $this->setPendingproductWithOptions(
            array(
                'size' => array('XS', 'S', 'M', 'L', 'XL'),
                'color' => array('red', 'blue', 'green', 'white', 'black'),
                'logo' => array('lion', 'deer', 'dragon', 'octopus', 'wolf'),
                'sleeve' => array('short', 'long'),
            ));

        $combinations = $this->generator->generateSkuCombinations();
        \Store_Config::override('AutoSkuGenerationCap',
            \Store_Config::getOriginal('AutoSkuGenerationCap'));
    }

    public function testSaveGeneratedCombinationsToDb()
    {
        $this->setSavedProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
            ));
        $this->generator->setTemplate($this->defaultTemplate);

        $combinations = $this->generator->generate();

        $this->assertCount(2, $combinations);
        foreach($combinations as $comb) {
            $this->assertInstanceOf('Store_Product_Attribute_Combination',
                Store_Product_Attribute_Combination::find($comb->getId())->first());
        }
    }

    public function testSaveDuplicatedCombinationsToDb()
    {
        $this->setSavedProductWithOptions(
            array(
                'cpu' => array('1.5G', '1.8G'),
            ));
        $this->generator->setTemplate($this->defaultTemplate);

        $combinations = $this->generator->generate();
        $combinations = $this->generator->generate();

        $this->assertCount(0, $combinations);
    }

    private function verifyCombinations($combinations)
    {
        $this->verifyProductAttributeIdToValueMapping($combinations);
        $this->verifyCombinationsAreUnique($combinations);
    }

    /**
     * Verifies the return data has correct product_attribute_id to attribute_value_id mappings
     * @param $combinations Array of combinations
     */
    private function verifyProductAttributeIdToValueMapping($combinations)
    {
        // Get all the product_attribute_id to attribute_value_id mapping from db
        $attributeValuesByProductAttributeId = array();
        $productAttributes = \Store_Product_Attribute::find($this->generator->getProductIdentifierWhereClause());
        foreach($productAttributes as $pa) {
            $attributeValues = $pa->getAttributeValues();
            foreach($attributeValues as $value) {
                $attributeValuesByProductAttributeId[$pa->getId()][] = $value->getId();
            }
        }

        // check the mapping validity in the combinations
        foreach($combinations as $comb) {
            foreach($comb as $productAttributeId => $attributeValueId) {
                $this->assertArrayHasKey($productAttributeId, $attributeValuesByProductAttributeId);
                $this->assertContains($attributeValueId, $attributeValuesByProductAttributeId[$productAttributeId]);
            }
        }
    }

    /**
     * Verifies each combination is unique
     * @param $combinations Array of combinations
     */
    private function verifyCombinationsAreUnique($combinations)
    {
        // turn each combination into concatenated string
        // for unique comparison
        $valuesAsString = array();
        foreach($combinations as $comb) {
            $values = array_values($comb);
            sort($values, SORT_NUMERIC);
            $valuesAsString[] = implode(",", $values);
        }

        // filter out duplicate value if any
        $unique = array_unique($valuesAsString);

        // confirm uniqueness as no duplicate is removed
        $this->assertEquals(count($unique), count($valuesAsString));
    }

    /**
     * Feed a newly creates a pending product with given options to the generator
     *
     * @param $options Array [ option_name => [ option_values ] ]
     * @param $required Boolean
     * @return Store_Product_Type
     */
    private function setPendingProductWithOptions($options, $required = true)
    {
        // make the product
        $product = $this->makePendingProduct();

        // feed product to generator
        $this->generator->setProduct($product);

        // make the option set with given options
        $set = $this->makeOptionSet($options, $required);

        // pending product has no recored in database
        // therefore we need to clone attributes from option set
        // and set them to use product hash
        foreach($set->getProductTypeAttributes() as $productTypeAttribute) {
            $productAttribute = $productTypeAttribute
                ->cloneToProductAttribute()
                ->setProductHash($product['id']);

            $productAttribute->save();
        }

        return $set;
    }

    /**
     * Feed a newly creates a pending product with given options to the generator
     *
     * @param $options Array [ option_name => [ option_values ] ]
     * @param $required Boolean
     * @return Store_Product_Type
     */
    private function setSavedProductWithOptions($options, $required = true)
    {
        // make the product
        $product = $this->makeSavedProduct();

        // feed product to generator
        $this->generator->setProduct($product);

        // make the option set with given options
        $set = $this->makeOptionSet($options, $required);

        // attac the option set to the saved product
        $products = new Store_Product_Gateway();
        $products->assignProductType($product['id'], $set);

        return $set;
    }

    private function makePendingProduct()
    {
        return array(
            'id' => md5(uniqid('', true)),
            'name' => "pending product",
            'brand_id' => 0,
        );
    }

    private function makeSavedProduct()
    {
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

        $products = new Store_Product_Gateway();
        $productId = (int)$products->add($data);
        $this->assertGreaterThan(0, $productId, $products->getError());

        return array(
            'id' => $productId,
            'name' => $data['prodname'],
            'brand_id' => $data['prodbrandid'],
        );
    }

    private function removeSavedProduct()
    {
        $products = new Store_Product_Gateway();

        $productId = (int)$products->search(array('prodname' => 'TEST_OPTIONS'));
        if ($productId) {
            $this->assertTrue($products->delete($productId), $products->getError());
        }
    }


    /**
     * Create an option set with given options
     *
     * @param $options Array
     * @param $isRequired Boolean
     */
    private function makeOptionSet($options, $isRequired = true)
    {
        // create the optioin set
        $set = new Store_Product_Type();
        $set->setName('test');
        $set->save();

        // convert option spec into actual models
        $attributes = $this->toAttributeModels($options);

        // attach all the options to the set
        foreach($attributes as $attr) {
            $productTypeAttribute = $set->addAttribute($attr);
            $productTypeAttribute->setRequired($isRequired);
            $productTypeAttribute->save();
        }

        return $set;
    }

    /**
     * Converts associative array in format of
     * [ option_name => [ option_value ] ]
     * to Store_Attribute array
     *
     * @param $array Array
     * @return Array of Store_Attribute instances
     */
    private function toAttributeModels($array)
    {
        $attributes = array();

        foreach($array as $name => $values) {

            // create the option itself
            $attr = new Store_Attribute();
            $attr->setName($name);
            $attr->setDisplayName($name);
            $attr->setType($this->defaultType);
            $attr->save();

            // add all possible values to the new option
            foreach($values as $value) {
                $attrValue = $attr->createAttributeValue();
                $attrValue->setLabel($value);
                $attrValue->save();
            }

            $attributes[] = $attr;
        }

        return $attributes;
    }

    /**
     * Use only a subset of all possible values from an given option
     * @param $set Store_Product_Type the set to customize
     * @param $optionName String name of the option to customize
     * @param $customizedValues QueryIterator of Store_Attribute_Value for allowed option values for this set
     */
    private function useCustomizedOptionValues($set, $optionName, $customizedValues)
    {
        // find the set-attribute mapping by option name
        $typeAttribute = Store_Product_Type_Attribute::find('product_type_id = ' . $set->getId() . ' AND display_name = "' . $optionName . '"')->first();

        // add customized values
        foreach($customizedValues as $customizedValue) {
            $typeAttribute->addValue($customizedValue);
        }
    }

}
