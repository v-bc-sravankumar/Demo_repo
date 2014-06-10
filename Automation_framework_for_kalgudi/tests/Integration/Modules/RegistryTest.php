<?php
use ModuleRegistry\RegistryLoader;
use ModuleRegistry\Config;
use ModuleRegistry\ModuleRegistry;

class RegistryTest extends PHPUnit_Framework_TestCase
{
    public function getCorrectRegistryIsLoadedDataProvider()
    {
        return array(
            array('CHECKOUT_STRIPE', 'ModuleRegistry\\Checkout\\Stripe', 'modules/checkout/stripe'),
            array('CHECKOUT_FAKE', null, ''),
            array('Checkout_Stripe', 'ModuleRegistry\\Checkout\\Stripe', 'modules/checkout/stripe'),
            array('foobar', null, ''),
        );
    }

    /**
     * @param string $moduleClass
     * @param string $expectedRegistryClass
     * @param string $moduleDir
     * @dataProvider getCorrectRegistryIsLoadedDataProvider
     */
    public function testCorrectRegistryIsLoaded($moduleClass, $expectedRegistryClass, $moduleDir)
    {
        /** @var ISC_MODULE $module */
        if (class_exists($moduleClass)) {
            $module = new $moduleClass();
            $registry = $module->getRegistry();
        } else {
            $registry = null;
        }

        if ($registry === null) {
            $this->assertEquals($expectedRegistryClass, $registry);
        } else {
            $this->assertEquals($expectedRegistryClass, get_class($registry));
            $this->assertEquals(ISC_BASE_PATH.'/'.$moduleDir, $registry->getModuleDir());
        }
    }

    public function getCorrectModuleLanguageFileLoadedDataProvider()
    {
        return array(
            array('CHECKOUT_STRIPE', array(
                    'StripeName' => 'Stripe',
                    'StripeDesc' => 'Stripe payment module',
                ),
            ),
        );
    }

    /**
     * @param string $moduleClass
     * @param array $langVars
     * @dataProvider getCorrectModuleLanguageFileLoadedDataProvider
     */
    public function testCorrectModuleLanguageFileLoaded($moduleClass, $langVars)
    {
        /** @var ISC_MODULE $module */
        $module = new $moduleClass();

        // ensure this language file gets loaded in case it has been previously
        // loaded to override any conflicting language variables
        $registry = $module->getRegistry();
        $registry->loadLang(true);

        // now module has been instantiated check if language variable is available
        foreach ($langVars as $name => $value) {
            $this->assertEquals($value, GetLang($name));
        }
    }

    /**
     * @return array
     */
    public function getPaymentProviderIsVisibleDataProvider()
    {
        return array(
            array(
                'CHECKOUT_STRIPE',
                array(
                    'Feature_ModernUI' => true,
                ),
                true,
            ),
            array(
                'CHECKOUT_STRIPE',
                array(
                    'Feature_ModernUI' => false,
                ),
                false,
            ),
        );
    }

    /**
     *
     * @param string $moduleClass
     * @param array $overrideConfigs
     * @param bool $isVisible
     * @dataProvider getPaymentProviderIsVisibleDataProvider
     */
    public function testPaymentProviderIsVisible($moduleClass, $overrideConfigs, $isVisible)
    {
        /** @var ISC_CHECKOUT_PROVIDER $module */
        $module = new $moduleClass();

        $this->overrideConfigVars($overrideConfigs);

        $this->assertEquals($isVisible, $module->isVisible());
    }

    /**
     * @return array
     */
    public function getPaymentProviderIsRecommendedDataProvider()
    {
        return array(
            array(
                'stripe',
                array('UseSSL' => 1),
                array(
                    'getDefaultCurrencyCode' => 'USD',
                    'getCountryCode' => 'US',
                ),
                true,
            ),
            array(
                'stripe',
                array('UseSSL' => 1),
                array(
                    'getDefaultCurrencyCode' => 'AUD',
                    'getCountryCode' => 'US',
                ),
                false,
            ),
            array(
                'stripe',
                array('UseSSL' => 1),
                array(
                    'getDefaultCurrencyCode' => 'EUR',
                    'getCountryCode' => 'GB',
                ),
                true,
            ),
            array(
                'stripe',
                array('UseSSL' => 1),
                array(
                    'getDefaultCurrencyCode' => 'CAD',
                    'getCountryCode' => 'GB',
                ),
                false,
            ),
            array(
                'stripe',
                array('UseSSL' => 1),
                array(
                    'getDefaultCurrencyCode' => 'USD',
                    'getCountryCode' => 'AU',
                ),
                false,
            ),
        );
    }

