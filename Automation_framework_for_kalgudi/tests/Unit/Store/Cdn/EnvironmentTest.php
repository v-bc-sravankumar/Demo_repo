<?php

namespace Unit\Store\Cdn;

use Store_Cdn_Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    private $configOverrides = array();
    private $envOverrides = array();

    public function tearDown()
    {
        foreach ($this->configOverrides as $var => $value) {
            \Store_Config::override($var, $value);
        }

        foreach ($this->envOverrides as $var => $value) {
            putenv($var . '=' . $value);
        }
    }

    public function testGenerateDefaultUsesStoreConfigForVersionUrlIfDefined()
    {
        $this->overrides['CdnUrlVersion'] = \Store_Config::get('CdnUrlVersion');

        $versionUrl = 'protocol://test.com/config';

        \Store_Config::override('CdnUrlVersion', $versionUrl);

        $env = Store_Cdn_Environment::generateDefault();
        $this->assertEquals($versionUrl, $env->getVersionUrlTemplate());
    }

    public function testGenerateDefaultUsesEnvForVersionUrlIfConfigNotDefined()
    {
        $this->overrides['CdnUrlVersion'] = \Store_Config::get('CdnUrlVersion');
        $this->envOverrides['BC_CDN_URL_VERSION'] = getenv('BC_CDN_URL_VERSION');

        \Store_Config::override('CdnUrlVersion', '');

        $versionUrl = 'protocol://test.com/env';
        putenv('BC_CDN_URL_VERSION=' . $versionUrl);

        $env = Store_Cdn_Environment::generateDefault();
        $this->assertEquals($versionUrl, $env->getVersionUrlTemplate());
    }
}
