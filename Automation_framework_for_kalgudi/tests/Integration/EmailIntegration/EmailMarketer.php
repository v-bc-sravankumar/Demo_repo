<?php

require_once dirname(__FILE__) . '/ModuleBase.php';

class Unit_EmailIntegration_EmailMarketer extends Unit_EmailIntegration_ModuleBase
{
	const TEST_API_URL = 'http://beast/~gwilym.evans/bamboo/iem/xml.php';
	const TEST_API_USERNAME = 'admin';
	const TEST_API_USERTOKEN = 'f13c2ebede977e010e9129c90c7dbc2d92e716d0';
	const TEST_LIST_ID = '1';

	public $api;

	public function setUp ()
	{
		parent::setUp();
		$this->api = new Interspire_EmailIntegration_EmailMarketer(self::TEST_API_URL, self::TEST_API_USERNAME, self::TEST_API_USERTOKEN);
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function tearDown ()
	{
		parent::tearDown();
		unset($this->api);
	}

	public function getModuleId ()
	{
		return 'emailmarketer';
	}

	public function getTestListId ()
	{
		return self::TEST_LIST_ID;
	}

	public function getBulkTestListId ()
	{
		return self::TEST_LIST_ID;
	}

	public function sortSubscriptionsByEmail ($a, $b)
	{
		return strcmp($a['email'], $b['email']);
	}

	public function getAllListMembers ($listId)
	{
		$members = array();

		$subscribers = $this->api->getSubscribers($listId);
		if (!$subscribers || !$subscribers->isSuccess()) {
			return false;
		}

		foreach ($subscribers->getData()->subscriberlist->children() as $subscriber) {
			$members[] = array(
				'email' => (string)$subscriber->emailaddress,
			);
		}

		usort($members, array($this, 'sortSubscriptionsByEmail'));
		return $members;
	}

	public function deleteTestSubscriptions ($listId = self::TEST_LIST_ID)
	{
		$members = $this->getAllListMembers($listId);
		$this->assertInternalType('array', $members, "Failed to get existing members for list $listId for deletion");

		$batchSize = 1000;

		while (count($members))
		{
			$member = array_pop($members);
			$result = $this->api->deleteSubscriber($listId, $member['email']);
			$this->assertInstanceOf('Interspire_EmailIntegration_EmailMarketer_XmlApiResponse', $result, "Result of deleteSubscriber was not an instance of XmlApiResponse");
			$this->assertTrue($result->isSuccess(), "deleteSubscriber was not successful: " . $result->getErrorMessage());
		}

		$members = $this->getAllListMembers($listId);
		$this->assertInternalType('array', $members, "Result of getAllListMembers is not an array");
		$this->assertTrue(empty($members), "Members list for list $listId after deletion is not empty");
	}

	public function getConfiguredModule ()
	{
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_emailmarketer' AND variablename IN ('isconfigured', 'apikey', 'url', 'username', 'usertoken')");

		$methods = explode(',', GetConfig('EmailIntegrationMethods'));
		if (!in_array('emailintegration_' . $this->getModuleId(), $methods)) {
			$methods[] = 'emailintegration_' . $this->getModuleId();
			Store_Config::override('EmailIntegrationMethods', implode(',', $methods));
		}

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_emailmarketer',
			'variablename' => 'url',
			'variableval' => self::TEST_API_URL,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_emailmarketer',
			'variablename' => 'username',
			'variableval' => self::TEST_API_USERNAME,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_emailmarketer',
			'variablename' => 'usertoken',
			'variableval' => self::TEST_API_USERTOKEN,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_emailmarketer',
			'variablename' => 'isconfigured',
			'variableval' => 1,
		));

		$GLOBALS['ISC_CLASS_DATA_STORE']->UpdateEmailIntegrationModuleVars();
		GetModuleById('emailintegration', $module, $this->getModuleId());

		$this->assertTrue($module->IsEnabled(), "Module is not enabled");
		$this->assertTrue($module->isConfigured(), "Module is not configured");

		return $module;
	}

	public function getTestMergeVars ()
	{
		return array(
			'1'		=> 'Mr',		// title (dropdown)
			'2'		=> 'First',		// first name (text - required)
			'3'		=> 'Last',		// last name (text)
			'7'		=> '29/2/2008',		// birth date (date)
			'11'	=> 'Australia',	// country (dropdown)
			'12'	=> "Test\nTest\nTest",	// multi-line text
			'13'	=> 1,	// number
			'14'	=> true,	// checkbox
			'15'	=> 'Beta',	// radio
		);
	}

	public function subscribe ()
	{
		return $this->api->addSubscriberToList(self::TEST_EMAIL, self::TEST_LIST_ID, 'html', true, $this->getTestMergeVars());
	}

	public function unsubscribe ()
	{
		return $this->api->deleteSubscriber(self::TEST_LIST_ID, self::TEST_EMAIL);
	}

	/**
	* @group remote
	*/
	public function testRemoteVerifyApi ()
	{
		$this->_testRemoteVerifyApi(array(
			'url' => self::TEST_API_URL,
			'username' => self::TEST_API_USERNAME,
			'usertoken' => self::TEST_API_USERTOKEN,
		));
	}

	public function testGetEmailProviderFieldIdReturnsString ()
	{
		$this->markTestSkipped();
	}

	public function testGetIpProviderFieldIdReturnsString ()
	{
		$this->markTestSkipped();
	}

	public function testUpdateSubscriptionIP ()
	{
		// IEM can update IP but doesn't seem to have anything in the XML for getting a subcriber's IP, so we can't verify that an update actually happened properly -- skip test
		$this->markTestSkipped();
	}

	public function getNewsletterSubscriptionMapping ()
	{
		return array(
			'2' => 'subfirstname',
		);
	}

	public function getOrderSubscriptionMapping ()
	{
		return array(
			'12' => 'shipping_method',
			'13' => 'total_inc_tax',
			'7' => 'orddate',
			'14' => 'OrderSubscription_BillingAddress',
		);
	}
}
