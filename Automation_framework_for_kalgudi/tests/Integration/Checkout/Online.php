<?php

class Unit_Checkout_Online extends Interspire_IntegrationTest
{
	/**
	* To be populated by setUp based on $moduleName
	*
	* @var ISC_CHECKOUT_PROVIDER
	*/
	public $module;

	/**
	* To be populated by setUp in child classes
	*
	* @var string
	*/
	public $moduleName;

	/**
	* Generic form values to be sent to the payment provider. To be populated by setUp.
	*
	* @var array
	*/
	public $form;

	/**
	* Order info to power the checkout module. To be populated by setUp.
	*
	* @var array
	*/
	public $order;

	/**
	* Order id for fake orders. To be populated by setUp.
	*
	* @var int
	*/
	public $orderId;

	/**
	* prefix this module uses for form fields
	*
	* @var string
	*/
	public $formPrefix = 'creditcard_';

	private $_backupUseSSL;

	private $_backupCheckoutMethods;

/*
	public $vars = array(
//		'chronopayapi' => array(
//			'sharedsecret' => 'Sha0oiyftyui',
//			'productid' => '004729-0001-0001',
//		),
//		'payjunction' => array(
//			'accountname' => 'pj-ql-01',
//			'accountpassword' => 'pj-ql-01p',
//			'cardcode' => 'NO',
//			'testmode' => 'YES',
//		),
//		'paypalpaymentsprous' => array(
//			'username' => 'yuki.c_1207726004_biz_api1.interspire.com',
//			'password' => 'L8LXK3ZNA7L357W8',
//			'signature' => 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-Aqp5gGMRyCF7vWyx4azR2Aq0iM6D',
//			'transactiontype' => 'Sale',
//			'cardcode' => 'NO',
//			'cardinalprocessorid' => '',
//			'cardinalmerchantid' => '',
//			'cardinaltransactionpwd' => '',
//			'testmode' => 'YES',
//		),
//		'paysimple' => array(
//			'merchantkey' => 'Eo1mhUaCNiuAH8G08nkzAqEhqK3ZTDGjae5WUlVBSGWBnO5jGhl5JybSQaS8J8tse2kvfPaIXz7APMtBA0Cl2rvdFCiBTq2rJnQJMySQ4LoCHmpWl3wtFns6jEf7HXu0',
//			'testmode' => 'YES',
//		),
	);
*/

