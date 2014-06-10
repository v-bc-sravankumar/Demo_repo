<?php

require_once dirname(__FILE__) . '/ModelLike_TestCase.php';

class Unit_Lib_Store_Customer extends ModelLike_TestCase
{
	protected $_createdModels = array();

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Customer();
		$model->setFirstName($this->_getCrudSmokeValue1());

		return $model;
	}

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getFirstName';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setFirstName';
	}

	protected function _getFindSmokeColumn ()
	{
		return 'custconfirstname';
	}

	protected function _createCustomer()
	{
		$customer = new Store_Customer();
		$customer
			->setFirstName('John')
			->setLastName('Smith')
			->setCustomerGroupId(3)
			->setCompany('ACME')
			->setEmailAddress('john@acme.com')
			->setPhoneNumber('123456789')
			->setToken(GenerateCustomerToken())
			->setPasswordResetToken('foo')
			->setPasswordResetEmailAddress('john@john.com')
			->setNotes('some notes')
			->setStoreCredit(45.92)
			->setFormSessionId(8)
			->setSalt('abcd')
			->setPasswordHash('efgh')
			->setDateCreated('1111')
			->setDateModified('6666');

		if (!$customer->save()) {
			$this->fail('failed to save the customer');
			return false;
		}

		$this->_createdModels[] = $customer;

		return $customer;
	}

	protected function _assertModelsAreSame(Store_Customer $customer1, Store_Customer $customer2)
	{
		$this->assertEquals($customer1->getFirstName(), $customer2->getFirstName(), 'mismatch in field: first name');
		$this->assertEquals($customer1->getLastName(), $customer2->getLastName(), 'mismatch in field: last name');
		$this->assertEquals($customer1->getCustomerGroupId(), $customer2->getCustomerGroupId(), 'mismatch in field: customer group id');
		$this->assertEquals($customer1->getCompany(), $customer2->getCompany(), 'mismatch in field: company');
		$this->assertEquals($customer1->getEmailAddress(), $customer2->getEmailAddress(), 'mismatch in field: email address');
		$this->assertEquals($customer1->getPhoneNumber(), $customer2->getPhoneNumber(), 'mismatch in field: phone number');
		$this->assertEquals($customer1->getToken(), $customer2->getToken(), 'mismatch in field: token');
		$this->assertEquals($customer1->getPasswordResetToken(), $customer2->getPasswordResetToken(), 'mismatch in field: password reset token');
		$this->assertEquals($customer1->getPasswordResetEmailAddress(), $customer2->getPasswordResetEmailAddress(), 'mismatch in field: password reset email');
		$this->assertEquals($customer1->getNotes(), $customer2->getNotes(), 'mismatch in field: notes');
		$this->assertEquals($customer1->getStoreCredit(), $customer2->getStoreCredit(), 'mismatch in field: store credit');
		$this->assertEquals($customer1->getFormSessionId(), $customer2->getFormSessionId(), 'mismatch in field: form session id');
		$this->assertEquals($customer1->getSalt(), $customer2->getSalt(), 'mismatch in field: salt');
		$this->assertEquals($customer1->getPasswordHash(), $customer2->getPasswordHash(), 'mismatch in field: password hash');
		$this->assertEquals($customer1->getDateCreated(), $customer2->getDateCreated(), 'mismatch in field: date created');
		$this->assertEquals($customer1->getDateModified(), $customer2->getDateModified(), 'mismatch in field: date modified');
	}

	public function tearDown()
	{
		foreach ($this->_createdModels as /** @var DataModel_Record $model */$model) {
			$model->delete();
		}

		parent::tearDown();
	}

	public function testSaveLoadCustomerFromDb()
	{
		$saveModel = $this->_createCustomer();
		if ($saveModel === false) {
			return;
		}

		$loadModel = new Store_Customer();
		if (!$loadModel->load($saveModel->getId())) {
			$this->fail('failed to the load customer from db');
			return;
		}

		$this->_assertModelsAreSame($saveModel, $loadModel);
	}

	public function testSetPasswordGeneratesHash()
	{
		$customer = new Store_Customer();
		$customer->setPassword('foo');
		$this->assertNotEmpty($customer->getPasswordHash(), "password hash is empty");
	}

	public function testValidatePassword()
	{
		$customer = new Store_Customer();
		$customer->setPassword('foo');
		$this->assertTrue($customer->validatePassword('foo'), "password did not validate");
	}

	public function testFindCustomersInCustomerGroup()
	{
		$this->_createCustomer();
		$customer = $this->_createCustomer();

		$customersInGroup = Store_Customer::findCustomersInCustomerGroup($customer->getCustomerGroupId());
		$this->assertEquals(2, $customersInGroup->count(), "incorrect number of customers in group");
	}

	public function testFindCustomerFromToken()
	{
		$this->_createCustomer();
		$customer = $this->_createCustomer();
		$customer->setToken('bar')->save();

		$findCustomer = Store_Customer::findFromCustomerToken('bar');
		$this->assertInstanceOf('Store_Customer', $findCustomer, "customer is not instance of Store_Customer");
		$this->assertEquals($customer->getId(), $findCustomer->getId(), "wrong customer was found");
	}

	public function testDeleteCustomerDeletesRelatedData()
	{
		$customer = $this->_createCustomer();

		// create an address
		$address = new Store_Customer_Address();
		$address->setCustomerId($customer->getId());
		if (!$address->save()) {
			$this->fail('failed to save customer address');
			return;
		}

		$this->_createdModels[] = $address;

		// create a notification preference
		$preference = new Store_Notification_Preference();
		$preference
			->setTypeId(1)
			->setCustomerId($customer->getId());

		if (!$preference->save()) {
			$this->fail('failed to save notification preference');
			return;
		}

		$this->_createdModels[] = $preference;

		$customer->delete();

		$this->assertEquals(0, Store_Customer_Address::findByCustomerId($customer->getId())->count(), "customer addresses were not deleted");
		$this->assertEquals(0, Store_Notification_Preference::findByCustomerId($customer->getId())->count(), "notification preferences were not deleted");
	}

	public function testDeleteCustomerWithCachedDataDeletesRelatedData()
	{
		$customer = $this->_createCustomer();

		// create an address
		$address = new Store_Customer_Address();
		$address->setCustomerId($customer->getId());
		if (!$address->save()) {
			$this->fail('failed to save customer address');
			return;
		}

		$this->_createdModels[] = $address;

		// create a notification preference
		$preference = new Store_Notification_Preference();
		$preference
			->setTypeId(1)
			->setCustomerId($customer->getId());

		if (!$preference->save()) {
			$this->fail('failed to save notification preference');
			return;
		}

		$this->_createdModels[] = $preference;

		// load up addresses and notifications to get them cached in the object.
		$customer->getAddresses();
		$customer->getNotificationPreferences();

		// create another address
		$address = new Store_Customer_Address();
		$address->setCustomerId($customer->getId());
		if (!$address->save()) {
			$this->fail('failed to save customer address');
			return;
		}

		$this->_createdModels[] = $address;

		// create another notification preference
		$preference = new Store_Notification_Preference();
		$preference
			->setTypeId(1)
			->setCustomerId($customer->getId());

		if (!$preference->save()) {
			$this->fail('failed to save notification preference');
			return;
		}

		$this->_createdModels[] = $preference;

		$customer->delete();

		$this->assertEquals(0, Store_Customer_Address::findByCustomerId($customer->getId())->count(), "customer addresses were not deleted");
		$this->assertEquals(0, Store_Notification_Preference::findByCustomerId($customer->getId())->count(), "notification preferences were not deleted");
	}

	public function testGetCustomerGroupWithValidGroupReturnsCustomerGroup()
	{
		$group = new Store_Customer_Group();
		$group->setName('foo');
		if (!$group->save()) {
			$this->fail('failed to save customer group');
			return;
		}

		$this->_createdModels[] = $group;

		$customer = new Store_Customer();
		$customer->setCustomerGroupId($group->getId());
		$this->assertEquals($group->getId(), $customer->getCustomerGroup()->getId(), "expected customer group id didn't match");
	}

	public function testGetCustomerGroupWithNoGroupReturnsFalse()
	{
		$customer = new Store_Customer();
		$this->assertFalse($customer->getCustomerGroup());
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
