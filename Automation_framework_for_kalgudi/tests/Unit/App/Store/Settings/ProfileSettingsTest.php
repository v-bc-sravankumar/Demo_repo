<?php

namespace Unit\App\Store\Settings;

use Store\Settings\ProfileSettings;
use PHPUnit_Framework_TestCase;
use Store_Settings;
use Store_Config;
use Store_Settings_Driver_Dummy;
use Lingua_Text;

class ProfileSettingsTest extends PHPUnit_Framework_TestCase
{
    protected function createProfileSettings()
    {
        $settings = new Store_Settings();
        $settings->setDriver(new Store_Settings_Driver_Dummy());
        $settings->load();

        return new ProfileSettings($this->getConfigMap(), $settings);
    }

    protected function assertValidFieldIsValid($field, $value)
    {
        $setter = 'set' . Lingua_Text::toClassName($field);
        $model = $this->createProfileSettings();
        $model->$setter($value);

        $this->assertTrue($model->isValid());
        $this->assertEmpty($model->getValidationErrors());
    }

    protected function assertInvalidFieldIsInvalid($field, $value)
    {
        $setter = 'set' . Lingua_Text::toClassName($field);
        $model = $this->createProfileSettings();
        $model->$setter($value);

        $this->assertFalse($model->isValid());

        $errors = $model->getValidationErrors();
        $this->assertArrayHasKey($field, $errors);
    }

    // getters and setters

    public function testGetNameEqualsNameSet()
    {
        $profileSettings = $this->createProfileSettings();
        $expected = 'Acme Widgets';
        $this->assertEquals($profileSettings, $profileSettings->setName($expected));
        $this->assertEquals($expected, $profileSettings->getName());
    }

    public function testGetAddressEqualsAddressSet()
    {
        $profileSettings = $this->createProfileSettings();
        $expected = 'Acme Widgets 123 Widget Street Acmeville, AC 12345 United States of America';
        $this->assertEquals($profileSettings, $profileSettings->setAddress($expected));
        $this->assertEquals($expected, $profileSettings->getAddress());
    }

    public function testGetAddressTypeEqualsAddressTypeSet()
    {
        $profileSettings = $this->createProfileSettings();
        $expected = 'Retail';
        $this->assertEquals($profileSettings, $profileSettings->setAddressType($expected));
        $this->assertEquals($expected, $profileSettings->getAddressType());
    }

    public function testGetEmailAddressEqualsEmailAddressSet()
    {
        $profileSettings = $this->createProfileSettings();
        $expected = 'contact@acmewidgets.com';
        $this->assertEquals($profileSettings, $profileSettings->setEmailAddress($expected));
        $this->assertEquals($expected, $profileSettings->getEmailAddress());
    }

    public function testGetPhoneNumberEqualsPhoneNumberSet()
    {
        $profileSettings = $this->createProfileSettings();
        $expected = '754-3010';
        $this->assertEquals($profileSettings, $profileSettings->setPhoneNumber($expected));
        $this->assertEquals($expected, $profileSettings->getPhoneNumber());
    }

    // validations

    public function testValidEmailAddressIsValid()
    {
        $this->assertValidFieldIsValid('email_address', 'contact@acmewidgets.com');
    }

    public function testInvalidEmailAddressIsInvalid()
    {
        $this->assertInvalidFieldIsInvalid('email_address', 'contact');
        $this->assertInvalidFieldIsInvalid('email_address', '');
        $this->assertInvalidFieldIsInvalid('email_address', null);
    }

    public function testValidNameIsValid()
    {
        $this->assertValidFieldIsValid('name', 'Acme Widgets');
    }

    public function testInvalidNameIsInvalid()
    {
        $this->assertInvalidFieldIsInvalid('name', 999);
        $this->assertInvalidFieldIsInvalid('name', '');
        $this->assertInvalidFieldIsInvalid('name', null);
    }

    public function testValidPhoneNumberIsValid()
    {
        $this->assertValidFieldIsValid('phone_number', '754-3010');
        $this->assertValidFieldIsValid('phone_number', '');
        $this->assertValidFieldIsValid('phone_number', null);
    }

    public function testInvalidPhoneNumberIsInvalid()
    {
        $this->assertInvalidFieldIsInvalid('phone_number', 999);
    }

    public function testValidAddressIsValid()
    {
        $this->assertValidFieldIsValid('address',
            'Acme Widgets 123 Widget Street Acmeville, AC 12345 United States of America');
    }

    public function testInvalidAddressIsInvalid()
    {
        $this->assertInvalidFieldIsInvalid('address', 999);
        $this->assertInvalidFieldIsInvalid('address', '');
        $this->assertInvalidFieldIsInvalid('address', null);
    }

    /**
     * @dataProvider validAddressTypeChoices
     */
    public function testValidAddressTypeIsValid($addressType)
    {
        $this->assertValidFieldIsValid('address_type', $addressType);
    }

    /**
     * @dataProvider invalidChoices
     */
    public function testInvalidAddressTypeIsInvalid($addressType)
    {
        $this->assertInvalidFieldIsInvalid('address_type', $addressType);
    }

    public function invalidChoices()
    {
        return array(
            array('foo'),
            array(''),
            array(null),
        );
    }

    public function validAddressTypeChoices()
    {
        return array(
            array(ProfileSettings::HOME_OFFICE),
            array(ProfileSettings::COMMERCIAL_OFFICE),
            array(ProfileSettings::RETAIL),
            array(ProfileSettings::WAREHOUSE),
        );
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