	public function setUp()
	{
		parent::setUp();

		require_once BUILD_ROOT.'/admin/init.php';

		$path = BUILD_ROOT . '/modules/checkout/' . $this->moduleName . '/module.' . $this->moduleName . '.php';
		$class = 'CHECKOUT_' . isc_strtoupper($this->moduleName);

		$this->_backupUseSSL = Store_Config::get('UseSSL');
		$this->_backupCheckoutMethods = Store_Config::get('CheckoutMethods');

		// set UseSSL
		Store_Config::override('UseSSL', SSL_NORMAL);

		// set checkout methods to include this module
		Store_Config::override('CheckoutMethods', 'checkout_' . $this->moduleName);

		// load dummy method values into db
		$this->assertTrue($GLOBALS['ISC_CLASS_DB']->Query("DELETE FROM [|PREFIX|]module_vars WHERE modulename = 'checkout_" . $GLOBALS['ISC_CLASS_DB']->Quote($this->moduleName) . "'"), "Failed to delete existing module_vars: " . $GLOBALS['ISC_CLASS_DB']->GetErrorMsg());
		foreach ($this->vars as $key => $values) {
			if (!is_array($values)) {
				$values = array($values);
			}

			foreach ($values as $value) {
				$this->assertNotEquals(false, $GLOBALS['ISC_CLASS_DB']->InsertQuery('module_vars', array(
					'modulename' => 'checkout_' . $this->moduleName,
					'variablename' => $key,
					'variableval' => $value,
				)), "Failed to insert new module_vars: " . $GLOBALS['ISC_CLASS_DB']->GetErrorMsg());
			}
		}

		// update vars in the datastore
		$GLOBALS['ISC_CLASS_DATA_STORE'] = GetClass('ISC_DATA_STORE');
		$GLOBALS['ISC_CLASS_DATA_STORE']->UpdateCheckoutModuleVars();
		$GLOBALS['ISC_CLASS_DATA_STORE']->Reload('CheckoutModuleVars');

		require_once($path);

		$this->module = new $class();

		$_COOKIE['SHOP_ORDER_TOKEN'] = GenerateOrderToken();

		$this->form = array(
			$this->formPrefix . 'cctype' => 'VISA',
			$this->formPrefix . 'name' => 'TEST',
			$this->formPrefix . 'ccno' => '4111111111111111',
			$this->formPrefix . 'ccexpm' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
			$this->formPrefix . 'ccexpy' => (int)date('y') + rand(2, 3),
			$this->formPrefix . 'cccvd' => '123',
			$this->formPrefix . 'cccode' => '123',
			$this->formPrefix . 'cvn' => '123',
			$this->formPrefix . 'issueno' => '',
			$this->formPrefix . 'issuedatem' => '',
			$this->formPrefix . 'issuedatey' => '',
		);

		$this->orderId = time() . rand(100, 999);

		$this->order = array(
			'orders' => array(
				$this->orderId => array(
					'orderid' => $this->orderId,
					'extrainfo' => '',
					'total_inc_tax' => rand(1, 999) . '.00',
					'total_tax' => 0,
					'ordipaddress' => '127.0.0.1',
					'ordcustid' => rand(1000000, 9999999),
					'ordbillfirstname' => 'FIRST',
					'ordbilllastname' => 'LAST',
					'ordbillemail' => 'unit.test@interspire.com',
					'ordbillphone' => '99999999',
					'ordbillcompany' => '',
					'ordbillstreet1' => '123 Main Street',
					'ordbillstreet2' => '',
					'ordbillsuburb' => 'Surry Hills',
					'ordbillzip' => '2010',
					'ordbillstate' => 'NSW',
					'ordbillstateid' => '0',
					'ordbillcountry' => 'Australia',
					'ordbillcountryid' => 13,
					'ordbillcountrycode' => 'AU',
					// Forces billing address to be the same as the shipping address
					'ordisdigital' => 1,
					'ordcurrencyid' => 1,
				),
			),
		);

		/*
		$this->forms['chronopayapi'] = $genericCreditCardForm;

		$this->forms['payjunction'] = $genericCreditCardForm;

		$this->forms['paypalpaymentsprous'] = $genericCreditCardForm;
		$this->forms['paypalpaymentsprous']['creditcard_ccno'] = '4595258908900506';

		$this->forms['paysimple'] = $genericCreditCardForm;
		*/
	}

	public function tearDown()
	{
		Store_Config::override('UseSSL', $this->_backupUseSSL);
		Store_Config::override('CheckoutMethods', $this->_backupCheckoutMethods);

		parent::tearDown();
	}

	/**
	* Method to call a module's ProcessPaymentForm method. This exists so a test class can override how it is called (like how the paypalpaymentsprous needs to be in manual mode to not die with a redirect).
	*
	* @param ISC_CHECKOUT_PROVIDER $module
	* @param array $form
	*/
	protected function callProcessPaymentForm ($module, $form)
	{
		return $module->ProcessPaymentForm($form);
	}

	public function testProcessPaymentForm ()
	{
		$this->module->SetOrderData($this->order);
		$this->assertFalse(method_exists($this->module, 'TransferToProvider'), "Cannot test ProcessPaymentForm for redirect (TransferToProvider) modules.");
		$this->assertTrue(method_exists($this->module, 'ProcessPaymentForm'), "Module has no public ProcessPaymentForm method.");
		$result = $this->callProcessPaymentForm($this->module, $this->form);

		$errors = $this->module->getErrors();

		// If the module returned some sort of bad state, check the store logs for any detailed error messages so we can show those.
		// This is not the best solution, and is a "hack" but at the moment, it's the best way for us to get feedback on the real
		// reasons for checkout module failures.
		if(!empty($errors) || $result == false) {
			$query = "
				SELECT logsummary, logmsg
				FROM [|PREFIX|]system_log
				WHERE logtype='payment' AND logmodule='".$GLOBALS['ISC_CLASS_DB']->quote($this->module->getName())."' AND logseverity='".LOG_SEVERITY_ERROR."'
			";
			$result = $GLOBALS['ISC_CLASS_DB']->query($query);
			while($message = $GLOBALS['ISC_CLASS_DB']->fetch($result)) {
				$errors[] = $message['logsummary'].': '.$message['logmsg'];
			}

			if(empty($errors)) {
				$this->fail('Module returned false for result for ProcessPaymentForm but generated no errors.');
			}
			else {
				$this->fail('Module produced errors: ('.implode(') (', $errors).')');
			}
		}

		$paymentStatus = $this->module->GetPaymentStatus();
		$orderStatus = GetOrderStatusFromPaymentStatus($paymentStatus);
		$this->assertTrue(in_array($orderStatus, GetPaidOrderStatusArray()), "Status returned by GetPaymentStatus is not a paid status");
	}