    /**
     * @param $registryId
     * @param array $overrideConfigs
     * @param array $overrideMethods
     * @param bool $isRecommended
     * @dataProvider getPaymentProviderIsRecommendedDataProvider
     */
    public function testPaymentProviderIsRecommended($registryId, $overrideConfigs, $overrideMethods, $isRecommended)
    {
        $loader = new RegistryLoader('checkout', $registryId);
        $registry = $loader();

        /** @var \ModuleRegistry\Checkout|PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock(get_class($registry), array('getModule'), array($loader));

        /** @var ISC_CHECKOUT_PROVIDER|PHPUnit_Framework_MockObject_MockObject $module */
        $module = $this->getMock($registry->getModuleClass(), array_keys($overrideMethods), array(), '', false);

        foreach ($overrideMethods as $method => $returnValue) {
            $module->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        $module->__construct();

        $registry->expects($this->any())
            ->method('getModule')
            ->will($this->returnValue($module));

        $this->overrideConfigVars($overrideConfigs);

        $this->assertEquals($isRecommended, $registry->isRecommended());
    }

    /**
     * @return array
     */
    public function getModuleIsEnabledDataProvider()
    {
        return array(
            array(
                'CHECKOUT_STRIPE',
                array(
                    'CheckoutMethods'  => 'checkout_stripe',
                ),
                true,
            ),
            array(
                'CHECKOUT_STRIPE',
                array(
                    'CheckoutMethods'  => '',
                ),
                false,
            ),
        );
    }

    /**
     *
     * @param string $moduleClass
     * @param array $overrideConfigs
     * @param bool $isEnabled
     * @dataProvider getModuleIsEnabledDataProvider
     */
    public function testModuleIsEnabled($moduleClass, $overrideConfigs, $isEnabled)
    {
        /** @var ISC_MODULE $module */
        $module = new $moduleClass();

        $this->overrideConfigVars($overrideConfigs);

        $this->assertEquals($isEnabled, $module->IsEnabled());
    }

    /**
     * @return array
     */
    public function getIdDataProvider()
    {
        return array(
            array('CHECKOUT_STRIPE', 'checkout_stripe'),
        );
    }

    /**
     * @param string $moduleClass
     * @param string $id
     * @dataProvider getIdDataProvider
     */
    public function testGetId($moduleClass, $id)
    {
        /** @var ISC_MODULE $module */
        $module = new $moduleClass();
        $this->assertEquals($id, $module->GetId());
    }

    /**
     * @return array
     */
    public function getRuntimeExceptionThrownWhenLoadingInvalidRegistryDataProvider()
    {
        return array(
            array('invalid_type', ''),
            array('checkout', 'invalid'),
            array('checkout', 'provider'),
            array('checkout', ''),
        );
    }

    /**
     * @param string $type
     * @param string $id
     * @expectedException \RuntimeException
     * @dataProvider getRuntimeExceptionThrownWhenLoadingInvalidRegistryDataProvider
     */
    public function testRuntimeExceptionThrownWhenLoadingInvalidRegistry($type, $id)
    {

        $loader = new RegistryLoader($type, $id);
        $loader();
    }

    /**
     * @return array
     */
    public function getCorrectModuleTemplateIsLoadedDataProvider()
    {
        return array(
            array(
                'CHECKOUT_STRIPE',
                'test-mode-toggle.tpl',
                '<div class="checkbox-toggle',
            ),
        );
    }

    /**
     * @param $moduleClass
     * @param $templateFileName
     * @param $pattern
     * @dataProvider getCorrectModuleTemplateIsLoadedDataProvider
     */
    public function testCorrectModuleTemplateIsLoaded($moduleClass, $templateFileName, $pattern)
    {
        /** @var ISC_MODULE $module */
        $module = new $moduleClass();
        $template = $module->getTemplateClass();

        $templateStr = $template->render($templateFileName);

        $this->assertContains($pattern, $templateStr);
    }

    public function testGetRegistryList()
    {
        $loader = new RegistryLoader('checkout');
        $checkoutProviders = $loader->getRegistryList();
        $this->assertGreaterThan(0, count($checkoutProviders));

        $provider = current($checkoutProviders);
        $this->assertInstanceOf('\ModuleRegistry\Checkout', $provider);
    }

    public function testRegistryConfig()
    {
        $config = Config::getConfig(ISC_BASE_PATH.'/config/registry/checkout.php');
        $this->assertArrayHasKey('stripe', $config);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testRuntimeExceptionIsThrownGettingInvalidConfig()
    {
        Config::getConfig('config/path/not/exists');
    }

    /**
     * @param array $configs
     */
    private function overrideConfigVars($configs)
    {
        foreach ($configs as $key => $value) {
            Store_Config::override($key, $value);
        }
    }
}
