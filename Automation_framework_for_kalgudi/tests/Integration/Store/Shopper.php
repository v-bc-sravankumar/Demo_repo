<?php

class Unit_Lib_Store_Shopper extends Interspire_UnitTest
{
	public function testGetCustomerTokenFromCookie()
	{
		$_COOKIE['SHOP_TOKEN'] = 'foo';

		$shopper = new Store_Shopper();
		$this->assertSame('foo', $shopper->getCustomerToken(), "customer token didn't match expected");
	}

	public function testGetCustomerTokenFromSession()
	{
		unset($_COOKIE['SHOP_TOKEN']);
		$_SESSION['SHOP_TOKEN'] = 'bar';

		$shopper = new Store_Shopper();
		$this->assertSame('bar', $shopper->getCustomerToken(), "customer token didn't match expected");
		$this->assertSame('bar', $_COOKIE['SHOP_TOKEN'], "cookie customer token didn't match expected");
	}

	public function testGetCustomerTokenForNoCustomerIsEmpty()
	{
		unset($_COOKIE['SHOP_TOKEN']);
		unset($_SESSION['SHOP_TOKEN']);

		$shopper = new Store_Shopper();
		$this->assertSame('', $shopper->getCustomerToken());
	}

	public function testIsGuestIsFalseForCustomer()
	{
		$_COOKIE['SHOP_TOKEN'] = 'foo';

		$shopper = new Store_Shopper();
		$this->assertFalse($shopper->isGuest());
	}

	public function testIsGuestIsTrueForGuest()
	{
		unset($_COOKIE['SHOP_TOKEN']);
		unset($_SESSION['SHOP_TOKEN']);

		$shopper = new Store_Shopper();
		$this->assertTrue($shopper->isGuest());
	}

	public function testGetCustomerForGuestIsFalse()
	{
		$shopper = $this->getMock('Store_Shopper', array('isGuest'));
		$shopper
			->expects($this->once())
			->method('isGuest')
			->will($this->returnValue(true));

		$this->assertFalse($shopper->getCustomer());
	}

	public function testGetCustomerForCustomerReturnsCustomer()
	{
		$customer = new Store_Customer();
		$customer->setToken('foo');
		if (!$customer->save()) {
			$this->fail('failed to create customer');
			return;
		}

		$_COOKIE['SHOP_TOKEN'] = 'foo';

		$shopper = new Store_Shopper();
		$getCustomer = $shopper->getCustomer();

		$this->assertInstanceOf('Store_Customer', $getCustomer, "getCustomer() didn't return a Store_Customer instance");
		$this->assertSame($customer->getId(), $getCustomer->getId(), "customer id didn't match");

		$customer->delete();
	}

	public function testGetCustomerGroupForCustomerInGroup()
	{
		$group = new Store_Customer_Group();
		$group->setName('foo');

		$customer = $this->getMock('Store_Customer', array('getCustomerGroupId', 'getCustomerGroup'));
		$customer
			->expects($this->once())
			->method('getCustomerGroupId')
			->will($this->returnValue(1));

		$customer
			->expects($this->once())
			->method('getCustomerGroup')
			->with(true)
			->will($this->returnValue($group));

		$shopper = $this->getMock('Store_Shopper', array('getCustomer'));
		$shopper
			->expects($this->once())
			->method('getCustomer')
			->will($this->returnValue($customer));

		$this->assertInstanceOf('Store_Customer_Group', $shopper->getCustomerGroup(), "getCustomerGroup() didn't return a Store_Customer_Group instance");
		$this->assertSame($group->getName(), $shopper->getCustomerGroup()->getName(), "customer group name didn't match");
	}

	public function testGetCustomerGroupForGuestReturnsGuestGroup()
	{
		$this->markTestIncomplete("incomplete due to testing issues with static classes");
	}

	public function testGetCustomerGroupReturnsDefaultGroup()
	{
		$this->markTestIncomplete("incomplete due to testing issues with static classes");
	}

	public function testGetAccessibleCategoriesForNoGroupReturnsTrue()
	{
		$shopper = $this->getMock('Store_Shopper', array('getCustomerGroup'));
		$shopper
			->expects($this->once())
			->method('getCustomerGroup')
			->will($this->returnValue(false));

		$this->assertTrue($shopper->getAccessibleCategories());
	}

	public function testGetAccessibleCategoriesForGroupWithNoAccessReturnsFalse()
	{
		$group = new Store_Customer_Group();
		$group->setCategoryAccessType(Store_Customer_Group::CATEGORY_ACCESS_NONE);

		$shopper = $this->getMock('Store_Shopper', array('getCustomerGroup'));
		$shopper
			->expects($this->once())
			->method('getCustomerGroup')
			->will($this->returnValue($group));

		$this->assertFalse($shopper->getAccessibleCategories());
	}

