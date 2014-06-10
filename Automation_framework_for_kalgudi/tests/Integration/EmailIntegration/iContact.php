<?php

require_once dirname(__FILE__) . '/ModuleBase.php';

class Unit_EmailIntegration_iContact extends Unit_EmailIntegration_ModuleBase
{
	// need icontact specifics here
	const API_APPID = 'pk2dF6xNSGPMLXsW5uxpCZdkKpeS4uBj';
	const API_USERNAME = 'chris.iona-beta';
	const API_PASSWORD = 'qwertyqwerty';
	const API_ACCOUNTID = '411411';
	const API_CLIENTFOLDERID = '122642';
	const TEST_LIST_ID = '189227';
	const BULK_TEST_LIST_ID = false;

	public $api;

	public function getNewsletterSubscriptionMapping ()
	{
		return array(
			'firstName' => 'subfirstname',
		);
	}

	public function getOrderSubscriptionMapping ()
	{
		return array(
			'firstName' => 'ordbillfirstname',
			'lastName' => 'ordbilllastname',
		);
	}

	public function setUp ()
	{
		parent::setUp();
		$this->api = new Interspire_EmailIntegration_iContact(
			self::API_APPID,
			self::API_USERNAME,
			self::API_PASSWORD,
			self::API_ACCOUNTID,
			self::API_CLIENTFOLDERID
		);
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function tearDown ()
	{
		unset($this->api);
		parent::tearDown();
	}

	public function getModuleId ()
	{
		return 'icontact';
	}

	public function getTestListId ()
	{
		return self::TEST_LIST_ID;
	}

	public function getBulkTestListId ()
	{
		return self::BULK_TEST_LIST_ID;
	}

	public function sortSubscriptionsByEmail ($a, $b)
	{
		return strcmp($a['email'], $b['email']);
	}

	public function getAllListMembers ($listId)
	{
		$members = array();

		return $members;
	}

	public function deleteTestSubscriptions ($listId = self::TEST_LIST_ID)
	{
		$response = $this->api->call('contacts');

		foreach ($response->data['contacts'] as $contact) {
			$this->api->call('contacts/' . $contact['contactId'], 'DELETE');
		}

		$response = $this->api->call('contacts');
		$this->assertTrue(empty($response->data['contacts']), "icontact contact list is not empty");
	}

	public function getConfiguredModule ()
	{
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_" . $this->getModuleId() . "' AND variablename = 'apiappid'");
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_" . $this->getModuleId() . "' AND variablename = 'apiusername'");
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_" . $this->getModuleId() . "' AND variablename = 'apipassword'");
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_" . $this->getModuleId() . "' AND variablename = 'apiclientfolderid'");
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_" . $this->getModuleId() . "' AND variablename = 'isconfigured'");

		$methods = explode(',', GetConfig('EmailIntegrationMethods'));
		if (!in_array('emailintegration_' . $this->getModuleId(), $methods)) {
			$methods[] = 'emailintegration_' . $this->getModuleId();
			Store_Config::override('EmailIntegrationMethods', implode(',', $methods));
		}

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
			'variablename' => 'apiappid',
			'variableval' => self::API_APPID,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
			'variablename' => 'apiusername',
			'variableval' => self::API_USERNAME,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
			'variablename' => 'apipassword',
			'variableval' => self::API_PASSWORD,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
			'variablename' => 'apiaccountid',
			'variableval' => self::API_ACCOUNTID,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
			'variablename' => 'apiclientfolderid',
			'variableval' => self::API_CLIENTFOLDERID,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_' . $this->getModuleId(),
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
		$merge_vars = array(
			'EMAIL' => Unit_EmailIntegration_ModuleBase::TEST_EMAIL,
			'TEXT' => 'Text',
			'NUMBER' => 1,
			'MULTIPLE' => 'First Choice',
			'DROPDOWN' => 'First Choice',
			'DATEFIELD' => '2010-12-31',
			'ADDRESS' => '',
			'PHONE' => '0298765432',
			'WEBSITE' => 'http://www.google.com/',
			'IMAGE' => 'http://www.google.com/intl/en_ALL/images/logo.gif',
		);

		return $merge_vars;
	}

	public function subscribe ()
	{
		$contact = $this->getTestMergeVars();
		$contact['email'] = self::TEST_EMAIL;

		$response = $this->api->call('contacts?email=' . urlencode(self::TEST_EMAIL), 'POST', array(
			$contact,
		));

		$contact = null;
		foreach ($response->data['contacts'] as $contact) {
			break;
		}

		$this->assertNotNull($contact, "contact not found or not added");

		$this->api->call('subscriptions', 'POST', array(
			array(
				'listId' => self::TEST_LIST_ID,
				'contactId' => $contact['contactId'],
			),
		));

		return true;
	}

	public function unsubscribe ()
	{
		// this is the only way to unsubscribe a test subscriber with icontact while being able to resubscribe them later
		$response = $this->api->call('contacts?email=' . urlencode(self::TEST_EMAIL));
		foreach ($response->data['contacts'] as $contact) {
			$this->api->call('contacts/' . rawurlencode($contact['contactId']), 'DELETE');
		}

		return true;
	}

	/**
	* @group remote
	*/
	public function testLists ()
	{
		$result = $this->api->call('lists');
		$lists = $result->data['lists'];

		// test for any lists returned
		$this->assertInternalType('array', $lists);
		$this->assertGreaterThan(0, count($lists));

		// look for the test list which should be at icontact
		$found = false;
		foreach ($lists as $list)
		{
			if ($list['listId'] == self::TEST_LIST_ID)
			{
				$found = true;
				break;
			}
		}

		$this->assertTrue($found);
	}

	/**
	* @group remote
	*/
	public function testListSubscribe ()
	{
		$this->assertTrue($this->subscribe(), "subscribe failed");
		$this->assertTrue($this->unsubscribe(), "unsubscribe failed");
	}

	/**
	* @group remote
	*/
	public function testRemoteVerifyApi ()
	{
		$this->_testRemoteVerifyApi(array(
			'appid' => self::API_APPID,
			'username' => self::API_USERNAME,
			'password' => self::API_PASSWORD,
		));
	}

	public function testGetIpProviderFieldIdReturnsString ()
	{
		$this->markTestSkipped();
	}

	public function testUpdateSubscriptionIp ()
	{
		$this->markTestSkipped();
	}
}
