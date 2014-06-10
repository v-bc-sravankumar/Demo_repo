<?php
require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

class Unit_Lib_Store_Customer_Address extends ModelLike_TestCase
{
	protected $_createdModels = array();

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Customer_Address();
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
		return 'shipfirstname';
	}

	protected function _createAddress()
	{
		$countryId = GetCountryIdByName('Australia');
		$stateId = GetStateByName('New South Wales', $countryId);

		$address = new Store_Customer_Address();
		$address
			->setFirstName('John')
			->setLastName('Smith')
			->setCompany('ACME')
			->setPhoneNumber('123456789')
			->setAddressLine1('Level 38')
			->setAddressLine2('43 Long Road')
			->setCity('Sydney')
			->setStateId($stateId)
			->setCountryId($countryId)
			->setZip('2000')
			->setFormSessionId(3)
			->setDateLastUsed(2222);

		if (!$address->save()) {
			$this->fail('failed to save the customer address');
			return false;
		}

		$this->_createdModels[] = $address;

		return $address;
	}

	protected function _assertModelsAreSame(Store_Customer_Address $address1, Store_Customer_Address $address2)
	{
		$this->assertEquals($address1->getFirstName(), $address2->getFirstName(), 'mismatch in field: first name');
		$this->assertEquals($address1->getLastName(), $address2->getLastName(), 'mismatch in field: last name');
		$this->assertEquals($address1->getCompany(), $address2->getCompany(), 'mismatch in field: company');
		$this->assertEquals($address1->getPhoneNumber(), $address2->getPhoneNumber(), 'mismatch in field: phone number');
		$this->assertEquals($address1->getAddressLine1(), $address2->getAddressLine1(), 'mismatch in field: address line 1');
		$this->assertEquals($address1->getAddressLine2(), $address2->getAddressLine2(), 'mismatch in field: address line 2');
		$this->assertEquals($address1->getCity(), $address2->getCity(), 'mismatch in field: city');
		$this->assertEquals($address1->getStateId(), $address2->getStateId(), 'mismatch in field: state id');
		$this->assertEquals($address1->getStateName(), $address2->getStateName(), 'mismatch in field: state name');
		$this->assertEquals($address1->getCountryId(), $address2->getCountryId(), 'mismatch in field: country id');
		$this->assertEquals($address1->getCountryName(), $address2->getCountryName(), 'mismatch in field: country name');
		$this->assertEquals($address1->getZip(), $address2->getZip(), 'mismatch in field: zip');
		$this->assertEquals($address1->getFormSessionId(), $address2->getFormSessionId(), 'mismatch in field: form session id');
		$this->assertEquals($address1->getDateLastUsed(), $address2->getDateLastUsed(), 'mismatch in field: date last used');
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
		$saveModel = $this->_createAddress();
		if ($saveModel === false) {
			return;
		}

		$loadModel = new Store_Customer_Address();
		if (!$loadModel->load($saveModel->getId())) {
			$this->fail('failed to the load customer address from db');
			return;
		}

		$this->_assertModelsAreSame($saveModel, $loadModel);
	}

	public function testSetCountryIdSetsCountryName()
	{
		$countryId = GetCountryIdByName('Australia');

		$address = new Store_Customer_Address();
		$address->setCountryId($countryId);

		$this->assertEquals('Australia', $address->getCountryName(), "country name didn't match expected");
	}

	public function testSetStateIdSetsStateName()
	{
		$countryId = GetCountryIdByName('Australia');
		$stateId = GetStateByName('New South Wales', $countryId);

		$address = new Store_Customer_Address();
		$address->setStateId($stateId);

		$this->assertEquals('New South Wales', $address->getStateName(), "state name didn't match expected");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
