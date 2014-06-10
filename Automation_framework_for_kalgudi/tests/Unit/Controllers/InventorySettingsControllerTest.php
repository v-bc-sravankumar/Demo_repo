<?php
use Store\Controllers;

class Unit_Controllers_InventorySettingsControllerTest extends PHPUnit_Framework_TestCase
{
	public function testCheckPermissions()
	{
		$mockAuth = $this->getMock('ISC_ADMIN_AUTH', array('HasPermission'));
		$mockAuth->expects($this->any())
			->method('HasPermission')
			->will($this->returnValue(false));

		$controller = new \InventorySettingsController();
		$controller->setPermissionValidator($mockAuth);

		$refSettings = new ReflectionClass("InventorySettingsController");
		$method = $refSettings->getMethod("checkPermission");
		$method->setAccessible(true);

		$this->assertFalse($method->invoke($controller));
	}

	public function testCheckFeaturePermissions()
	{
		$mockAuth = $this->getMock('ISC_ADMIN_AUTH', array('HasPermission'));
		$mockAuth->expects($this->any())
			->method('HasPermission')
			->will($this->returnValue(true));

		$controller = new \InventorySettingsController();
		$controller->setPermissionValidator($mockAuth);

		$refSettings = new ReflectionClass("InventorySettingsController");
		$method = $refSettings->getMethod("checkPermission");
		$method->setAccessible(true);

		\Store_Feature::override('InventorySettings', false);
		$this->assertFalse($method->invoke($controller));

		\Store_Feature::override('InventorySettings', true);
		$this->assertTrue($method->invoke($controller));
	}
}
