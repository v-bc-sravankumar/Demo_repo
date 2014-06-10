<?php

namespace Integration\DomainModel\Repository;

use Repository\InventorySettings;

class InventorySettingsTest extends \PHPUnit_Framework_TestCase
{
	public function testDefaultSettings()
	{
		//Existing settings
		$this->assertEquals(true, \Store_Config::getDefault('UpdateInventoryOnOrderEdit'));
		$this->assertEquals(false, \Store_Config::getDefault('UpdateInventoryOnOrderDelete'));
		$this->assertEquals(false, \Store_Config::getDefault('UpdateInventoryOnOrderRefund'));
		$this->assertEquals('', \Store_Config::getDefault('LowInventoryNotificationAddress'));

		//Migrated settings
		//UpdateInventoryLevels
		$this->assertEquals(\Store\Settings\InventorySettings::UPDATE_STOCK_ORDER_PLACED, \Store_Config::getDefault('UpdateStockBehavior'));
		$this->assertEquals(false, \Store_Config::getDefault('ShowPreOrderInventory'));

		//New settings
		$this->assertEquals(\Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING, \Store_Config::getDefault('ProductOutOfStockBehavior'));
		$this->assertEquals(\Store\Settings\InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING, \Store_Config::getDefault('OptionOutOfStockBehavior'));
		$this->assertEquals('Out of stock', \Store_Config::getDefault('DefaultOutOfStockMessage'));
		$this->assertEquals(false, \Store_Config::getDefault('ShowOutOfStockMessage'));
		$this->assertEquals('', \Store_Config::getDefault('OutOfStockNotificationAddress'));
		$this->assertEquals('dont_show', \Store_Config::getDefault('StockLevelDisplay'));
		$this->assertEquals('order_placed', \Store_Config::getDefault('UpdateStockBehavior'));
	}

	public function testGetSettings()
	{
		$repository = new InventorySettings();
		$settings = $repository->getSettings();

		$this->assertArrayHasKey('settings', $settings);
		$this->assertEquals(12, count($settings['settings']));
	}

	public function testInvalidEmailSettings()
	{
		$repository = new InventorySettings();
		$existingSettings = $repository->getSettings();

		$data['low_stock_notification_email_address'] = 'notcorrect';
		$data['out_of_stock_notification_email_address'] = 'notcorrect';

		$settings = $repository->saveSettings($data);

		$this->assertArrayHasKey('errors', $settings);
		$this->assertArrayHasKey('low_stock_notification_email_address', $settings['errors']);
		$this->assertArrayHasKey('out_of_stock_notification_email_address', $settings['errors']);

		//cleanup
		$settings = $repository->saveSettings($existingSettings['settings']);
	}

	public function testInvalidSettings()
	{
		$repository = new InventorySettings();
		$existingSettings = $repository->getSettings();

		$configMap = $this->getConfigMap();

		$data = array();
		foreach ($configMap as $key => $config) {
			$data[$key] = 999;
		}

		$settings = $repository->saveSettings($data);

		$this->assertArrayHasKey('errors', $settings);

		$this->assertArrayNotHasKey('edit_order_stock_adjustment', $settings['errors']);
		$this->assertArrayNotHasKey('delete_order_stock_adjustment', $settings['errors']);
		$this->assertArrayNotHasKey('refund_order_stock_adjustment', $settings['errors']);
		$this->assertArrayNotHasKey('show_pre_order_stock_levels', $settings['errors']);
		$this->assertArrayNotHasKey('show_out_of_stock_message', $settings['errors']);

		$this->assertArrayHasKey('default_out_of_stock_message', $settings['errors']);
		$this->assertArrayHasKey('update_stock_behavior', $settings['errors']);
		$this->assertArrayHasKey('stock_level_display', $settings['errors']);
		$this->assertArrayHasKey('product_out_of_stock_behavior', $settings['errors']);
		$this->assertArrayHasKey('option_out_of_stock_behavior', $settings['errors']);
		$this->assertArrayHasKey('low_stock_notification_email_address', $settings['errors']);
		$this->assertArrayHasKey('out_of_stock_notification_email_address', $settings['errors']);

		//cleanup
		$settings = $repository->saveSettings($existingSettings['settings']);
	}

	public function testValidSettings()
	{
		$repository = new InventorySettings();
		$existingSettings = $repository->getSettings();

		$data = $this->getConfigMap();

		$settings = $repository->saveSettings($data);
		$this->assertFalse(array_key_exists('errors', $settings));

		$savedSettings = $repository->getSettings();

		foreach ($data as $key => $value) {
			if (($key == 'edit_order_stock_adjustment') ||
					($key == 'delete_order_stock_adjustment') ||
					($key == 'refund_order_stock_adjustment') ||
					($key == 'show_pre_order_stock_levels') ||
					($key == 'show_out_of_stock_message') ) {
				$this->assertTrue($savedSettings['settings'][$key], $key . ' did not save');
			}
			else {
				$this->assertEquals($value, $savedSettings['settings'][$key], $key . ' did not save');
			}
		}

		//cleanup
		$settings = $repository->saveSettings($existingSettings['settings']);
	}

	public function testValidEmailSettings()
	{
		$repository = new InventorySettings();
		$existingSettings = $repository->getSettings();

		$data['low_stock_notification_email_address'] = 'jane@example.com';
		$data['out_of_stock_notification_email_address'] = 'joe@example.com';

		$settings = $repository->saveSettings($data);

		$this->assertFalse(array_key_exists('errors', $settings));

		$savedSettings = $repository->getSettings();

		$this->assertEquals('jane@example.com', $savedSettings['settings']['low_stock_notification_email_address']);
		$this->assertEquals('joe@example.com', $savedSettings['settings']['out_of_stock_notification_email_address']);

		//cleanup
		$settings = $repository->saveSettings($existingSettings['settings']);
	}

	public function testHideProductIfOutOfStock()
	{
		//Feature disabled
		\Store_Feature::override('InventorySettings', false);
		$this->assertFalse(InventorySettings::hideProductIfOutOfStock());

		//Feature enabled
		\Store_Feature::override('InventorySettings', true);
		\Store_Config::override('ProductOutOfStockBehavior', \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING);
		$this->assertFalse(InventorySettings::hideProductIfOutOfStock());

		//all other options need to hide the product
		\Store_Config::override('ProductOutOfStockBehavior', 'anything');
		$this->assertTrue(InventorySettings::hideProductIfOutOfStock());
	}

	private function getConfigMap()
	{
		return array (
			'update_stock_behavior' => \Store\Settings\InventorySettings::UPDATE_STOCK_ORDER_PLACED,
			'edit_order_stock_adjustment' => 'true',
			'delete_order_stock_adjustment' => 'true',
			'refund_order_stock_adjustment' => 'true',
			'stock_level_display' => \Store\Settings\InventorySettings::STOCK_LEVEL_DISPLAY_SHOW,
			'show_pre_order_stock_levels' => 'true',
			'product_out_of_stock_behavior' => \Store\Settings\InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING,
			'option_out_of_stock_behavior' => \Store\Settings\InventorySettings::OPTION_OUT_OF_STOCK_HIDE,
			'default_out_of_stock_message' => 'New Out of stock message',
			'show_out_of_stock_message' => 'true',
			'low_stock_notification_email_address' => 'foo@bar.com',
			'out_of_stock_notification_email_address' =>  'baz@bar.com',
		);
	}
}
