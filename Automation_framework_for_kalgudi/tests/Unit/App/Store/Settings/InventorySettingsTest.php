<?php

namespace Unit\App\Store\Settings;

use Store\Settings\InventorySettings;
use PHPUnit_Framework_TestCase;
use Store_Settings;
use Store_Config;
use Store_Settings_Driver_Dummy;

class TestableInventorySettings extends InventorySettings
{
    private $fieldToTest;

    public function __construct($fieldToTest)
    {
        $fieldToTest = is_array($fieldToTest) ? $fieldToTest : array($fieldToTest);


        $this->fieldToTest = array_combine($fieldToTest, $fieldToTest);

        $settings = new Store_Settings();
        $settings->setDriver(new Store_Settings_Driver_Dummy());
        $settings->load();

        parent::__construct(array(), $settings);
    }

    public function getData()
    {
        return array_intersect_key(parent::getData(), $this->fieldToTest);
    }

    public function getValidationRules()
    {
        return array_intersect_key(parent::getValidationRules(), $this->fieldToTest);
    }
}

class InventorySettingsTest extends PHPUnit_Framework_TestCase
{
    // setters and getters


    public function testSetGetProductOutOfStockBehavior()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setProductOutOfStockBehavior(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE));
        $this->assertEquals(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE, $model->getProductOutOfStockBehavior());
    }

    public function testSetGetOptionOutOfStockBehavior()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_HIDE));
        $this->assertEquals(InventorySettings::OPTION_OUT_OF_STOCK_HIDE, $model->getOptionOutOfStockBehavior());
    }

    public function testSetGetUpdateStockBehavior()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setUpdateStockBehavior(InventorySettings::UPDATE_STOCK_ORDER_COMPLETED_OR_SHIPPED));
        $this->assertEquals(InventorySettings::UPDATE_STOCK_ORDER_COMPLETED_OR_SHIPPED, $model->getUpdateStockBehavior());
    }

    public function testSetGetEditOrderStockAdjustment()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setEditOrderStockAdjustment(true));
        $this->assertTrue($model->getEditOrderStockAdjustment());
    }

    public function testSetGetDeleteOrderStockAdjustment()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setDeleteOrderStockAdjustment(true));
        $this->assertTrue($model->getDeleteOrderStockAdjustment());
    }

    public function testSetGetRefundOrderStockAdjustment()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setRefundOrderStockAdjustment(true));
        $this->assertTrue($model->getRefundOrderStockAdjustment());
    }

    public function testSetGetShowPreOrderStockLevel()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setShowPreOrderStockLevels(true));
        $this->assertTrue($model->getShowPreOrderStockLevels());
    }

    public function testSetGetDefaultOutOfStockMessage()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setDefaultOutOfStockMessage('foo bar'));
        $this->assertEquals('foo bar', $model->getDefaultOutOfStockMessage());
    }

    public function testSetGetShowOutOfStockMessage()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setShowOutOfStockMessage(true));
        $this->assertTrue($model->getShowOutOfStockMessage());
    }

    public function testSetGetLowStockNotificationAddress()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setLowStockNotificationAddress('low@foobar.com'));
        $this->assertEquals('low@foobar.com', $model->getLowStockNotificationAddress());
    }

    public function testSetGetOutOfStockNotificationAddress()
    {
        $model = new InventorySettings();
        $this->assertEquals($model, $model->setOutOfStockNotificationAddress('out@foobar.com'));
        $this->assertEquals('out@foobar.com', $model->getOutOfStockNotificationAddress());
    }

    // validations

    private function assertValidFieldIsValid($field, $value)
    {
        $model = new TestableInventorySettings($field);
        $model->setData(array(
            $field => $value,
        ));

        $this->assertTrue($model->isValid());
        $this->assertEmpty($model->getValidationErrors());
    }

    private function assertInvalidFieldIsInvalid($field, $value)
    {
        $model = new TestableInventorySettings($field);
        $model->setData(array(
            $field => $value,
        ));

        $this->assertFalse($model->isValid());

        $errors = $model->getValidationErrors();
        $this->assertArrayHasKey($field, $errors);
    }

    public function invalidChoices()
    {
        return array(
            array('foo'),
            array(''),
            array(null),
        );
    }

    public function validProductOutOfStockBehaviorChoices()
    {
        return array(
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE_AND_ACCESSIBLE),
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE),
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_REDIRECT_TO_CATEGORY),
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING),
        );
    }

    public function validOptionOutOfStockBehaviorChoices()
    {
        return array(
            array(InventorySettings::OPTION_OUT_OF_STOCK_HIDE),
            array(InventorySettings::OPTION_OUT_OF_STOCK_LABEL),
            array(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING),
        );
    }

    public function validUpdateStockBehaviorChoices()
    {
        return array(
            array(InventorySettings::UPDATE_STOCK_ORDER_PLACED),
            array(InventorySettings::UPDATE_STOCK_ORDER_COMPLETED_OR_SHIPPED),
        );
    }

    public function validStockLevelDisplayChoices()
    {
        return array(
            array(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW),
            array(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW_WHEN_LOW),
            array(InventorySettings::STOCK_LEVEL_DISPLAY_DONT_SHOW),
        );
    }

    /**
     * @dataProvider validProductOutOfStockBehaviorChoices
     */
    public function testValidateValidProductOutOfStockBehaviors($behavior)
    {
        $this->assertValidFieldIsValid('product_out_of_stock_behavior', $behavior);
    }

    /**
     * @dataProvider invalidChoices
     */
    public function testValidateInvalidProductOutOfStockBehaviors($behavior)
    {
        $this->assertInvalidFieldIsInvalid('product_out_of_stock_behavior', $behavior);
    }

    /**
     * @dataProvider validOptionOutOfStockBehaviorChoices
     */
    public function testValidateValidOptionOutOfStockBehaviors($behavior)
    {
        $this->assertValidFieldIsValid('option_out_of_stock_behavior', $behavior);
    }

    /**
     * @dataProvider invalidChoices
     */
    public function testValidateInvalidOptionOutOfStockBehaviors($behavior)
    {
        $this->assertInvalidFieldIsInvalid('option_out_of_stock_behavior', $behavior);
    }

    /**
     * @dataProvider validUpdateStockBehaviorChoices
     */
    public function testValidateValidUpdateStockBehaviors($behavior)
    {
        $this->assertValidFieldIsValid('update_stock_behavior', $behavior);
    }

    /**
     * @dataProvider invalidChoices
     */
    public function testValidateInvalidUpdateStockBehaviors($behavior)
    {
        $this->assertInvalidFieldIsInvalid('update_stock_behavior', $behavior);
    }

    /**
     * @dataProvider validStockLevelDisplayChoices
     */
    public function testValidateValidStockLevelDisplays($display)
    {
        $this->assertValidFieldIsValid('stock_level_display', $display);
    }

    /**
     * @dataProvider invalidChoices
     */
    public function testValidateInvalidStockLevelDisplays($display)
    {
        $this->assertInvalidFieldIsInvalid('stock_level_display', $display);
    }

    private function assertBooleanField($field)
    {
        $this->assertValidFieldIsValid($field, true);
        $this->assertValidFieldIsValid($field, false);

        $this->assertInvalidFieldIsInvalid($field, 'true');
        $this->assertInvalidFieldIsInvalid($field, 1);
        $this->assertInvalidFieldIsInvalid($field, '');
        $this->assertInvalidFieldIsInvalid($field, null);
    }

    public function testValidateEditOrderStockAdjustment()
    {
        $this->assertBooleanField('edit_order_stock_adjustment');
    }

    public function testValidateDeleteOrderStockAdjustment()
    {
        $this->assertBooleanField('delete_order_stock_adjustment');
    }

    public function testValidateRefundOrderStockAdjustment()
    {
        $this->assertBooleanField('refund_order_stock_adjustment');
    }

    public function testValidateShowPreOrderStockLevels()
    {
        $this->assertBooleanField('show_pre_order_stock_levels');
    }

    public function testValidateShowOutOfStockMessage()
    {
        $this->assertBooleanField('show_out_of_stock_message');
    }

    public function testValidateDefaultOutOfStockMessage()
    {
        $this->assertValidFieldIsValid('default_out_of_stock_message', 'foobar');
        $this->assertValidFieldIsValid('default_out_of_stock_message', '');
        $this->assertValidFieldIsValid('default_out_of_stock_message', null);

        $this->assertInvalidFieldIsInvalid('default_out_of_stock_message', 5);
    }

    public function testValidateLowStockNotificationAddress()
    {
        $this->assertValidFieldIsValid('low_stock_notification_email_address', 'foo@bar.com');
        $this->assertValidFieldIsValid('low_stock_notification_email_address', null);
        $this->assertValidFieldIsValid('low_stock_notification_email_address', '');

        $this->assertInvalidFieldIsInvalid('low_stock_notification_email_address', 'foo');
    }

    public function testValidateOutOfStockNotificationAddress()
    {
        $this->assertValidFieldIsValid('out_of_stock_notification_email_address', 'foo@bar.com');
        $this->assertValidFieldIsValid('out_of_stock_notification_email_address', null);
        $this->assertValidFieldIsValid('out_of_stock_notification_email_address', '');

        $this->assertInvalidFieldIsInvalid('out_of_stock_notification_email_address', 'foo');
    }

    public function testEmailFieldsAreSetToNullIfEmptyOnSave()
    {
        $model = new TestableInventorySettings(array('low_stock_notification_email_address', 'out_of_stock_notification_email_address'));
        $model->setLowStockNotificationAddress('');
        $model->setOutOfStockNotificationAddress('');

        $model->save();

        $this->assertNull($model->getLowStockNotificationAddress());
        $this->assertNull($model->getOutOfStockNotificationAddress());
    }

    public function testIsNotUsingOptionOutOfStockBehavior()
    {
        $model = new InventorySettings();
        $model->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING);
        $this->assertFalse($model->isUsingOptionOutOfStockBehavior());
    }

    public function testIsUsingOptionOutOfStockHideBehavior()
    {
        $model = new InventorySettings();
        $model->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_HIDE);
        $this->assertTrue($model->isUsingOptionOutOfStockBehavior());
    }

    public function testIsUsingOptionOutOfStockLabelBehavior()
    {
        $model = new InventorySettings();
        $model->setOptionOutOfStockBehavior(InventorySettings::OPTION_OUT_OF_STOCK_LABEL);
        $this->assertTrue($model->isUsingOptionOutOfStockBehavior());
    }

    public function testHideProductIfOutOfStockIsFalseIfInventorySettingsDisabled()
    {
        $backupSetting = \Store_Feature::isEnabled('InventorySettings');
        \Store_Feature::override('InventorySettings', false);

        $model = new InventorySettings();
        $this->assertFalse($model->hideProductIfOutOfStock());

        \Store_Feature::override('InventorySettings', $backupSetting);
    }


    public function testHideProductIfOutOfStockIsFalseForOutOfStockDoNothingBehavior()
    {
        $backupSetting = \Store_Feature::isEnabled('InventorySettings');
        \Store_Feature::override('InventorySettings', true);

        $model = new InventorySettings();
        $model->setProductOutOfStockBehavior(InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
        $this->assertFalse($model->hideProductIfOutOfStock());

        \Store_Feature::override('InventorySettings', $backupSetting);
    }

    public function outOfStockBehaviorsDataProvider()
    {
        return array(
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE_AND_ACCESSIBLE),
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE),
            array(InventorySettings::PRODUCT_OUT_OF_STOCK_REDIRECT_TO_CATEGORY),
        );
    }

    /**
     * @dataProvider outOfStockBehaviorsDataProvider
     */
    public function testHideProductIfOutOfStockIsFalseForAllOtherOutOfStockBehaviors($behavior)
    {
        $backupSetting = \Store_Feature::isEnabled('InventorySettings');
        \Store_Feature::override('InventorySettings', true);

        $model = new InventorySettings();
        $model->setProductOutOfStockBehavior($behavior);
        $this->assertTrue($model->hideProductIfOutOfStock());

        \Store_Feature::override('InventorySettings', $backupSetting);
    }
}
