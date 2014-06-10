<?php
require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

class Unit_Lib_Store_Customer_Group extends ModelLike_TestCase
{
	protected $_createdModels = array();

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Customer_Group();
		$model->setName($this->_getCrudSmokeValue1());

		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'groupname';
	}

	protected function _createGroup()
	{
		$saveModel = new Store_Customer_Group();
		$saveModel
			->setName('foo ' . uniqid())
			->setIsDefault(true)
			->setStorewideDiscountAmount(45)
			->setStorewideDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PRICE)
			->setCategoryAccessType(Store_Customer_Group::CATEGORY_ACCESS_SPECIFIC)
			->setAccessibleCategories(array(1, 3, 5));

		if (!$saveModel->save()) {
			$this->fail('failed to save the customer group');
			return false;
		}

		$this->_createdModels[] = $saveModel;

		return $saveModel;
	}

	protected function _createDiscounts(Store_Customer_Group $group)
	{
		$discount1 = new Store_Customer_Group_Discount();
		$discount1
			->setCustomerGroupId($group->getId())
			->setCategoryOrProductId(1)
			->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_PRODUCT)
			->setDiscountAmount(5)
			->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED);

		if (!$discount1->save()) {
			$this->fail('failed to save the customer group discounts');
			return false;
		}

		$this->_createdModels[] = $discount1;

		$discount2 = new Store_Customer_Group_Discount();
		$discount2
			->setCustomerGroupId($group->getId())
			->setCategoryOrProductId(2)
			->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_PRODUCT)
			->setDiscountAmount(10)
			->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_PERCENT);

		if (!$discount2->save()) {
			$this->fail('failed to save the customer group discounts');
			return false;
		}

		$this->_createdModels[] = $discount2;

		return array($discount1, $discount2);
	}

	protected function _assertModelsAreSame(Store_Customer_Group $saveModel, Store_Customer_Group $loadModel)
	{
		$this->assertEquals($saveModel->getName(), $loadModel->getName(), "loaded name doesn't match saved name");
		$this->assertEquals($saveModel->getIsDefault(), $loadModel->getIsDefault(), "loaded is-default doesn't match saved is-default");
		$this->assertEquals($saveModel->getStorewideDiscountAmount(), $loadModel->getStorewideDiscountAmount(), "loaded discount amount doesn't match saved discount amount");
		$this->assertEquals($saveModel->getStorewideDiscountMethod(), $loadModel->getStorewideDiscountMethod(), "loaded discount method doesn't match saved discount method");
		$this->assertEquals($saveModel->getCategoryAccessType(), $loadModel->getCategoryAccessType(), "loaded access type doesn't match saved access ty[e");
		$this->assertEquals($saveModel->getAccessibleCategories(), $loadModel->getAccessibleCategories(), "loaded name doesn't match saved name");
	}

	public function tearDown()
	{
		foreach ($this->_createdModels as /** @var DataModel_Record $model */$model) {
			$model->delete();
		}

		parent::tearDown();
	}

	public function testSaveLoadCustomerGroupFromDb()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		$loadModel = new Store_Customer_Group();
		if (!$loadModel->load($saveModel->getId())) {
			$this->fail('failed to the load customer group from db');
			return;
		}

		$this->_assertModelsAreSame($saveModel, $loadModel);
	}

	public function testSaveLoadCustomerGroupFromDatastore()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		$returnData = array(
			$saveModel->getId() => array(
				'customergroupid'       => $saveModel->getId(),
				'groupname'             => $saveModel->getName(),
				'discount'              => $saveModel->getStorewideDiscountAmount(),
				'discountmethod'        => $saveModel->getStorewideDiscountMethod(),
				'isdefault'             => $saveModel->getIsDefault(),
				'categoryaccesstype'    => $saveModel->getCategoryAccessType(),
				'accesscategories'      => $saveModel->getAccessibleCategories(),
			),
		);

		$datastore = $this->getMock('ISC_DATA_STORE', array('Read'));
		$datastore
			->expects($this->once())
			->method('Read')
			->with('CustomerGroups')
			->will($this->returnValue($returnData));

		$loadModel = Store_Customer_Group::loadFromDatastore($saveModel->getId(), $datastore);
		if ($loadModel === false) {
			$this->fail('failed to the load customer group from datastore');
			return;
		}

		$this->_assertModelsAreSame($saveModel, $loadModel);
	}

	public function testDeleteCustomerGroup()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		$saveModel->delete();

		$loadModel = new Store_Customer_Group();
		$this->assertFalse($loadModel->load($saveModel->getId()));
	}

	public function testDeleteCustomerGroupDeletesAccessCategories()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		$saveModel->delete();

		// check that the categories have been deleted
		$categoryCount = Store::getStoreDb()->FetchOne('SELECT COUNT(*) FROM [|PREFIX|]customer_group_categories WHERE customergroupid = ' . $saveModel->getId());
		$this->assertEquals(0, $categoryCount, 'access categories were not deleted after deleting the customer group');
	}

	public function testDeleteCustomerGroupDeletesDiscounts()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		// create a discount
		$discount = new Store_Customer_Group_Discount();
		$discount
			->setCustomerGroupId($saveModel->getId())
			->setCategoryOrProductId(1)
			->setDiscountType(Store_Customer_Group_Discount::DISCOUNT_TYPE_CATEGORY)
			->setAppliesTo(Store_Customer_Group_Discount::APPLIES_TO_CATEGORY)
			->setDiscountAmount(5)
			->setDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED);

		if (!$discount->save()) {
			$this->fail('failed to save customer group discount');
			return;
		}

		$this->_createdModels[] = $discount;

		$saveModel->delete();

		$discountCount = Store_Customer_Group_Discount::find('customergroupid = ' . $saveModel->getId())->count();

		$this->assertEquals(0, $discountCount, 'discounts were not deleted after deleting the customer group');
	}

	public function testGetProductDiscounts()
	{
		$saveModel = $this->_createGroup();
		if ($saveModel === false) {
			return;
		}

		if (!$this->_createDiscounts($saveModel)) {
			return;
		}

		$discounts = $saveModel->getProductDiscounts();
		$this->assertArrayIsNotEmpty($discounts, 'no product discounts found for the customer group');

		$expected = array(
			array(
				'productId' => 1,
				'discountAmount' => 5,
				'discountMethod' => Store_Customer_Group::DISCOUNT_METHOD_FIXED,
			),
			array(
				'productId' => 2,
				'discountAmount' => 10,
				'discountMethod' => Store_Customer_Group::DISCOUNT_METHOD_PERCENT,
			),
		);

		$this->assertEquals($expected, $discounts, "product discounts don't match expected");
	}

	public function testSavingGroupDisablesIsDefaultForOtherGroups()
	{
		$group1 = $this->_createGroup();
		if ($group1 === false) {
			return;
		}

		$group2 = $this->_createGroup();
		if ($group2 === false) {
			return;
		}

		// reload the first group and ensure the is-default flag is disabled
		$group1->load($group1->getId());
		$this->assertFalse($group1->getIsDefault(), "first customer group is still set as default");
	}

	public function testGetDefaultCustomerGroup()
	{
		$group = $this->_createGroup();
		if ($group === false) {
			return;
		}
		$group->setIsDefault(false)->save();

		$defaultGroup = $this->_createGroup();
		if ($defaultGroup === false) {
			return;
		}

		$this->_assertModelsAreSame($defaultGroup, Store_Customer_Group::findDefaultCustomerGroup(), 'default customer group is not as expected');
	}

	public function testGetDefaultCustomerGroupWithNoDefaultReturnsFalse()
	{
		$group = $this->_createGroup();
		if ($group === false) {
			return;
		}
		$group->setIsDefault(false)->save();

		$this->assertFalse(Store_Customer_Group::findDefaultCustomerGroup(), 'default customer group should be false');
	}

	public function testGetDiscountsOnClonedGroup()
	{
		$group = $this->_createGroup();
		if ($group === false) {
			return;
		}

		if (!$this->_createDiscounts($group)) {
			return;
		}

		$clonedGroup = clone $group;
		$this->assertEquals(2, $clonedGroup->getDiscounts()->count(), "number of discounts on cloned customer group doesn't match");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
