<?php

class Unit_ProductImages_Resizing extends Interspire_UnitTest
{
	/**
	 * Regression test for ISC-3806
	 *
	 * @return void
	 */
	public function testCreateResizedFileCorrectChmod ()
	{
		if (stripos(PHP_OS, 'win') !== false) {
			$this->markTestSkipped("unable to test chmod on windows");
			return;
		}

		$original = dirname(__FILE__) . '/1x1.gif';
		$source = dirname(__FILE__) . '/1x1_resize_source.gif';
		$destination = dirname(__FILE__) . '/1x1_resize_destination.gif';
		$width = 100;
		$height = 100;

		if (file_exists($source)) {
			$this->assertTrue(unlink($source), "failed to unlink source $source");
		}

		$this->assertTrue(copy($original, $source), "failed to copy original $original to source $source");
		$this->assertTrue(isc_chmod($source, '644'), "failed to chmod source $source");

		$permissions_source = fileperms($source);

		if (file_exists($destination)) {
			$this->assertTrue(unlink($destination), "failed to unlink destination $destination");
		}

		$permissions_expected = ISC_WRITEABLE_FILE_PERM;
		$image = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance($source);
		ISC_PRODUCT_IMAGE::createResizedFile($image, $width, $height, $destination, null, $permissions_expected);

		$this->assertFileExists($destination, "failed to create destination $destination file");

		$permissions_destination = fileperms($destination);
		unlink($destination);

		clearstatcache();
		$permissions_source_afterResize = fileperms($source);
		unlink($source);

		// check that the source was NOT modified
		$this->assertEquals($permissions_source, $permissions_source_afterResize, "permission mismatch on source $source");

		// check that the destination WAS modified
		$this->assertEquals($permissions_expected, $permissions_destination & $permissions_expected, "permission mismatch on destination $destination");
	}

	/**
	 * Basic smoke test of resizeScratch
	 *
	 * @throws Exception
	 */
	public function testResizeScratchSmoke()
	{
		// can't help using local files as the GD extension does not support PHP file stream abstraction
		$original = __DIR__ . '/Nasa_blue_marble.jpg.blah';
		$destination = __DIR__ . '/resize_canvas_test.jpg';

		if (file_exists($destination)) {
			if (!unlink($destination)) {
				throw new Exception("Destination file already exists at $destination but I can't remote it.");
			}
		}

		$image = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance($original);
		$image->loadImageFileToScratch();
		$image->resizeScratch(120, 100);
		$image->saveScratchToFile($destination, new ISC_IMAGE_WRITEOPTIONS_JPEG());

		$this->assertSame(120, $image->getWidth());
		$this->assertSame(100, $image->getHeight());

		// the gd lib is coupled to isc_chmod directly which doesn't work right in isolation, reset perms now
		chmod($destination, 0644);
		$result = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance($destination);
		$this->assertSame(120, $result->getWidth());
		$this->assertSame(100, $result->getHeight());

		unlink($destination);
	}
}
