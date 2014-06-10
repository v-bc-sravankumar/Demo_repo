<?php

require_once dirname(__FILE__) . '/../../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Attribute_Rule extends ModelLike_TestCase
{
    private $testRules = array();

    public function tearDown()
    {
        foreach($this->testRules as $rule) {
            $rule->delete();
        }
        $this->testRules = array();
    }

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getProductId';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setProductId';
	}

	protected function _getCrudSmokeValue1 ()
	{
		return 24;
	}

	protected function _getCrudSmokeValue2 ()
	{
		return 25;
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Attribute_Rule;
		$model->setProductId($this->_getCrudSmokeValue1())
			->setPriceAdjuster(new Store_ValueAdjuster_Relative)
			->setWeightAdjuster(new Store_ValueAdjuster_Relative);
		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'sort_order';
	}

	protected function _getFindSmokeSetPattern ()
	{
		return '10%s';
	}

	protected function _getFindSmokeLikePattern ()
	{
		return '10%';
	}

	protected function _getFindSmokeGetMethod ()
	{
		return 'getSortOrder';
	}

	protected function _getFindSmokeSetMethod ()
	{
		return 'setSortOrder';
	}

	public function dataProviderCloneCorrectlySubClones ()
	{
		return array(
			array('getPriceAdjuster'),
			array('getWeightAdjuster'),
		);
	}

	public function testSetGetSortOrder ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertInstanceOf('Store_Product_Attribute_Rule', $model->setSortOrder(2));
		$this->assertSame(2, $model->getSortOrder());
	}

	public function testDeleteCascadesToProductAttributeRuleConditions ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Rule");

		$attribute = new Store_Attribute;
		$attribute
			->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$productAttribute = new Store_Product_Attribute;
		$productAttribute
			->setProductId($model->getProductId())
			->setAttributeId($attribute->getId());
		$this->assertTrue($productAttribute->save(), "failed to save Store_Product_Attribute");

		$attributeValue = new Store_Attribute_Value;
		$attributeValue
			->setAttributeId($attribute->getId())
			->setLabel('bar');
		$this->assertTrue($attributeValue->save(), "failed to save Store_Attribute_Value");

		$productAttributeRuleCondition = new Store_Product_Attribute_Rule_Condition;
		$productAttributeRuleCondition
			->setProductAttributeRuleId($model->getId())
			->setProductAttributeId($productAttribute->getId())
			->setAttributeValueId($attributeValue->getId());
		$this->assertTrue($productAttributeRuleCondition->save(), "failed to save Store_Product_Attribute_Rule_Condition");

		$this->assertTrue($model->delete(), "failed to delete Store_Product_Attribute_Rule");

		$this->assertFalse($productAttributeRuleCondition->load(), "loading Store_Product_Attribute_Rule_Condition worked but should have failed");
	}

	public function testSetGetPriceAdjuster ()
	{
		$model = $this->_getCrudSmokeInstance();
		$adjuster = new Store_ValueAdjuster_Percentage;
		$this->assertInstanceOf('Store_Product_Attribute_Rule', $model->setPriceAdjuster($adjuster));
		$this->assertSame($adjuster, $model->getPriceAdjuster($adjuster));
	}

	public function testSetGetWeightAdjuster ()
	{
		$model = $this->_getCrudSmokeInstance();
		$adjuster = new Store_ValueAdjuster_Relative;
		$this->assertInstanceOf('Store_Product_Attribute_Rule', $model->setWeightAdjuster($adjuster));
		$this->assertSame($adjuster, $model->getWeightAdjuster($adjuster));
	}

	public function testGetPriceAdjusterReturnsNullForNewInstance ()
	{
		$model = new Store_Product_Attribute_Rule;
		$this->assertNull($model->getPriceAdjuster());
	}

	public function testGetWeightAdjusterReturnsNullForNewInstance ()
	{
		$model = new Store_Product_Attribute_Rule;
		$this->assertNull($model->getWeightAdjuster());
	}

	public function testGetPriceAdjusterForLoadedInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Rule");

		$id = $model->getId();
		$model = new Store_Product_Attribute_Rule;
		$model->load($id);

		$adjuster = $model->getPriceAdjuster();
		$this->assertInstanceOf('Store_ValueAdjuster_Relative', $adjuster);
		$this->assertSame($adjuster, $model->getPriceAdjuster(), "internal caching failed");
	}

	public function testGetWeightAdjusterForLoadedInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Rule");

		$id = $model->getId();
		$model = new Store_Product_Attribute_Rule;
		$model->load($id);

		$adjuster = $model->getWeightAdjuster();
		$this->assertInstanceOf('Store_ValueAdjuster_Relative', $adjuster);
		$this->assertSame($adjuster, $model->getWeightAdjuster(), "internal caching failed");
	}

	public function testSetNullPriceAdjuster ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setPriceAdjuster(null);
		$this->assertNull($model->getPriceAdjuster());
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Rule");
		$this->assertTrue($model->load(), "failed to save-load Store_Product_Attribute_Rule");
		$this->assertNull($model->getPriceAdjuster());
	}

	public function testSetNullWeightAdjuster ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setWeightAdjuster(null);
		$this->assertNull($model->getWeightAdjuster());
		$this->assertTrue($model->save(), "failed to save Store_Product_Attribute_Rule");
		$this->assertTrue($model->load(), "failed to save-load Store_Product_Attribute_Rule");
		$this->assertNull($model->getWeightAdjuster());
	}

	public function testGetImage ()
	{
		$model = new Store_Product_Attribute_Rule;
		$model->setImageExtension('jpg');
		$this->assertTrue($model->save(), "failed to save model");

		$image = $model->getImage();
		$this->assertInstanceOf('Store_Product_Attribute_Rule_Image', $image);
	}

	public function testDeleteCascadesToImageFiles ()
	{
		// @todo needs rewriting for Store_Product_Attribute_Rule_Image
		/*
		$model = $this->_getCrudSmokeInstance();
		$model->setImageExtension('jpg');
		$this->assertTrue($model->save(), "failed to save model");
		$this->assertTrue(touch($model->getImagePath(true)), "failed to create dummy image file");
		$this->assertTrue($model->delete(), "failed to delete model");
		$this->assertFalse(file_exists($model->getImagePath(true)), "dummy image file still exists");
		*/
	}

	public function testGetAdjustedPriceWithNoAdjuster ()
	{
		$model = new Store_Product_Attribute_Rule;
		$this->assertSame(10.0, $model->getAdjustedPrice(10.0));
	}

	public function testGetAdjustedWeightWithNoAdjuster ()
	{
		$model = new Store_Product_Attribute_Rule;
		$this->assertSame(10.0, $model->getAdjustedWeight(10.0), "adjusted value mismatch");
	}

	public function testGetAdjustedPrice ()
	{
		$model = new Store_Product_Attribute_Rule;

		$adjuster = new Store_ValueAdjuster_Percentage;
		$adjuster->setAdjustment(10);

		$model->setPriceAdjuster($adjuster);

		$this->assertEquals(12.1, $model->getAdjustedPrice(11), "adjusted value mismatch", 0.0001);
	}

	public function testGetAdjustedWeight ()
	{
		$model = new Store_Product_Attribute_Rule;

		$adjuster = new Store_ValueAdjuster_Relative;
		$adjuster->setAdjustment(10);

		$model->setWeightAdjuster($adjuster);

		$this->assertEquals(21.1, $model->getAdjustedWeight(11.1), "adjusted value mismatch", 0.0001);
	}

	public function testPartialSaveMaintainsPriceWeightAdjusters ()
	{
		// ISC-2187 regression test

		$model = new Store_Product_Attribute_Rule;

		$price = new Store_ValueAdjuster_Percentage;
		$price->setAdjustment(10);

		$weight = new Store_ValueAdjuster_Relative;
		$weight->setAdjustment(20);

		$model
			->setProductId($this->_getCrudSmokeValue1())
			->setPriceAdjuster($price)
			->setWeightAdjuster($weight)
			->setSortOrder(0);
		$this->assertTrue($model->save(), "failed to save model");

		$id = $model->getId();

		$model = new Store_Product_Attribute_Rule;
		$model
			->setId($id)
			->setSortOrder(1);
		$this->assertTrue($model->save(), "failed to save model(2)");

		$model = Store_Product_Attribute_Rule::find($id)->first();
		$this->assertInstanceOf('Store_Product_Attribute_Rule', $model, "failed to load model");
		$this->assertInstanceOf(get_class($price), $model->getPriceAdjuster(), "priceAdjuster type mismatch");
		$this->assertSame($price->getAdjustment(), $model->getPriceAdjuster()->getAdjustment(), "priceAdjuster value mismatch");
		$this->assertInstanceOf(get_class($weight), $model->getWeightAdjuster(), "weightAdjuster type mismatch");
		$this->assertSame($weight->getAdjustment(), $model->getWeightAdjuster()->getAdjustment(), "weightAdjuster value mismatch");
	}

	public function testCloneMaintainsPriceWeightAdjusters ()
	{
		$orig = new Store_Product_Attribute_Rule;

		$price = new Store_ValueAdjuster_Percentage;
		$price->setAdjustment(10);

		$weight = new Store_ValueAdjuster_Relative;
		$weight->setAdjustment(20);

		$orig
			->setProductId($this->_getCrudSmokeValue1())
			->setPriceAdjuster($price)
			->setWeightAdjuster($weight)
			->setSortOrder(0)
			->setStop(0)
			->setImageExtension('')
			->setPurchasingDisabled(false)
			->setPurchasingDisabledMessage('')
			->setPurchasingHidden(false)
			->setEnabled(1)
			->setImageModifiedAt(time());

		$this->assertTrue($orig->save(), "failed to save original");

		$copy = $orig->copy();

		$copy
			->setProductId(null)
			->setProductHash('12345')
			->setSortOrder(1);

		$this->assertTrue($copy->save(), "failed to save copy");

		$model = Store_Product_Attribute_Rule::find('product_hash = 12345')->first();

		$this->assertInstanceOf('Store_Product_Attribute_Rule', $model, "failed to load model");
		$this->assertInstanceOf(get_class($price), $model->getPriceAdjuster(), "priceAdjuster type mismatch");
		$this->assertSame($price->getAdjustment(), $model->getPriceAdjuster()->getAdjustment(), "priceAdjuster value mismatch");
		$this->assertInstanceOf(get_class($weight), $model->getWeightAdjuster(), "weightAdjuster type mismatch");
		$this->assertSame($weight->getAdjustment(), $model->getWeightAdjuster()->getAdjustment(), "weightAdjuster value mismatch");
	}

	public function testCloneImage()
	{
		$orig = new Store_Product_Attribute_Rule;

		$orig
			->setProductId($this->_getCrudSmokeValue1())
			->setPriceAdjuster(null)
			->setWeightAdjuster(null)
			->setSortOrder(0)
			->setStop(0)
			->setImageExtension('.jpg')
			->setPurchasingDisabled(false)
			->setPurchasingDisabledMessage('')
			->setPurchasingHidden(false)
			->setEnabled(1)
			->setImageModifiedAt(time());

		$this->assertTrue($orig->save(), "failed to save original");

		$origSource =  __DIR__ . '/images/1x1.jpg';
		$origDest = $orig->getImage()->getSourcePath(true);

		//place file for the original rule
		$this->assertTrue(copy($origSource, $origDest), 'original image copy failed');
		$this->assertTrue(file_exists($origDest), 'original image does not exist.');

		$copy = $orig->copy();

		$copy
			->setProductId(null)
			->setProductHash('12345')
			->setSortOrder(1)
			->setImageExtension('test');

		$this->assertTrue($copy->save(), "failed to save copy");

		$this->assertTrue($copy->cloneImage($orig), "failed to clone image");
		$this->assertEquals($copy->getImageExtension(), '.jpg', "copy has wrong file extension");

		$image = $copy->getImage();
		$this->assertInstanceOf('Store_Product_Attribute_Rule_Image', $image);

		$clonedPath = $copy->getImage()->getSourcePath(true);
		$this->assertTrue(file_exists($clonedPath), 'cloned image does not exist.');

		unlink($origDest);
		unlink($clonedPath);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUpdateSortOrderWithNonExistRuleId()
    {
        \Store_Product_Attribute_Rule::updateSortOrderCascade(-1, 0);
    }

    public function testUpdateSortOrderMoveToLowerIndex()
    {
        $this->makeTestRules();

        $ruleToMove = $this->testRules[4];

        $this->assertEquals(4, $ruleToMove->getSortOrder());
        $this->assertTrue(\Store_Product_Attribute_Rule::updateSortOrderCascade($ruleToMove->getId(), 0));

        $this->assertRuleSortOrder($this->testRules[0], 1);
        $this->assertRuleSortOrder($this->testRules[1], 2);
        $this->assertRuleSortOrder($this->testRules[2], 3);
        $this->assertRuleSortOrder($this->testRules[3], 4);
        $this->assertRuleSortOrder($this->testRules[4], 0);
        $this->assertRuleSortOrder($this->testRules[5], 5);
    }

    public function testUpdateSortOrderMoveBelowLowerBound()
    {
        $this->makeTestRules();

        $ruleToMove = $this->testRules[4];

        $this->assertEquals(4, $ruleToMove->getSortOrder());
        $this->assertTrue(\Store_Product_Attribute_Rule::updateSortOrderCascade($ruleToMove->getId(), -10));

        $this->assertRuleSortOrder($this->testRules[0], 1);
        $this->assertRuleSortOrder($this->testRules[1], 2);
        $this->assertRuleSortOrder($this->testRules[2], 3);
        $this->assertRuleSortOrder($this->testRules[3], 4);
        $this->assertRuleSortOrder($this->testRules[4], 0);
        $this->assertRuleSortOrder($this->testRules[5], 5);
    }

    public function testUpdateSortOrderMoveToHigherIndex()
    {
        $this->makeTestRules();

        $ruleToMove = $this->testRules[4];

        $this->assertEquals(4, $ruleToMove->getSortOrder());
        $this->assertTrue(\Store_Product_Attribute_Rule::updateSortOrderCascade($ruleToMove->getId(), 9));

        // assert the effected rules are updated accordingly
        $this->assertRuleSortOrder($this->testRules[3], 3);
        $this->assertRuleSortOrder($this->testRules[4], 9);
        $this->assertRuleSortOrder($this->testRules[5], 4);
        $this->assertRuleSortOrder($this->testRules[6], 5);
        $this->assertRuleSortOrder($this->testRules[7], 6);
        $this->assertRuleSortOrder($this->testRules[8], 7);
        $this->assertRuleSortOrder($this->testRules[9], 8);
    }

    public function testUpdateSortOrderMoveAboveUpperBound()
    {
        $this->makeTestRules();

        $ruleToMove = $this->testRules[4];

        $this->assertEquals(4, $ruleToMove->getSortOrder());
        $this->assertTrue(\Store_Product_Attribute_Rule::updateSortOrderCascade($ruleToMove->getId(), 100));

        // assert the effected rules are updated accordingly
        $this->assertRuleSortOrder($this->testRules[3], 3);
        $this->assertRuleSortOrder($this->testRules[4], 9);
        $this->assertRuleSortOrder($this->testRules[5], 4);
        $this->assertRuleSortOrder($this->testRules[6], 5);
        $this->assertRuleSortOrder($this->testRules[7], 6);
        $this->assertRuleSortOrder($this->testRules[8], 7);
        $this->assertRuleSortOrder($this->testRules[9], 8);
    }

    private function assertRuleSortOrder($rule, $expectedIndex)
    {
        $rule->load();
        $this->assertEquals($expectedIndex, $rule->getSortOrder());
    }

    private function makeTestRules()
    {
        $this->testRules = array();
        for($index = 0 ; $index < 10; $index++) {
            $rule = new \Store_Product_Attribute_Rule();
            $rule->setProductId(9999);
            $rule->setSortOrder($index);
            $rule->save();
            $this->testRules[] = $rule;
        }
    }
}
