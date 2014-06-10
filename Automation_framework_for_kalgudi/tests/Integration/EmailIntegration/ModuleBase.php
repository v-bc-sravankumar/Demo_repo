<?php

abstract class Unit_EmailIntegration_ModuleBase extends Interspire_IntegrationTest
{
	const TEST_EMAIL = 'chaitanya.kuber@bigcommerce.com';

	protected $keystore;

	public function setUp()
	{
		$this->keystore = Interspire_KeyStore::instance();
		while (Interspire_TaskManager_Internal::executeNextTask());
	}

	public function tearDown ()
	{
		while (Interspire_TaskManager_Internal::executeNextTask());
	}

	abstract public function getModuleId ();

	abstract public function getTestListId ();

	abstract public function getBulkTestListId ();

	/** @return ISC_EMAILINTEGRATION */
	abstract public function getConfiguredModule ();

	abstract public function deleteTestSubscriptions ($listId = '');

	public function testGetSettingsTemplateReturnsString ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('string', $module->getSettingsTemplate());
	}

	public function testGetSettingsJavaScriptReturnsString ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('string', $module->getSettingsJavaScript());
	}

	public function testIsSelectableReturnsBool ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('bool', $module->isSelectable());
	}

	public function testSupportsBulkExportReturnsBool ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('bool', $module->supportsBulkExport());
	}

	public function testGetAvailableMailFormatPreferencesReturnsArray ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('array', $module->getAvailableMailFormatPreferences());
	}

	public function testGetDefaultMailFormatPreferenceReturnsInt ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('int', $module->getDefaultMailFormatPreference());
	}

	public function testGetEmailProviderFieldIdReturnsString ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('string', $module->getEmailProviderFieldId());
	}

	public function testGetIpProviderFieldIdReturnsString ()
	{
		$module = $this->getConfiguredModule();
		$this->assertInternalType('string', $module->getIpProviderFieldId());
	}

	/**
	* @group remote
	*/
	public function testUpdateSubscriptionIp ()
	{
		$module = $this->getConfiguredModule();

		// remove existing
		$this->deleteTestSubscriptions();

		// subscribe someone new
		require_once dirname(__FILE__) . '/Subscription/Newsletter.php';
		$subscription = new Unit_EmailIntegration_Subscription_Newsletter();
		/** @var Interspire_EmailIntegration_Subscription_Order */
		$subscription = $subscription->getSubscriptionInstance();
		$subscription->setDoubleOptIn(false);
		$subscription->setSendWelcome(false);

		$result = $module->addSubscriberToList($this->getTestListId(), $subscription, $this->getNewsletterSubscriptionMapping(), false);

		$this->assertTrue($result instanceof Interspire_EmailIntegration_AddSubscriberResult, "Result of addSubscriberToList is not an instance of AddSubscriberResult as expected");
		$this->assertFalse($result->pending);
		$this->assertTrue($result->success, "Result of addSubscriberToList was not successful");

		// change their ip `asynchronously`
		$module->updateSubscriptionIP($subscription->getSubscriptionEmail(), '74.86.11.163');

		// run tasks until no more exist
		while (Interspire_TaskManager_Internal::executeNextTask('emailintegration')) { }

		// find their details and confirm the change
		$member = $module->findListSubscriber($this->getTestListId(), $subscription);
		$this->assertInstanceOf('Interspire_EmailIntegration_Subscription_Existing', $member);

		$this->assertEquals('74.86.11.163', $member->getSubscriptionIP());
	}

	abstract public function testRemoteVerifyApi ();
	protected function _testRemoteVerifyApi ($auth)
	{
		$module = $this->getConfiguredModule();
		$result = $module->remoteVerifyApi($auth, array());
		$this->assertInternalType('array', $result);
		$this->assertTrue($result['success'], "Result of remoteVerifyApi was not successful");
		$this->assertInternalType('string', $result['newsletterRules']);
		$this->assertInternalType('string', $result['customerRules']);
	}

	/**
	* @group remote
	*/
	public function testAsynchronousAddSubscriberToList ()
	{
		$this->deleteTestSubscriptions();
		$module = $this->getConfiguredModule();

		require_once dirname(__FILE__) . '/Subscription/Newsletter.php';
		$subscription = new Unit_EmailIntegration_Subscription_Newsletter();

		/** @var Interspire_EmailIntegration_Subscription_Newsletter */
		$subscription = $subscription->getSubscriptionInstance();
		$subscription->setDoubleOptIn(false);
		$subscription->setSendWelcome(false);

		$result = $module->addSubscriberToList($this->getTestListId(), $subscription, $this->getNewsletterSubscriptionMapping());

		$this->assertTrue($result->pending, "Result of addSubscriberToList was not pending as expected");
		$this->assertNull($result->success);
		$this->assertNull($result->existed);

		// run tasks until no more exist
		while (Interspire_TaskManager_Internal::executeNextTask('emailintegration')) { }

		$result = $module->findListSubscriber($this->getTestListId(), $subscription);
		$this->assertInstanceOf('Interspire_EmailIntegration_Subscription_Existing', $result, "After asynchronous add, a call to findListSubscriber failed to find the subscription which was supposed to have been added");
		$this->assertEquals($subscription->getSubscriptionEmail(), $result->getSubscriptionEmail());

		$remoteData = $result->getSubscriptionData();
		$this->assertArrayIsNotEmpty($remoteData, 'Failed asserting that subscription data for existing subscription is a non-empty array');
		$subscriptionData = $subscription->getSubscriptionData();
		foreach ($this->getNewsletterSubscriptionMapping() as $remote => $local) {
			$this->assertArrayHasKey($remote, $remoteData, 'Remote does not contain mapped field "' . $remote . '": ' . print_r($remoteData, true));
			$this->assertArrayHasKey($local, $subscriptionData, 'Local data does not contain mapped field "' . $local . '": ' . print_r($subscriptionData, true));
			// the assertion below does not currently work because local can be a complex data structure, but remote is usually simple
			//$this->assertEquals($subscriptionData[$local], $remoteData[$remote], 'Mapped remote subscription field "' . $remote . '" does not match local subscription field "' . $local . '"');
		}
	}

	abstract public function getNewsletterSubscriptionMapping ();

	public function newsletterSubscriptionRulesDataProvider ()
	{
		// flush existing rules
		Interspire_EmailIntegration_Rule::deleteAllRules();

		$data = array();

		$rule = new Interspire_EmailIntegration_Rule_NewsletterSubscribed(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $this->getNewsletterSubscriptionMapping());
		$data[] = array($rule, true, false);

		// empty map test
		$rule = new Interspire_EmailIntegration_Rule_NewsletterSubscribed(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), array());
		$data[] = array($rule, true, false);

		// disabling this item for now because the call to deleteTestSubscriptions in testNewsletterSubscriptionRouting
		// makes mailchimp respond with a failure when the remove doesn't work
		//$rule = new Interspire_EmailIntegration_Rule_NewsletterSubscribed(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_REMOVE, $this->getTestListId(), $this->getNewsletterSubscriptionMapping());
		//$data[] = array($rule, false, true);

		return $data;
	}

	/**
	* @dataProvider newsletterSubscriptionRulesDataProvider
	* @group remote
	*/
	public function testNewsletterSubscriptionRouting (Interspire_EmailIntegration_Rule $rule, $expectAdd, $expectRemove)
	{
		$this->assertTrue(Interspire_EmailIntegration_Rule::deleteAllRules(), "Failed to delete existing email integration rules");

		$rule->save();
		$this->assertNotNull($rule->id);

		$module = $this->getConfiguredModule();

		// make sure lists and fields are up to date from provider
		$lists = $module->getLists();
		foreach ($lists as $list) {
			$module->getListFields($list['provider_list_id']);
		}

		require_once dirname(__FILE__) . '/Subscription/Newsletter.php';
		$subscription = new Unit_EmailIntegration_Subscription_Newsletter();

		/** @var Interspire_EmailIntegration_Subscription_Newsletter */
		$subscription = $subscription->getSubscriptionInstance();
		$subscription->setDoubleOptIn(false);
		$subscription->setSendWelcome(false);

		$this->deleteTestSubscriptions();
		$results = $subscription->routeSubscription(false);
		$this->assertInternalType('array', $results);

		if ($expectAdd || $expectRemove) {
			$this->assertEquals(1, count($results), "Expected single result from routeSubscription but found none or more than one.");
			foreach ($results as /** @var Interspire_EmailIntegration_SubscriberActionResult */$result) {
				$this->assertFalse($result->pending, $result->apiErrorMessage . "\n\n" . $result->apiResponseBody);
				$this->assertEquals('emailintegration_' . $this->getModuleId(), $result->moduleId, "subscription passed through an unexpected module, possible configuration issue");
				if ($expectAdd) {
					$this->assertInstanceOf('Interspire_EmailIntegration_AddSubscriberResult', $result, "A result returned by routeSubscription was not of the expected class");
					$this->assertTrue($result->success, "Successful add expected, but success was false: " . $result->apiErrorMessage . "\n\n" . $result->apiResponseBody . "\n\n" . print_r($subscription->getSubscriptionData(), true));
				} else if ($expectRemove) {
					$this->assertInstanceOf('Interspire_EmailIntegration_RemoveSubscriberResult', $result, "A result returned by routeSubscription was not of the expected class");
					$this->assertTrue($result->success, "Successful remove expected, but success was false: " . $result->apiErrorMessage . "\n\n" . $result->apiResponseBody . "\n\n" . print_r($subscription->getSubscriptionData(), true));
				}
			}
		} else {
			$this->assertEquals(0, count($results));
		}

		/** @var Interspire_EmailIntegration_Subscription_Existing */
		$result = $module->findListSubscriber($this->getTestListId(), $subscription);
		if ($expectAdd) {
			$this->assertInstanceOf('Interspire_EmailIntegration_Subscription_Existing', $result, "routeSubscription reported OK but findListSubscriber did not return an instance of Interspire_EmailIntegration_Subscription_Existing");
			$this->assertEquals($subscription->getSubscriptionEmail(), $result->getSubscriptionEmail(), "Email address if subscriber located by findListSubscriber does not match email which was sent as subscription");
			$remoteData = $result->getSubscriptionData();
			$this->assertArrayIsNotEmpty($remoteData, 'Failed asserting that subscription data for existing subscription is a non-empty array');
			$subscriptionData = $subscription->getSubscriptionData();
			foreach ($rule->fieldMap as $remote => $local) {
				$this->assertArrayHasKey($remote, $remoteData, 'Remote does not contain mapped field "' . $remote . '": ' . print_r($remoteData, true));
				$this->assertArrayHasKey($local, $subscriptionData, 'Local data does not contain mapped field "' . $local . '": ' . print_r($subscriptionData, true));
				// the assertion below does not currently work because local can be a complex data structure, but remote is usually simple
				//$this->assertEquals($subscriptionData[$local], $remoteData[$remote], 'Mapped remote subscription field "' . $remote . '" does not match local subscription field "' . $local . '"');
			}
		} else if ($expectRemove) {
			$this->assertFalse($result);
		}
	}

	abstract public function getOrderSubscriptionMapping ();

	public function orderSubscriptionRulesDataProvider ()
	{
		// flush existing rules
		Interspire_EmailIntegration_Rule::deleteAllRules();

		$data = array();

		$map = $this->getOrderSubscriptionMapping();

		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $map);
		$data[] = array($rule, true, false);

		// empty map test
		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), array());
		$data[] = array($rule, true, false);

		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $map, array(
			'orderType' => 'category',
			'orderCriteria' => '1',
		));
		$data[] = array($rule, true, false);

		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $map, array(
			'orderType' => 'brand',
			'orderCriteria' => '1',
		));
		$data[] = array($rule, true, false);

		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $map, array(
			'orderType' => 'product',
			'orderCriteria' => '28',
		));
		$data[] = array($rule, true, false);

		$rule = new Interspire_EmailIntegration_Rule_OrderCompleted(null, 'emailintegration_' . $this->getModuleId(), Interspire_EmailIntegration_Rule::ACTION_ADD, $this->getTestListId(), $map, array(
			'orderType' => 'product',
			'orderCriteria' => '1',
		));
		$data[] = array($rule, false, false);

		return $data;
	}

	/**
	* @dataProvider orderSubscriptionRulesDataProvider
	* @group remote
	*/
	public function testOrderSubscriptionRouting ($rule, $expectAdd, $expectRemove)
	{
		$this->assertTrue(Interspire_EmailIntegration_Rule::deleteAllRules(), "Failed to delete existing email integration rules");

		$rule->save();
		$this->assertNotNull($rule->id);

		$module = $this->getConfiguredModule();

		if ($expectAdd) {
			$this->assertTrue(ISC_EMAILINTEGRATION::doOrderAddRulesExist(), 'list add is expected but no add rules exist');
		}

		// make sure lists and fields are up to date from provider
		$lists = $module->getLists();
		foreach ($lists as $list) {
			$module->getListFields($list['provider_list_id']);
		}

		require_once dirname(__FILE__) . '/Subscription/Order.php';
		$subscription = new Unit_EmailIntegration_Subscription_Order();

		/** @var Interspire_EmailIntegration_Subscription_Order */
		$subscription = $subscription->getSubscriptionInstance();
		$subscription->setDoubleOptIn(false);
		$subscription->setSendWelcome(false);

		$this->deleteTestSubscriptions();
		$results = $subscription->routeSubscription(false);
		$this->assertInternalType('array', $results, "Result from routeSubscription is not an array.");

		if ($expectAdd || $expectRemove) {
			$this->assertEquals(1, count($results), "Expected single result from routeSubscription but found none or more than one.");
			foreach ($results as /** @var Interspire_EmailIntegration_SubscriberActionResult */$result) {
				$this->assertFalse($result->pending, $result->apiErrorMessage . "\n\n" . $result->apiResponseBody);
				$this->assertEquals('emailintegration_' . $this->getModuleId(), $result->moduleId, $result->apiErrorMessage . "\n\n" . $result->apiResponseBody);
				if ($expectAdd) {
					$this->assertInstanceOf('Interspire_EmailIntegration_AddSubscriberResult', $result);
					$this->assertTrue($result->success, $result->apiErrorMessage . "\n\n" . $result->apiResponseBody);
				} else if ($expectRemove) {
					$this->assertInstanceOf('Interspire_EmailIntegration_RemoveSubscriberResult', $result);
					$this->assertTrue($result->success, $result->apiErrorMessage . "\n\n" . $result->apiResponseBody);
				}
			}
		} else {
			$this->assertEquals(0, count($results));
		}

		/** @var Interspire_EmailIntegration_Subscription_Existing */
		$result = $module->findListSubscriber($this->getTestListId(), $subscription);
		if ($expectAdd) {
			$this->assertInstanceOf('Interspire_EmailIntegration_Subscription_Existing', $result, "routeSubscription reported OK but findListSubscriber did not return an instance of Interspire_EmailIntegration_Subscription_Existing");
			$this->assertEquals($subscription->getSubscriptionEmail(), $result->getSubscriptionEmail(), "Email address if subscriber located by findListSubscriber does not match email which was sent as subscription");
			$remoteData = $result->getSubscriptionData();
			$this->assertArrayIsNotEmpty($remoteData, 'Failed asserting that subscription data for existing subscription is a non-empty array');
			$subscriptionData = $subscription->getSubscriptionData();
			foreach ($rule->fieldMap as $remote => $local) {
				$this->assertArrayHasKey($remote, $remoteData, 'Remote data does not contain mapped field "' . $remote . '": ' . print_r($remoteData, true));
				$this->assertArrayHasKey($local, $subscriptionData, 'Local data does not contain mapped field "' . $local . '": ' . print_r($subscriptionData, true));
				// the assertion below does not currently work because local can be a complex data structure, but remote is usually simple
				//$this->assertEquals($subscriptionData[$local], $remoteData[$remote], 'Mapped remote subscription field "' . $remote . '" does not match local subscription field "' . $local . '"');
			}
		} else if ($expectRemove) {
			$this->assertFalse($result);
		}
	}

	public function createTestCustomers ($batch)
	{
		$existing = $this->fixtures->FetchRow("SELECT COUNT(*) as customer_count FROM `[|PREFIX|]customers`");
		if ((int)$existing['customer_count'] > 0) {
			$this->fail("Cannot create test customers as customers already exist in the test database, meaning some other test is creating data but not removing it after it's done.");
			return false;
		}
		unset($existing);

		$form = new ISC_FORM();
		$entity = new Store_Customer_Gateway();

		$i = 0;
		while ($i < $batch) {
			$formSessionId = $form->saveFormSession(FORMFIELDS_FORM_ACCOUNT);
			if (!$formSessionId) {
				return false;
			}

			// this is based off a var_export of $StoreCustomer in ISC_ADMIN_CUSTOMERS->AddCustomerStep2 before the call to customerEntity->add
			$customer = array(
				'customerid' => 0,
				'custconfirstname' => ucfirst(Interspire_RandomWords::word()),
				'custconlastname' => ucfirst(Interspire_RandomWords::word()),
				'custconcompany' => ucwords(Interspire_RandomWords::phrase()),
				'custconemail' => preg_replace('#\W#', '', Interspire_RandomWords::word()) . '.' . preg_replace('#\W#', '', Interspire_RandomWords::word()) . '@4fite.com', // mailchimp won't accept example.com email addresses
				'custconphone' => '1234567890',
				'custpassword' => 'password',
				'custstorecredit' => number_format(mt_rand(1, 999999) * .01, '2', '.', ''),
				'custgroupid' => '0',
				'custformsessionid' => $formSessionId,
			);

			$customerId = $entity->add($customer);
			if (!$customerId) {
				return false;
			}

			// @todo create a last-used address

			$i++;
		}

		return $i;
	}

	public function removeTestCustomers ()
	{
		$entity = new Store_Customer_Gateway();

		$customers = $this->fixtures->Query("SELECT customerid FROM `[|PREFIX|]customers`");
		if (!$customers) {
			return false;
		}

		while ($customer = $this->fixtures->Fetch($customers)) {
			if (!$entity->delete($customer['customerid'])) {
				return false;
			}
		}

		return true;
	}

	/**
	* @group remote
	*/
	public function testCustomerModuleExport ()
	{
		$listId = $this->getBulkTestListId();
		if (!$listId) {
			$this->markTestSkipped();
			return;
		}

		// create customers to export
		$batch = Job_EmailIntegration_ModuleExport::BATCH_SIZE * 2.5;
		$customerCount = $this->createTestCustomers($batch);
		$this->assertEquals($batch, $customerCount, "Expected $batch test customers, but $customerCount were created instead");

		// make sure the module is configured
		$this->getConfiguredModule();

		// remove any users in the remote bulk list
		$this->deleteTestSubscriptions($listId);

		// make sure we're logged in as admin
		$this->assertTrue($this->login(), "Failed to login as admin user");

		// send data to the admin remote handler
		$data = array(
			'return' => true, // tell the handler to return data instead of outputting json

			'exportType' => 'Customer',
			'exportStep' => 'Commence',
			'exportModule' => $this->getModuleId(),

//			'exportSearch' => array(),
//			'exportMap' => array(),

			'exportList' => $listId,
			'exportDoubleOptin' => false,
			'exportUpdateExisting' => true,
		);

		$remote = new ISC_ADMIN_REMOTE_EMAILINTEGRATION();
		$data = $remote->handleModuleExport($data);
		$this->assertInternalType('array', $data, "Expected handleModuleExport to return an array");

		// process the job that should have been created by the above
		$expectedIterations = 4;
		$iterations = 0;
		$keystoreDumps = array();

		$keystorePrefix = 'email:module_export:' . $data['id'] . ':';

		while (Interspire_TaskManager_Internal::executeNextTask()) {
			$iterations++;

			// dump keystore data after each iteration
			$keystoreDumps[$iterations] = $this->keystore->multiGet($keystorePrefix . '*');
		}

		// check task results
		$this->assertEquals($expectedIterations, $iterations, "Expected $expectedIterations task iterations, but $iterations happened instead.");

		$keystoreLast = array_pop($keystoreDumps);
		$keystoreSecondLast = array_pop($keystoreDumps);

		foreach ($keystoreDumps as $iteration => $keystore) {
			// check results of each iteration
			$this->assertEquals(0, $keystore[$keystorePrefix . 'error_count'], "Error count was non-zero on iteration $iteration");

			$this->assertEquals(min(Job_EmailIntegration_ModuleExport::BATCH_SIZE * $iteration, $batch), $keystore[$keystorePrefix . 'success_count'], "Success count was incorrect on iteration $iteration");
		}

		$this->assertTrue(empty($keystoreLast), "Keystore for this export should be empty after last task run but isn't");

		// check subscription data exists and is correctly mapped at provider
		// note: this is currently mailchimp specific -- need a way of returning module-neutral *current* subscriber information?
		$subscriptions = $this->getAllListMembers($listId);
		$customers = $this->fixtures->Query('SELECT customerid, custconemail FROM `[|PREFIX|]customers` ORDER BY custconemail');

		// subscriptions and customers should both be ordered by email address so assume they will be in the same array index
		$customerOffset = 0;
		while ($customer = $this->fixtures->Fetch($customers)) {
			$subscription = $subscriptions[$customerOffset];
			// @todo check more than just email address to make sure fields are mapped correctly for batches
			$this->assertEquals($subscription['email'], $customer['custconemail'], "Subscription email does not match subscription email at offset $customerOffset");
			$customerOffset++;
		}

		$this->assertEquals(count($subscriptions), $customerOffset, "Customer and susbcription counts do not match");

		// remove test customers
		$this->assertTrue($this->removeTestCustomers(), "Failed to remove test customers");

		// remove test subscriptions
		$this->deleteTestSubscriptions($listId);
	}
}
