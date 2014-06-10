<?php

namespace Integration\DomainModel\Repository;

use Repository\ProfileSettings;

class ProfileSettingsTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSettings()
    {
        $repository = new ProfileSettings();
        $settings = $repository->getSettings();

        $this->assertArrayHasKey('settings', $settings);
        $this->assertEquals(count($this->getConfigMap()), count($settings['settings']));
    }

    public function testInvalidSettingsYieldErrors()
    {
        $repository = new ProfileSettings();
        $existingSettings = $repository->getSettings();

        $configMap = $this->getConfigMap();

        $data = array();
        foreach ($configMap as $key => $config) {
            $data[$key] = 999;
        }

        $settings = $repository->saveSettings($data);

        $this->assertArrayHasKey('errors', $settings);

        $this->assertArrayHasKey('name', $settings['errors']);
        $this->assertArrayHasKey('address', $settings['errors']);
        $this->assertArrayHasKey('address_type', $settings['errors']);
        $this->assertArrayHasKey('phone_number', $settings['errors']);
        $this->assertArrayHasKey('email_address', $settings['errors']);

        //cleanup
        $settings = $repository->saveSettings($existingSettings['settings']);
    }

    public function testValidSettingsAreSaved()
    {
        $repository = new ProfileSettings();
        $existingSettings = $repository->getSettings();

        $data = $this->getConfigMap();

        $settings = $repository->saveSettings($data);
        $this->assertFalse(array_key_exists('errors', $settings));

        $savedSettings = $repository->getSettings();

        $this->assertEquals($savedSettings, array('settings' => $data));

        //cleanup
        $settings = $repository->saveSettings($existingSettings['settings']);
    }

    protected function getConfigMap()
    {
        return array (
            'name' => 'Acme Widgets',
            'address' => 'Acme Widgets 123 Widget Street Acmeville, AC 12345 United States of America',
            'address_type' => 'Retail',
            'email_address' => 'contact@acmewidgets.com',
            'phone_number' => '754-3010',
        );
    }
}