	/*
	public function testOrderMarkedAsPaid ()
	{
		$_SESSION['CART'] = array();
		$_SESSION['CHECKOUT'] = array();

		$cart = new ISC_CART_API();
		$cart->SetCartSession($_SESSION['CART']);

		$cart->AddItem(24, 1); // sample product: ipod socks, $29

		$cart->Set('SHIPPING_METHOD', array(
			'methodName' => 'TEST',
			'methodCost' => DefaultPriceFormat(rand(0, 70)), // random shipping price, not a typical scenario but it bypasses dupe transaction checking in the online providers
			'methodId' => '',
			'methodModule' => 'custom',
			'handlingCost' => 0
		));

		$address = array(
			'shipfirstname' => 'FIRST',
			'shiplastname' => 'LAST',
			'shipcompany' => '',
			'shipaddress1' => '123 Main Street',
			'shipaddress2' => '',
			'shipcity' => 'Seattle',
			'shipstate' => 'Washington',
			'shipstateid' => '62',
			'shipzip' => '98101',
			'shipcountry' => 'United States',
			'shipcountryid' => '226',
			'shipemail' => 'unit.test@interspire.com',
			'shipphone' => '99999999',
			'shipdestination' => 'residential',
			'saveAddress' => true,
		);

		$_SESSION['CHECKOUT']['CHECKOUT_TYPE'] = 'express';

		// fake quote
		$_SESSION['CHECKOUT']['SHIPPING_QUOTES'][0][0] = array(
			1 => array(
				'description' => 'TEST',
				'price' => 0,
				'handling' => 0,
				'module' => '',
				'methodId' => 0,
			),
		);


		$checkout = GetClass('ISC_CHECKOUT');
		$this->assertTrue($checkout->SetOrderBillingAddress($address), "SetOrderBillingAddress did not return expected value of true");
		$this->assertTrue($checkout->SetOrderShippingAddress($address), "SetOrderShippingAddress did not return expected value of true");
		$checkout->SetOrderShippingProvider(0, 0, 1);
		$checkout->BuildOrderConfirmation();

		// manipulation of $_POST as SavePendingOrder accesses it directly
		$_POST['checkout_provider'] = 'checkout_' . $this->moduleName;
		$_POST['AgreeTermsAndConditions'] = 'on';
		$_POST['anonymousCheckout'] = 'on';
		$pendingResult = $checkout->SavePendingOrder();
		unset($_POST['checkout_provider'], $_POST['AgreeTermsAndConditions'], $_POST['anonymousCheckout']);

		$this->assertTrue(is_array($pendingResult), "SavePendingOrder did not return expected value type of array");
		$this->assertFalse(isset($pendingResult['error']), "SavePendingOrder produced errors. Error:[" . @$pendingResult['error'] . "] ErrorDetails:[" . @$pendingResult['errorDetails'] . "]");

		$module = $pendingResult['provider'];
		$result = $this->callProcessPaymentForm($module, $this->form);
		$this->assertEquals(0, count($module->GetErrors()), "Module produced errors: (" . implode(') (', $module->GetErrors()) .")");
		$this->assertTrue($result, "Module returned false result for ProcessPaymentForm but generated no errors.");

		$paymentStatus = $module->GetPaymentStatus();
		$orderStatus = GetOrderStatusFromPaymentStatus($paymentStatus);
		$this->assertTrue(in_array($orderStatus, GetPaidOrderStatusArray()), "Status returned by GetPaymentStatus is not a paid status");
		$this->assertTrue(CompletePendingOrder($_COOKIE['SHOP_ORDER_TOKEN'], $orderStatus, false), "CompletePendingOrder did not return expected value of true");
	}
	*/
}
