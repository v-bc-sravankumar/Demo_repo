<?php

class Unit_Checkout_Offline extends Interspire_IntegrationTest
{
	public $forms = array();

	public $vars = array(
		'bankdeposit' => array(
			'helptext' => 'Bank Name: ACME Bank
Bank Branch: New York
Account Name: John Smith
Account Number: XXXXXXXXXXXX

Type any special instructions in here.',
			'displayname' => 'Bank Deposit',
		),
		'bpay' => array(
			'padlength' => '10',
			'billercode' => '1234',
			'displayname' => 'BPAY',
		),
		'cheque' => array(
			'helptext' => 'Type instructions to pay by check in here.',
			'displayname' => 'Cheque',
		),
		'cod' => array(
			'helptext' => 'Type your cash on delivery instructions in here.',
			'displayname' => 'Cash on Delivery',
		),
		'instore' => array(
			'helptext' => 'Type instructions to pay by visiting your retail store in here.',
			'displayname' => 'Pay in Store',
		),
		'moneyorder' => array(
			'helptext' => 'Type instructions to pay by money order in here.',
			'displayname' => 'Money Order',
		),
		'creditcardmanually' => array(
			'acceptedcards' => array(
				'MAESTRO',
				'SOLO',
				'MC',
				'DINERS',
				'DISCOVER',
				'AMEX',
				'VISA',
				'SWITCH',
				'LASER',
				'JCB',
			),
			'displayname' => 'Credit Card (Manual)',
		),
	);

	public function setUp()
	{
		parent::setUp();

		require_once BUILD_ROOT.'/admin/init.php';

		$this->forms['creditcardmanually'] = array(
			'cc_ccno' => '4012888888881881',
			'cc_name' => 'TEST',
			'cc_cctype' => 'VISA',
			'cc_cvv2' => '123',
			'cc_ccexpm' => '09',
			'cc_ccexpy' => intval(date('y')) + 2,
			'cc_issuedatem' => '01',
			'cc_issuedatey' => '2009',
			'cc_issueno' => '1',
		);
	}

	public function moduleListDataProvider ()
	{
		require_once BUILD_ROOT.'/admin/init.php';

		$dh = opendir(BUILD_ROOT . '/modules/checkout');
		$modules = array();

		while ($entry = readdir($dh)) {
			$path = BUILD_ROOT . '/modules/checkout/' . $entry;

			if (!is_dir($path)) {
				continue;
			}

			$path .= '/module.' . $entry . '.php';

			if (!file_exists($path)) {
				continue;
			}

			$class = 'CHECKOUT_' . strtoupper($entry);
			require_once($path);
			$instance = new $class();
			if ($instance->GetPaymentType() !== PAYMENT_PROVIDER_OFFLINE) {
				continue;
			}

			if (isset($this->vars[$entry])) {
				$vars = $this->vars[$entry];
				$vars['is_setup'] = 1;
				$vars['availablecountries'] = 'all';
			} else {
				$vars = null;
			}

			$module = array($entry, $class, $path, $vars);
			$modules[] = $module;
		}

		closedir($dh);
		return $modules;
	}

	/**
	* @dataProvider moduleListDataProvider
	*/
	public function testOfflineMessage ($directory, $class, $path, $vars)
	{
		require_once($path);
		$module = new $class();

		if (!method_exists($module, 'GetOfflinePaymentMessage')) {
			$this->markTestSkipped("Module $directory is offline provider but does not implement GetOfflinePaymentMessage");
		}

		$this->assertNotNull($vars, "No module_vars provided for payment module $directory.");

                // load dummy method values into db
                $GLOBALS['ISC_CLASS_DB']->Query("DELETE FROM [|PREFIX|]module_vars WHERE modulename = 'checkout_$directory'");
                foreach ($vars as $key => $values) {
                        if (!is_array($values)) {
                                $values = array($values);
                        }

                        foreach ($values as $value) {
                                $GLOBALS['ISC_CLASS_DB']->InsertQuery('module_vars', array(
                                        'modulename' => 'checkout_' . $directory,
                                        'variablename' => $key,
                                        'variableval' => $value,
                                ));
                        }
                }

		$paymentData = array(
			'orders' => array(
				99999 => array(
					'orderid' => 99999,
				),
			),
		);

		$module->SetOrderData($paymentData);

		$message = $module->GetOfflinePaymentMessage();

                // remove dummy shipping vars
                $GLOBALS['ISC_CLASS_DB']->Query("DELETE FROM [|PREFIX|]module_vars WHERE modulename = 'checkout_$directory'");

		$this->assertFalse(empty($message), "Module $directory is not returning an offline payment message.");
	}

	/**
	* @dataProvider moduleListDataProvider
	*/
	public function testPayment ($directory, $class, $path, $vars)
	{
		require_once($path);
		$module = new $class();

		if (!method_exists($module, 'ProcessPaymentForm')) {
			$this->markTestSkipped("Module $directory is offline provider but does not implement ProcessPaymentForm.");
		}

		$this->assertTrue(isset($this->forms[$directory]), "No test payment form available for checkout module $directory");

		$form = $this->forms[$directory];
		foreach ($form as $key => $value) {
			$_POST[$key] = $value;
		}

		$paymentData = array(
			'orders' => array(
				99999 => array(
					'orderid' => 99999,
					'extrainfo' => '',
				),
			),
		);

		$module->SetOrderData($paymentData);
		$this->assertTrue($module->ProcessPaymentForm($form));
	}
}
