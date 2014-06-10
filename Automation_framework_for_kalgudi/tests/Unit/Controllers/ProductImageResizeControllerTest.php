<?php

// note: though this namespace is included here, phpunit getMockClass calls need the fully-qualified class names
use Store\Controllers;

require_once __DIR__ . '/../../../config/init/autoloader.php';

class Unit_Controllers_ProductImageResizeControllerTest extends PHPUnit_Framework_TestCase
{
	public function testInvalidLengthIsNotPermitted()
	{
		if (!method_exists($this, 'getMockClass')) {
			$this->markTestSkipped('Test not supported in this PHPUnit version.');
		}

		$controller = $this->getMockClass(
			'Store\Controllers\ProductImageResizeController',
			array('getPermittedLengths')
		);

		$controller::staticExpects($this->any())
			->method('getPermittedLengths')
			->will($this->returnValue(array(
				100,
				200,
				300,
			)));

		$this->assertFalse($controller::isLengthPermitted(0));
	}

	public function testValidLengthIsPermitted()
	{
		if (!method_exists($this, 'getMockClass')) {
			$this->markTestSkipped('Test not supported in this PHPUnit version.');
		}

		$controller = $this->getMockClass(
			'Store\Controllers\ProductImageResizeController',
			array('getPermittedLengths')
		);

		$controller::staticExpects($this->any())
			->method('getPermittedLengths')
			->will($this->returnValue(array(
				100,
				200,
				300,
			)));

		$this->assertTrue($controller::isLengthPermitted(200));
	}

	public function testTransparentBackgroundIsValid()
	{
		$this->assertTrue(Controllers\ProductImageResizeController::isBackgroundValid('transparent'));
	}

	public function testRgbBackgroundIsValid()
	{
		$this->assertTrue(Controllers\ProductImageResizeController::isBackgroundValid('ABC123'));
	}

	public function testInvalidBackground()
	{
		$this->assertFalse(Controllers\ProductImageResizeController::isBackgroundValid(''));
	}
}
