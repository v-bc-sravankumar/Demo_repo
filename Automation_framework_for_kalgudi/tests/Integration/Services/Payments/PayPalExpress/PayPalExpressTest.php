<?php

namespace Integration\Services\Payments\PayPalExpress;

use Services\Payments\PayPalExpress\PayPalExpress;

class PayPalExpressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Our service instance.
     * @var PayPalExpress
     */
    private $service;

    /**
     * Some dummy configuration data for use in tests.
     * @var array
     */
    private $config = array(
        'username' => 'John',
        'signature' => 'Johnski',
        'password' => 'foo',
        'transactiontype' => 'Authorization',
        'testmode' => 'NO'
    );

    public function setUp()
    {
        parent::setUp();
        $this->service = new PayPalExpress();
    }

    public function testSupportedCurrencyCodesAreReturned()
    {
        $actual = $this->service->getSupportedCurrencyCodes();

        $expected = array(
            'USD',
            'EUR',
            'GBP',
            'JPY',
            'CAD',
            'AUD',
            'MXN',
            'MXP',
            'NZD',
            'CHF',
            'HKD',
            'SGD',
            'SEK',
            'DKK',
            'PLN',
            'NOK',
            'HUF',
            'CZK',
            'ILS',
            'BRL',
            'MYR',
            'PHP',
            'TWD',
            'THB',
            'TRY',
        );

        sort($expected);
        sort($actual);

        $intersection = array_intersect($expected, $actual);

        $this->assertEquals($expected, $intersection);
    }

    public function testConfigFetching()
    {
        // Grab the config.
        $config = $this->service->getConfig();

        // Verify that it has the expected fields.
        $this->assertTrue(is_array($config));
        foreach ($this->config as $key => $value) {
            $this->assertTrue(array_key_exists($key, $config));
        }
    }

    public function testConfigUpdating()
    {
        // Mock out the underlying engine.
        $mock = $this->getMock('\Onboarding\InMemoryStepDataProvider');
        $mock->expects($this->once())
            ->method('markComplete')
            ->with($this->equalTo(PayPalExpress::GETTING_STARTED_ID));
        $this->service->setProvider($mock);

        // Update.
        $this->service->update($this->config);

        // Reload and verify the changes.
        $values = $this->service->getConfig();
        foreach ($this->config as $key => $value) {
            $this->assertEquals($value, $values[$key]);
        }

        // Destroy the settings.
        $module = $this->service->getModule();
        $module->DeleteModuleSettings();
    }

    public function testEnablingAndDisabling()
    {
        // Setup some mocks/expectations around the deactivation event.
        $handler = $this->getMock('stdClass', array('handle'));
        \Interspire_Event::bind(\Store_Event::EVENT_CONTROL_PANEL_MODULE_DEACTIVATED, array($handler, 'handle'));
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('Interspire_Event'));

        // Enable PayPal.
        $this->service->enable();
        $this->assertContains($this->service->getCheckoutId(), \Store_Config::get(PayPalExpress::CONFIG_ID));

        // Disable it.
        $this->service->disable();
        $this->assertNotContains($this->service->getCheckoutId(), \Store_Config::get(PayPalExpress::CONFIG_ID));
    }

    public function testConfigMerging()
    {
        // Build some pretend config.
        $old = array('a' => 'foo');
        $new = array('b' => 'bar');

        // Merge.
        $merged = $this->service->mergeConfig($old, $new);

        // Verify.
        $this->assertEquals(array(
            'a' => 'foo',
            'b' => 'bar'
        ), $merged);
    }

    public function testConfigMergingWithBlankValues()
    {
        // Build some pretend config.
        $old = array('a' => 'foo');
        $new = array('a' => '', 'b' => 'bar');

        // Merge.
        $merged = $this->service->mergeConfig($old, $new);

        // Make sure the blank value for 'a' was ignored.
        $this->assertEquals(array(
            'a' => 'foo',
            'b' => 'bar'
        ), $merged);
    }

    public function testConfigValidation()
    {
        // Build some config.
        $config = array(
            'a' => 'foo',               // Should be stripped.
            'transactiontype' => 'Foo', // Should be stripped.
            'testmode' => 'YES'         // Should be accepted.
        );

        // Validate
        $validated = $this->service->validateConfig($config);

        // Make sure the invalid values were stripped.
        $this->assertEquals(array(
            'testmode' => 'YES'
        ), $validated);
    }

    public function testIsEnabled()
    {
        // Grab the starting value for the relevant store configuration option.
        $initialCheckouts = $this->service->getCheckouts();

        // Add PayPal to our list of checkouts.
        $this->service->saveCheckouts(array($this->service->getCheckoutId()));
        $this->assertTrue($this->service->isEnabled());

        // Remove it.
        $this->service->saveCheckouts(array());
        $this->assertFalse($this->service->isEnabled());

        // Cleanup.
        $this->service->saveCheckouts($initialCheckouts);
    }
}
