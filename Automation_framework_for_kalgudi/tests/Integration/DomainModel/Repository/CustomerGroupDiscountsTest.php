<?php
use \Repository\CustomerGroupDiscounts;

class DomainModel_Repository_CustomerGroupDiscountsTest extends Interspire_IntegrationTest
{
	protected $_createdModels = array();

	public function testCRUD()
	{
		//create
		$data = new stdClass();
		$data->type = 'product';
		$data->group_id = 1;
		$data->cat_or_prod_id = 2;
		$data->amount = 3.00;
		$data->method = 'fixed';

		$repository = new CustomerGroupDiscounts();
		$result = $repository->addGroupDiscount($data);


		$discount = new Store_Customer_Group_Discount();
		if (!$discount->load($result->getId())) {
			$this->fail('failed to load saved customer group discount');
			return;
		}

		$this->assertEquals(1, $discount->getCustomerGroupId(), "customer group id doesn't match");
		$this->assertEquals(2, $discount->getCategoryOrProductId(), "category/product id doesn't match");
		$this->assertEquals(\Store_Customer_Group_Discount::DISCOUNT_TYPE_PRODUCT, $discount->getDiscountType(), "discount type doesn't match");
		$this->assertEquals(3.00, $discount->getDiscountAmount(), "discount amount doesn't match");
		$this->assertEquals(\Store_Customer_Group::DISCOUNT_METHOD_FIXED, $discount->getDiscountMethod(), "discount method doesn't match");
		$this->assertEquals(\Store_Customer_Group_Discount::APPLIES_TO_NOT_APPLICABLE, $discount->getAppliesTo(), "applies-to doesn't match");

		//update
		$data = new stdClass();
		$data->id = $result->getId();
		$data->type = 'category';
		$data->group_id = 1;
		$data->cat_or_prod_id = 5;
		$data->amount = 6.55;
		$data->method = 'percent';
		$data->applies_to = 'CATEGORY_AND_SUBCATS';

		$result = $repository->updateGroupDiscount($data);
		if (!$discount->load($result->getId())) {
			$this->fail('failed to load saved customer group discount');
			return;
		}

		$this->assertEquals(1, $discount->getCustomerGroupId(), "customer group id doesn't match");
		$this->assertEquals(5, $discount->getCategoryOrProductId(), "category/product id doesn't match");
		$this->assertEquals(\Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY, $discount->getDiscountType(), "discount type doesn't match");
		$this->assertEquals(6.55, $discount->getDiscountAmount(), "discount amount doesn't match");
		$this->assertEquals(\Store_Customer_Group::DISCOUNT_METHOD_PERCENT, $discount->getDiscountMethod(), "discount method doesn't match");
		$this->assertEquals(\Store_Customer_Group_Discount::APPLIES_TO_CATEGORY_AND_SUBCATS, $discount->getAppliesTo(), "applies-to doesn't match");

		//delete
		$data = new stdClass();
		$data->id = $result->getId();
		$result = $repository->removeGroupDiscount($data);
		$this->assertFalse($discount->load($data));
	}

	public function testValidateDuplicateCatOrProd()
	{
		$data = new stdClass();
		$data->type = 'product';
		$data->group_id = 1;
		$data->cat_or_prod_id = 2;
		$data->amount = 3.00;
		$data->method = 'fixed';

		$repository = new CustomerGroupDiscounts();
		$saved = $repository->addGroupDiscount($data);

		$this->_createdModels[] = $saved;

		$result = $repository->validateDuplicateCatOrProd((array)($data));
		$this->assertFalse($result['is_valid']);
	}


	public function testIsUpdateValid()
	{
		$data = new stdClass();
		$data->type = 'product';
		$data->group_id = 1;
		$data->cat_or_prod_id = 2;
		$data->amount = 101.00;
		$data->method = 'percent';

		$repository = new CustomerGroupDiscounts();

		$discount = new \Store_Customer_Group_Discount();
		$discount->setId($data->id);

		$discount
			->setCategoryOrProductId($data->cat_or_prod_id)
			->setDiscountType($data->type)
			->setDiscountAmount($data->amount)
			->setDiscountMethod($data->method)
			->setAppliesTo($data->applies_to);


		$repository->isUpdateValid($discount);
		$errors = $repository->getErrors();
		$this->assertFalse(empty($errors));
	}


	public function testCheckDuplicateCatOrProdIdsDuringSwap()
	{
		$repository = new CustomerGroupDiscounts();

		$discount1 = $this->addDiscountWithCatOrProdId(2);
		$discount2 = $this->addDiscountWithCatOrProdId(3);

		//swap cat_or_prod_id between discounts
		$discount1->cat_or_prod_id = 3;
		$discount2->cat_or_prod_id = 2;

		$updatedDiscounts = array($discount1, $discount2);

		$this->assertTrue($repository->checkDuplicateCatOrProdIds(
				$updatedDiscounts,
				'product',
				1));
	}

	public function testCheckDuplicateCatOrProdIdsForNewIds()
	{
		$repository = new CustomerGroupDiscounts();

		$discount1 = $this->addDiscountWithCatOrProdId(2);
		$discount2 = $this->addDiscountWithCatOrProdId(3);

		//add new cat_or_prod_ids
		$discount1->cat_or_prod_id = 5;
		$discount2->cat_or_prod_id = 6;

		$updatedDiscounts = array($discount1, $discount2);

		$this->assertTrue($repository->checkDuplicateCatOrProdIds(
				$updatedDiscounts,
				'product',
				1));
	}

	public function testCheckDuplicateCatOrProdIdsForDuplicates()
	{
		$repository = new CustomerGroupDiscounts();

		$discount1 = $this->addDiscountWithCatOrProdId(2);
		$discount2 = $this->addDiscountWithCatOrProdId(3);

		//create duplicate cat_or_prod_ids
		$discount1->cat_or_prod_id = 2;
		$discount2->cat_or_prod_id = 2;

		$updatedDiscounts = array($discount1, $discount2);

		$this->assertFalse($repository->checkDuplicateCatOrProdIds(
				$updatedDiscounts,
				'product',
				1));
	}

	private function addDiscountWithCatOrProdId($id)
	{
		$repository = new CustomerGroupDiscounts();

		$discount = new stdClass();
		$discount->type = 'product';
		$discount->group_id = 1;
		$discount->cat_or_prod_id = $id;
		$discount->amount = 3.00;
		$discount->method = 'fixed';
		$saved = $repository->addGroupDiscount($discount);

		$discount->id = $saved->getId();
		$this->_createdModels[] = $saved;

		return $discount;
	}

	public function tearDown()
	{
		foreach ($this->_createdModels as /** @var DataModel_Record $model */$model) {
			$model->delete();
		}

		parent::tearDown();
	}

}