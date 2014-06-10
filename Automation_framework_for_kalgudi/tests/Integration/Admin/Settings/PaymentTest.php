<?php

namespace Integration\Admin\Settings;

class PaymentTest extends \PHPUnit_Framework_TestCase {

	const REF = 'ABC123';

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $mock;
	/** @var \ISC_ADMIN_SETTINGS_PAYMENT */
	private $paymentSettings;

	private $originalCurrency;
	private $featureEnabled;

	public static function setUpBeforeClass()
	{
		define('ISC_ADMIN_CP', 1); // Required for ISC_ADMIN_SETTINGS_PAYMENT
	}

	protected function setUp()
	{
		$this->featureEnabled = \Store_Feature::isEnabled('ReferralPreferredPaymentProviders');
		\Store_Feature::enable('ReferralPreferredPaymentProviders');
		$this->originalCurrency = $GLOBALS['ISC_CLASS_DATA_STORE']->Read('Currencies');
		$this->mock = $this->getMockBuilder('INTERSPIRE_TEMPLATE')
			->setMethods(array('assign', 'display'))
			->disableOriginalConstructor()
			->getMock();

		$this->paymentSettings = new StubSettingsPayments($this->mock);
	}

	protected function tearDown()
	{
		$GLOBALS['ISC_CLASS_DATA_STORE']->Save('Currencies', $this->originalCurrency);
		// Flush cache
		$GLOBALS['ISC_CLASS_DATA_STORE']->Read('Currencies', true);
		\Store_Feature::override('ReferralPreferredPaymentProviders', $this->featureEnabled);
	}

	private function setCurrency($code)
	{
		$GLOBALS['ISC_CLASS_DATA_STORE']->Save('Currencies', array(
			2 => array('currencyname' => $code, 'currencycode' => $code, 'currencyisdefault' => 1),
			'default' => 2,
		));
		// Calling Save against the data store doesn't actually invalidate the
		// cache, so we need to force read to flush cache :(
		$GLOBALS['ISC_CLASS_DATA_STORE']->Read('Currencies', true);
	}

	/**
	 * This test attempts to use HSBC as the preferred payment provider based on referrer ID
	 * set in config/environments/test.php
	 * HSBC is only supported if GBP is the default currency.
	 */
	public function testSupportedReferralProviders()
	{
		$this->setCurrency('GBP');
		\Store_Config::override(\Platform\Referral::CONFIG_KEY, self::REF);
		\Store_Config::override('UseSSL', true);

		$assignKey = null;
		$assignValue = null;

		$this->mock
			->expects($this->any())
			->method('assign')
			->will($this->returnCallback(function ($key, $val) use (&$assignKey, &$assignValue) {
				// Only capture the template->assign args when the first arg is 'paymentMethods'
				if ($key === 'paymentMethods') {
					$assignKey = $key;
					$assignValue = $val;
				}
			}));

		$this->paymentSettings->HandleToDo('');

		$this->assertEquals('paymentMethods', $assignKey);
		$this->assertArrayHasKey(\ISC_CHECKOUT_PROVIDER::METHOD_CREDIT_CARD, $assignValue);
		$this->assertCount(1, $assignValue[\ISC_CHECKOUT_PROVIDER::METHOD_CREDIT_CARD]['providers']);
		$this->assertArrayHasKey('hsbc', $assignValue[\ISC_CHECKOUT_PROVIDER::METHOD_CREDIT_CARD]['providers']);
		$this->assertTrue(
			$assignValue[\ISC_CHECKOUT_PROVIDER::METHOD_CREDIT_CARD]['providers']['hsbc']['recommended'],
			'Expected prodiver to be recommended'
		);
	}

	/**
	 * This test attempts to use HSBC as the preferred payment provider based on referrer ID
	 * but should fallback to Bigcommerce's preferred payments because the provider doesn't
	 * support USD currency.
	 */
	public function testUnsupportedReferralProviders()
	{
		$this->setCurrency('USD');
		\Store_Config::override(\Platform\Referral::CONFIG_KEY, self::REF);
		\Store_Config::override('UseSSL', true);

		$assignKey = null;
		$assignValue = null;

		$this->mock
			->expects($this->any())
			->method('assign')
			->will($this->returnCallback(function ($key, $val) use (&$assignKey, &$assignValue) {
				// Only capture the template->assign args when the first arg is 'paymentMethods'
				if ($key === 'paymentMethods') {
					$assignKey = $key;
					$assignValue = $val;
				}
			}));

		$this->paymentSettings->HandleToDo('');

		// Create an array of default recommended provider IDs
		$recommendedIds = array_map(function ($recommended) {
			return $recommended['id'];
		}, array_filter(\Payments\Providers::getAll(), function ($provider) {
			return $provider['object']->isRecommended();
		}));

		$this->assertNotEmpty($assignValue);
		foreach ($assignValue as $method) {
			$this->assertNotEmpty($method['providers']);
			foreach ($method['providers'] as $provider) {
				$this->assertContains($provider['id'], $recommendedIds);
			}
		}
	}
}

class StubSettingsPayments extends \ISC_ADMIN_SETTINGS_PAYMENT
{
	public function __construct(\Interspire_Template $template)
	{
		parent::__construct();
		$this->template = $template;

		$this->auth = new StubAuth();
	}
}

class StubAuth
{
	public function HasPermission($_)
	{
		return true;
	}
}