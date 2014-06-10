<?php
require_once(BUILD_ROOT."/lib/ImageDir.php");

class Admin_ImageDirTest extends PHPUnit_Framework_TestCase
{
	public function testImageDirSizeValid()
	{
		$dest = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/uploaded_images/');

		$file = new Interspire_File(dirname(__FILE__)."/images/valid.jpg");
		$file->copy($dest.'valid_new.jpg', true);

		$imageDir = new \ImageDir();
		$files = $imageDir->GetImageDirFiles();

		$this->assertEquals(1, count($files));
		$this->assertArrayHasKey('id', $files[0]);
		$this->assertArrayHasKey('url', $files[0]);
		$this->assertArrayHasKey('name', $files[0]);
		$this->assertArrayHasKey('modified', $files[0]);
		$this->assertGreaterThan(0, $files[0]['size']);

		unlink($dest.'valid_new.jpg');
	}

	public function testImageDirSizeInvalid()
	{
		$dest = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/uploaded_images/');

		$file = new Interspire_File(dirname(__FILE__)."/images/invalid.jpg");
		$file->copy($dest.'invalid_new.jpg', true);

		$imageDir = new \ImageDir();
		$files = $imageDir->GetImageDirFiles();

		$this->assertEquals(1, count($files));
		$this->assertArrayHasKey('id', $files[0]);
		$this->assertArrayHasKey('url', $files[0]);
		$this->assertArrayHasKey('name', $files[0]);
		$this->assertArrayHasKey('modified', $files[0]);
		$this->assertEquals(0, $files[0]['size']);

		unlink($dest.'invalid_new.jpg');
	}
}
