<?php

namespace Integration\Services\Payments\Stripe;

use Services\Payments\Stripe\BCStripe;

class StripeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|CHECKOUT_STRIPE
     */
    private $provider;

    private $overriddenConfigs = array();

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('CHECKOUT_STRIPE')
            ->setMethods(array('getHelpHtml','getUser', 'getCountryCode', 'getDefaultCurrencyCode'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(array(
                'useremail'     => '',
                'userfirstname' => 'John',
                'userlastname'  => 'Doe',
            )));

        $this->provider->__construct();
    }

    /**
     * @return array
     */
    public function providerConnectWithStripeUri()
    {
        $respArr = array(
            'state'                       => '',
            'client_id'                   => 'client-id',
            'scope'                       => 'read_write',
            'response_type'               => 'code',
            'stripe_user'                 => array(
                'country'        => 'CA',
                'currency'       => 'cad',
                'email'          => '',
                'first_name'     => 'John',
                'last_name'      => 'Doe',
                'state'          => 'TX',
                'business_name'  => 'My Store',
                'url'            => '',
                'street_address' => '123 Test St',
                'city'           => 'Austin',
                'zip'            => '11111',
                'phone_number'   => '22222',
            ),
        );

        $tests = array(
            array(
                array(),  // config
                array(),   // params
                $respArr,  // expected array of uri get parameters
            ),
        );


        $respArr['stripe_user']['street_address'] = '123 Test St';
        $tests[] = array(
            array(
                'CompanyAddress' => '',
                'StoreAddress'   => "123 Test St,\nCity TX 12345",
            ),
            array(),
            $respArr,
        );

        $respArr['stripe_user']['street_address'] = '11 Main St';
        $tests[] = array(
            array(
                'CompanyAddress' => '11 Main St',
                'StoreAddress'   => "123 Test St,\nCity TX 12345",
            ),
            array(),
            $respArr,
        );

        return $tests;
    }

    /**
     * @dataProvider providerConnectWithStripeUri
     */
    public function testConnectWithStripeUri($config, $params, $expectedParams)
    {
        $config += array(
            'ShopPathNormal'   => '',
            'ShopPath'         => '',
            'StoreName'        => 'My Store',
            'CompanyAddress'   => '123 Test St',
            'CompanyState'     => 'Texas',
            'CompanyCity'      => 'Austin',
            'CompanyZip'       => '11111',
            'StorePhoneNumber' => '22222',
        );

        $params += array(
            'state'                 => '',
            'client_id'             => 'client-id',
        );

        $userParams = array(
            'country'  => 'CA',
            'currency' => 'cad',
        );

        foreach($config as $key => $value) {
            $this->overrideConfig($key, $value);
        }

        $uri = $this->provider->getConnectWithStripeUri($params, $userParams);
        $uriDetails = parse_url($uri);
        parse_str($uriDetails['query'], $uriParams);

        $this->assertTrue($expectedParams == $uriParams, "expected params:\n" . var_export($expectedParams, true) . "\nactual params:\n" . var_export($uriParams, true));
    }

    public function testEncodeDecodeStates()
    {
        $testParams = array(
            'key1' => 'abc',
            'key2' => 'xyz',
        );
        $state = $this->provider->encodeState($testParams);
        $decoded = $this->provider->decodeState($state);

        $this->assertEquals($testParams, $decoded);
    }

    /**
     * @return array
     */
    public function getSupportedCountriesCurrencies()
    {
        $tests = array();
        $regions = new \Geography\Regions();
        foreach(BCStripe::getSupportedCountries() as $countryCode) {
            $stripe = new BCStripe($countryCode);
            foreach($stripe->getSupportedCurrencies() as $currencyCode) {
                $tests[] = array($regions->findCountryByCode($countryCode)->getCountryName(), $currencyCode, true);
            }
        }

        $tests[] = array('Australia', 'USD', false);
        $tests[] = array('United States', 'GBP', false);
        $tests[] = array('United Kingdom', 'CAD', false);

        return $tests;
    }

    /**
     * @dataProvider getSupportedCountriesCurrencies
     */
    public function testSupportedCountriesCurrencies($country, $currencyCode, $expectedSupported)
    {
        $this->overrideConfig('BillingCountry', $country);
        $this->overrideConfig('UseSSL', 1);

        $stripeModule = $this->getStripeMock($currencyCode);

        $isSupported = $stripeModule->isSupported();

        $this->assertTrue($expectedSupported == $isSupported, 'For '.$country.' the currency '.$currencyCode.' is expected to '.($expectedSupported ? '' : 'not ').'be supported');
    }

    /**
     * @param $currencyCode
     * @return CHECKOUT_STRIPE|PHPUnit_Framework_MockObject_MockObject
     */
    private function getStripeMock($currencyCode)
    {
        /** @var CHECKOUT_STRIPE|PHPUnit_Framework_MockObject_MockObject $stripeModule */
        $stripeModule = $this->getMockBuilder('CHECKOUT_STRIPE')
            ->setMethods(array('getDefaultCurrencyCode', 'getHelpHtml'))
            ->disableOriginalConstructor()
            ->getMock();

        $stripeModule->expects($this->any())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue($currencyCode));

        $stripeModule->__construct();
        return $stripeModule;
    }

    /**
     * @return array
     */
    public function getSupportedCardsScenarios()
    {
        return array(
            array('United States', 'USD', array('VISA', 'MC', 'AMEX', 'DISCOVER', 'JCB', 'DINERS')),
            array('United States', 'CAD', array()),
            array('Canada', 'CAD', array('VISA', 'MC', 'AMEX')),
            array('Canada', 'USD', array('VISA', 'MC', 'AMEX')),
            array('Canada', 'AUD', array()),
            array('United Kingdom', 'USD', array('VISA', 'MC', 'AMEX')),
            array('United Kingdom', 'GBP', array('VISA', 'MC', 'AMEX')),
            array('United Kingdom', 'EUR', array('VISA', 'MC', 'AMEX')),
            array('United Kingdom', 'AUD', array()),
            array('Ireland', 'USD', array('VISA', 'MC', 'AMEX')),
            array('Ireland', 'GBP', array('VISA', 'MC', 'AMEX')),
            array('Ireland', 'EUR', array('VISA', 'MC', 'AMEX')),
            array('Ireland', 'CAD', array()),
            array('Australia', 'USD', array()),
        );
    }

    /**
     * @dataProvider getSupportedCardsScenarios
     */
    public function testSupportedCards($country, $currencyCode, $expectedCards)
    {
        $this->overrideConfig('BillingCountry', $country);
        $this->overrideConfig('UseSSL', 1);

        $stripeModule = $this->getStripeMock($currencyCode);

        $supportedCards = $stripeModule->getSupportedCards();

        $this->assertEquals($stripeModule->getDefaultCurrencyCode(), $currencyCode);

        $this->assertTrue($expectedCards == $supportedCards, 'For '.$country.' with currency '.$currencyCode.' expected supported cards: '.implode(',', $expectedCards).', actual supported cards: '.implode(',', $supportedCards));
    }

    private function overrideConfig($key, $value)
    {
        $this->overriddenConfigs[$key] = \Store_Config::get($key);
        \Store_Config::override($key, $value);
    }

    public function tearDown()
    {
        // reset configuration variables back to what they were before being overridden
        foreach ($this->overriddenConfigs as $key => $value) {
            \Store_Config::override($key, $value);
        }
    }
}