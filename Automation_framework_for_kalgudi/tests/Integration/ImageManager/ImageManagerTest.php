<?php
require_once(BUILD_ROOT."/admin/includes/classes/class.remote.imagemanager.php");

class Admin_ImageManagerTest extends PHPUnit_Framework_TestCase
{

	public function testIsValidImageFile()
	{
		$GLOBALS['ISC_CLASS_ADMIN_ENGINE'] = getClass('ISC_ADMIN_ENGINE');

		$imageManager = new ReflectionClass("ISC_ADMIN_REMOTE_IMAGEMANAGER");

		$method = $imageManager->getMethod("IsValidImageFile");
		$method->setAccessible(true);

		$test = new ISC_ADMIN_REMOTE_IMAGEMANAGER(null);

		$filePath = dirname(__FILE__) . '/images/invalid.jpg';
		$this->assertFalse($method->invoke($test, $filePath));

		$filePath = dirname(__FILE__) . '/images/valid.jpg';
		$this->assertTrue($method->invoke($test, $filePath));
	}
}