	public function testGetAccessibleCategoriesForGroupWithSpecificAccessReturnsIds()
	{
		$group = new Store_Customer_Group();
		$group
			->setCategoryAccessType(Store_Customer_Group::CATEGORY_ACCESS_SPECIFIC)
			->setAccessibleCategories(array(1,3,5));

		$shopper = $this->getMock('Store_Shopper', array('getCustomerGroup'));
		$shopper
			->expects($this->once())
			->method('getCustomerGroup')
			->will($this->returnValue($group));

		$this->assertEquals($group->getAccessibleCategories(), $shopper->getAccessibleCategories());
	}

	public function testGetAccessibleCategoriesForGroupWithSpecificAccessAndNoCategoriesReturnsFalse()
	{
		$group = new Store_Customer_Group();
		$group->setCategoryAccessType(Store_Customer_Group::CATEGORY_ACCESS_SPECIFIC);

		$shopper = $this->getMock('Store_Shopper', array('getCustomerGroup'));
		$shopper
			->expects($this->once())
			->method('getCustomerGroup')
			->will($this->returnValue($group));

		$this->assertFalse($shopper->getAccessibleCategories());
	}

	public function testGetTaxZoneIdForSearchingPricesEnteredAndDisplayedWithoutTaxIsFalse()
	{
		$currentTaxDisplay = Store_Config::get('taxDefaultTaxDisplayCatalog');
		$currentTaxEntered = Store_Config::get('taxEnteredWithPrices');

		Store_Config::override('taxDefaultTaxDisplayCatalog', TAX_PRICES_DISPLAY_EXCLUSIVE);
		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_EXCLUSIVE);

		$shopper = new Store_Shopper();
		$this->assertFalse($shopper->getTaxZoneIdForProductSearching());

		Store_Config::override('taxDefaultTaxDisplayCatalog', $currentTaxDisplay);
		Store_Config::override('taxEnteredWithPrices', $currentTaxEntered);
	}

	public function testGetTaxZoneIdForSearchingPricesDisplayedWithoutTaxIsZero()
	{
		$currentTaxDisplay = Store_Config::get('taxDefaultTaxDisplayCatalog');
		$currentTaxEntered = Store_Config::get('taxEnteredWithPrices');

		Store_Config::override('taxDefaultTaxDisplayCatalog', TAX_PRICES_DISPLAY_EXCLUSIVE);
		Store_Config::override('taxEnteredWithPrices', TAX_PRICES_ENTERED_INCLUSIVE);

		$shopper = new Store_Shopper();
		$this->assertSame(0, $shopper->getTaxZoneIdForProductSearching());

		Store_Config::override('taxDefaultTaxDisplayCatalog', $currentTaxDisplay);
		Store_Config::override('taxEnteredWithPrices', $currentTaxEntered);
	}

	public function testGetTaxZoneIdForSearchingPricesDisplayedWithTax()
	{
		$currentTaxDisplay = Store_Config::get('taxDefaultTaxDisplayCatalog');

		Store_Config::override('taxDefaultTaxDisplayCatalog', TAX_PRICES_DISPLAY_INCLUSIVE);

		$shopper = $this->getMock('Store_Shopper', array('getTaxZoneId'));
		$shopper
			->expects($this->once())
			->method('getTaxZoneId')
			->will($this->returnValue(99));

		$this->assertSame(99, $shopper->getTaxZoneIdForProductSearching());

		Store_Config::override('taxDefaultTaxDisplayCatalog', $currentTaxDisplay);
	}

	public function testGetPriceFieldForProductSearchingWithTaxZone()
	{
		$shopper = $this->getMock('Store_Shopper', array('getTaxZoneIdForProductSearching'));
		$shopper
			->expects($this->once())
			->method('getTaxZoneIdForProductSearching')
			->will($this->returnValue(99));

		$this->assertSame('prices.tax_zone_prices.tax_zone_id_99', $shopper->getPriceFieldForProductSearching());
	}

	public function testGetPriceFieldForProductSearchingWithoutTaxZone()
	{
		$shopper = $this->getMock('Store_Shopper', array('getTaxZoneIdForProductSearching'));
		$shopper
			->expects($this->once())
			->method('getTaxZoneIdForProductSearching')
			->will($this->returnValue(false));

		$this->assertSame('prices.calculated', $shopper->getPriceFieldForProductSearching());
	}
}
