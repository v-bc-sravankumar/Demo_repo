<?php

require_once dirname(__FILE__) . '/ModuleBase.php';

class Unit_EmailIntegration_MailChimp extends Unit_EmailIntegration_ModuleBase
{
	const TEST_API_KEY = 'a696725c32b47e225d3d30f72bce306f-us1';
	const TEST_LIST_ID = '7468fe54ea';
	const BULK_TEST_LIST_ID = 'c3592fd3bb';

	public $api;

	public function setUp ()
	{
		parent::setUp();
		$this->api = new Interspire_EmailIntegration_MailChimp(self::TEST_API_KEY);
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function tearDown ()
	{
		parent::tearDown();
		unset($this->api);
	}

	public function getModuleId ()
	{
		return 'mailchimp';
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

		$offset = 0;
		$limit = 15000;

		while (true) {
			$result = $this->api->listMembers($listId, 'subscribed', null, $offset, $limit);
			if (!is_array($result)) {
				return $result;
			}

			if (empty($result)) {
				break;
			}

			$members += $result;
			$offset += $limit;
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
			$batch = array();
			while (count($members) && count($batch) < $batchSize)
			{
				$member = array_pop($members);
				$batch[] = $member['email'];
			}
			$result = $this->api->listBatchUnsubscribe($listId, $batch, true, false, false);
			$this->assertInternalType('array', $result, "Result of listBatchUnsubscribe was not an array: " . $this->api->errorMessage);
			$this->assertEquals(0, $result['error_count'], "listBatchUnsubscribe reported errors:\n\n" . print_r($result, true));
		}

		$members = $this->api->listMembers($listId, 'subscribed', null, 0, 1);
		$this->assertInternalType('array', $members, "Result of listMembers is not an array: " . $this->api->errorMessage);
		$this->assertTrue(empty($members), "Members list for list $listId after deletion is not empty");
	}

	public function getConfiguredModule ()
	{
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_mailchimp' AND variablename = 'apikey'");
		$this->fixtures->DeleteQuery('module_vars', "WHERE modulename = 'emailintegration_mailchimp' AND variablename = 'isconfigured'");

		$methods = explode(',', GetConfig('EmailIntegrationMethods'));
		if (!in_array('emailintegration_' . $this->getModuleId(), $methods)) {
			$methods[] = 'emailintegration_' . $this->getModuleId();
			Store_Config::override('EmailIntegrationMethods', implode(',', $methods));
		}

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_mailchimp',
			'variablename' => 'apikey',
			'variableval' => self::TEST_API_KEY,
		));

		$this->fixtures->InsertQuery('module_vars', array(
			'modulename' => 'emailintegration_mailchimp',
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
		return $this->api->listSubscribe(self::TEST_LIST_ID, Unit_EmailIntegration_ModuleBase::TEST_EMAIL, $this->getTestMergeVars(), 'html', false, true, false, false);
	}

	public function unsubscribe ()
	{
		return $this->api->listUnsubscribe(self::TEST_LIST_ID, Unit_EmailIntegration_ModuleBase::TEST_EMAIL, true, false, false);
	}

	/**
	* @group remote
	*/
	public function testLists ()
	{
		$result = $this->api->lists();

		// test for any lists returned
		$this->assertInternalType('array', $result);
		$this->assertGreaterThan(0, count($result));

		// look for the test list which should be at mailchimp
		$found = false;
		foreach ($result as $list)
		{
			if ($list['id'] == self::TEST_LIST_ID)
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
	public function testListMemberInfo ()
	{
		$this->subscribe();
		$result = $this->api->listMemberInfo(self::TEST_LIST_ID, Unit_EmailIntegration_ModuleBase::TEST_EMAIL);
		$this->assertInternalType('array', $result);
		$this->assertEquals($result['email'], Unit_EmailIntegration_ModuleBase::TEST_EMAIL);

		$expected = $this->getTestMergeVars();
		foreach ($result['merges'] as $tag => $value)
		{
			if (isset($expected[$tag]))
			{
				// all fields will come back as their MERGE# equivalents too so can't check for exact array equality
				$this->assertEquals($expected[$tag], $value);
			}
		}

		$this->unsubscribe();
	}

	/**
	* @group remote
	*/
	public function testListMergeVars ()
	{
		$result = $this->api->listMergeVars(self::TEST_LIST_ID);
		$this->assertInternalType('array', $result);

		// find all of these fields in the results
		$lookFor = array(
			'EMAIL' => 'email',
			'TEXT' => 'text',
			'NUMBER' => 'number',
			'MULTIPLE' => 'radio',
			'DROPDOWN' => 'dropdown',
			'DATEFIELD' => 'date',
			'ADDRESS' => 'address',
			'PHONE' => 'phone',
			'WEBSITE' => 'url',
			'IMAGE' => 'imageurl',
		);

		foreach ($result as $field)
		{
			$tag = $field['tag'];
			$this->assertTrue(isset($lookFor[$tag]), "Found extra field in mailchimp test list: " . $tag);
			$this->assertEquals($lookFor[$tag], $field['field_type'], "Field " . $tag . " on mailchimp expected to be of type " . $lookFor[$tag] . " but is " . $field['field_type'] . " instead");
			unset($lookFor[$tag]);
		}

		// there should be no elements left if all are found
		$this->assertEquals(0, count($lookFor), "The following fields were not found in the test list at mailchimp: " . implode(", ", array_keys($lookFor)));
	}

	/**
	* @group remote
	*/
	public function testListSubscribe ()
	{
		$this->assertTrue($this->subscribe());
		$this->unsubscribe();
	}

	/**
	* @group remote
	*/
	public function testListUnsubscribe ()
	{
		$this->subscribe();
		$result = $this->unsubscribe();
		$this->assertTrue($result);
		$result = $this->api->listMemberInfo(self::TEST_LIST_ID, Unit_EmailIntegration_ModuleBase::TEST_EMAIL);
		$this->assertFalse($result);
	}

	/**
	* @group remote
	*/
	public function testPing ()
	{
		$result = $this->api->ping();
		$this->assertEquals(Interspire_EmailIntegration_MailChimp::PING_OK, $result);
	}

	/**
	* @group remote
	*/
	public function testRemoteVerifyApi ()
	{
		$this->_testRemoteVerifyApi(array(
			'key' => self::TEST_API_KEY
		));
	}

	public function getNewsletterSubscriptionMapping ()
	{
		return array(
			'TEXT' => 'subfirstname',
		);
	}

	public function getOrderSubscriptionMapping ()
	{
		return array(
			'TEXT' => 'shipping_method',
			'NUMBER' => 'total_inc_tax',
			'DATEFIELD' => 'orddate',
			'ADDRESS' => 'OrderSubscription_BillingAddress',
		);
	}
}
