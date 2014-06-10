<?php

class Unit_Lib_Store_Customer_Group_Discount extends Interspire_UnitTest
{
	protected $_createdModels = array();

	public function testSaveLoadCustomerGroupDiscount()
	{
		$saveDiscount = new Store_Customer_Group_Discount();
		$saveDiscount
			->setCustomerGroupId(1)
			->setCategoryOrProductId(2)
			->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
			->setDiscountAmount(5)
			->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED)
			->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		if (!$saveDiscount->save()) {
			$this->fail('failed to save the customer group discount');
			return;
		}

		$this->_createdModels[] = $saveDiscount;

		$loadDiscount = new Store_Customer_Group_Discount();
		if (!$loadDiscount->load($saveDiscount->getId())) {
			$this->fail('failed to load saved customer group discount');
			return;
		}

		$this->assertEquals($saveDiscount->getCustomerGroupId(), $loadDiscount->getCustomerGroupId(), "customer group id doesn't match");
		$this->assertEquals($saveDiscount->getCategoryOrProductId(), $loadDiscount->getCategoryOrProductId(), "category/product id doesn't match");
		$this->assertEquals($saveDiscount->getDiscountType(), $loadDiscount->getDiscountType(), "discount type doesn't match");
		$this->assertEquals($saveDiscount->getDiscountAmount(), $loadDiscount->getDiscountAmount(), "discount amount doesn't match");
		$this->assertEquals($saveDiscount->getDiscountMethod(), $loadDiscount->getDiscountMethod(), "discount method doesn't match");
		$this->assertEquals($saveDiscount->getAppliesTo(), $loadDiscount->getAppliesTo(), "applies-to doesn't match");
	}

	public function testSetProductDiscountTypeSetsAppliesToNotApplicable()
	{
		$discount = new Store_Customer_Group_Discount();
		$discount->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_PRODUCT);

		$this->assertSame(Store_Customer_Group_Discount::APPLIES_TO_NOT_APPLICABLE, $discount->getAppliesTo(), 'discount applies-to should be not applicable');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testIsDiscountAmountValid()
	{
		$discount = new Store_Customer_Group_Discount();
		$discount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(2)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(500)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PERCENT)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$discount->isValid();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testIsCategoryOrProductIdValid()
	{
		$discount = new Store_Customer_Group_Discount();
		$discount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(0)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(5)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PERCENT)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$discount->isValid();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testIsCategoryOrProductDuplicated()
	{
		$saveDiscount = new Store_Customer_Group_Discount();
		$saveDiscount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(2)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(5)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$saveDiscount->save();
		$this->_createdModels[] = $saveDiscount;

		$discount = new Store_Customer_Group_Discount();
		$discount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(2)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(5)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PERCENT)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$discount->isValid();
	}

	public function testValidDiscount()
	{
		$saveDiscount = new Store_Customer_Group_Discount();
		$saveDiscount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(2)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(5)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$saveDiscount->save();
		$this->_createdModels[] = $saveDiscount;

		$discount = new Store_Customer_Group_Discount();
		$discount
		->setCustomerGroupId(1)
		->setCategoryOrProductId(3)
		->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
		->setDiscountAmount(5)
		->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PERCENT)
		->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS);

		$discount->isValid();

	}

	public function tearDown()
	{
		foreach ($this->_createdModels as /** @var DataModel_Record $model */$model) {
			$model->delete();
		}

		parent::tearDown();
	}


}
