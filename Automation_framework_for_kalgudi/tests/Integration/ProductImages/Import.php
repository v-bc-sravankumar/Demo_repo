<?php

class Unit_ProductImages_Import extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testSortOrderIsSetCorrectlyWhenDeletingAProductImage ()
	{
		$image1 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTSORT', true, false, true);
		$image2 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTSORT', true, false, true);
		$image3 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTSORT', true, false, true);

		$this->assertEquals(0, $image1->getSort());
		$this->assertEquals(1, $image2->getSort());
		$this->assertEquals(2, $image3->getSort());

		$image2->delete();

		$image1 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image1->getProductImageId());
		$image3 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image3->getProductImageId());

		$this->assertEquals(0, $image1->getSort());
		$this->assertEquals(1, $image3->getSort());

		$image1->delete();

		$image3 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image3->getProductImageId());
		$this->assertEquals(0, $image3->getSort());

		$image3->delete();
	}

	public function testThumbnailSelectionForInProgressProduct ()
	{
		$image1 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTTHUMBNAIL', true, false, true);
		$image2 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTTHUMBNAIL', true, false, true);
		$image3 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', 'TESTTHUMBNAIL', true, false, true);

		$this->assertTrue($image1->getIsThumbnail());
		$this->assertFalse($image2->getIsThumbnail());
		$this->assertFalse($image3->getIsThumbnail());

		// set second image as thumbnail, check others are no longer thumbnail
		$image2->setIsThumbnail(true);
		$image2->saveToDatabase();

		$image1 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image1->getProductImageId());
		$image2 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image2->getProductImageId());
		$image3 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image3->getProductImageId());

		$this->assertFalse($image1->getIsThumbnail());
		$this->assertTrue($image2->getIsThumbnail());
		$this->assertFalse($image3->getIsThumbnail());

		// set third image as thumbnail, check others are no longer thumbnail
		$image3->setIsThumbnail(true);
		$image3->saveToDatabase();

		$image1 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image1->getProductImageId());
		$image2 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image2->getProductImageId());
		$image3 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image3->getProductImageId());

		$this->assertFalse($image1->getIsThumbnail());
		$this->assertFalse($image2->getIsThumbnail());
		$this->assertTrue($image3->getIsThumbnail());

		$newThumbnailId = 0;

		// delete third image, check first image is now thumbnail and not second
		$image3->delete(true, true, $newThumbnailId);
		$this->assertEquals($image1->getProductImageId(), $newThumbnailId);

		$image1 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image1->getProductImageId());
		$image2 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image2->getProductImageId());

		$this->assertTrue($image1->getIsThumbnail());
		$this->assertFalse($image2->getIsThumbnail());

		// delete first image and check second is thumbnail
		$image1->delete(true, true, $newThumbnailId);
		$this->assertEquals($image2->getProductImageId(), $newThumbnailId);

		$image2 = ISC_PRODUCT_IMAGE::getProductImageFromDatabase($image2->getProductImageId());

		$this->assertTrue($image2->getIsThumbnail());

		// clean up - delete second image
		$image2->delete(true, true, $newThumbnailId);
		$this->assertNull($newThumbnailId);
	}

	/**
	 * BIG-8124 - Ensure that only one image can be the thumbnail
	 */
	public function testThumbnailSelectionForNoProductIdOrHash()
	{
		// simulate import process by creating images not assigned to a product. this will prevent the images from being saved.
		$image1 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', false, false, false, false);
		$image2 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', false, false, false, false);
		$image3 = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/1x1.gif', '1x1.gif', false, false, false, false);

		// set each image to be the thumbnail
		$image1->setIsThumbnail(true);
		$image1->setProductHash('TESTPRODUCT');

		$image2->setIsThumbnail(true);
		$image2->setProductHash('TESTPRODUCT');

		$image3->setIsThumbnail(true);
		$image3->setProductHash('TESTPRODUCT');

		// now save all the images, causing inserts to occur
		$image1->saveToDatabase();
		$image2->saveToDatabase();
		$image3->saveToDatabase();

		// reload the images
		$image1->loadFromDatabase();
		$image2->loadFromDatabase();
		$image3->loadFromDatabase();

		// only image3 should be the thumbnail
		$this->assertFalse($image1->getIsThumbnail());
		$this->assertFalse($image2->getIsThumbnail());
		$this->assertTrue($image3->getIsThumbnail());

		$image1->delete(false);
		$image2->delete(false);
		$image3->delete(false);
	}

	public function testImportToExistingProductJpg1 ()
	{
		$productId = 1;
		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah', 'original.jpg.ext', $productId, false, false, true);
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');
		$this->assertEquals($productId, $image->getProductId(), 'Image was expected to be imported against product ' . $productId . ' but found to be imported to ' . $image->getProductId());
		$image->delete();
	}

	public function testImportToExistingProductJpg2 ()
	{
		$productId = 1;
		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/leaf.jpg', 'original.jpg', $productId, false, false, true);
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');
		$this->assertEquals($productId, $image->getProductId(), 'Image was expected to be imported against product ' . $productId . ' but found to be imported to ' . $image->getProductId());
		$image->delete();
	}

	public function testImportToExistingProductPng ()
	{
		$productId = 1;
		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/coat_of_arms.png', 'original.png', $productId, false, false, true);
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');
		$this->assertEquals($productId, $image->getProductId(), 'Image was expected to be imported against product ' . $productId . ' but found to be imported to ' . $image->getProductId());
		$image->delete();
	}

	public function testImportJpeg ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		// this test assumes that the php environment has safe mode disabled

		$now = time();

		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah', 'original.jpg.ext', 'TESTIMPORTJPEG', true, false, true);

		// test import went ok on the surface
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');

		// check database record
		$result = $this->fixtures->Query("SELECT * FROM `[|PREFIX|]product_images` WHERE imageid = " . $image->getProductImageId());
		$row = $this->fixtures->Fetch($result);
		if (!$row) {
			throw new Exception("Unable to verify image record in database.");
		}

		// check each known field
		$this->assertEquals('0', $row['imageprodid']);
		$this->assertEquals('TESTIMPORTJPEG', $row['imageprodhash']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.jpg__[0-9]{5}\\.jpg$#', $row['imagefile']);
		$this->assertEquals('1', $row['imageisthumb']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.jpg__[0-9]{5}_tiny\\.jpg$#', $row['imagefiletiny']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.jpg__[0-9]{5}_thumb\\.jpg$#', $row['imagefilethumb']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.jpg__[0-9]{5}_std\\.jpg$#', $row['imagefilestd']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.jpg__[0-9]{5}_zoom\\.jpg$#', $row['imagefilezoom']);
		$this->assertEquals('', $row['imagedesc']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertTrue((int)$row['imagedateadded'] >= $now, 'imagedateadded is not >= timestamp of import');
		$this->assertEquals('50x51', $row['imagefiletinysize']);
		$this->assertEquals('220x225', $row['imagefilethumbsize']);
		$this->assertEquals('280x287', $row['imagefilestdsize']);
		$this->assertEquals('1249x1280', $row['imagefilezoomsize']);

		// check files exist and are valid images
		$files = array(
			"source" => $image->getAbsoluteSourceFilePath(),
			"tiny" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_TINY),
			"thumb" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL),
			"std" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_STANDARD),
			"zoom" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_ZOOM),
		);

		foreach ($files as $type => $path) {
			$this->assertTrue(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" does not exist import.');
			$this->assertTrue(ISC_PRODUCT_IMAGE::isValidImageFile($path), 'Image file type "' . $type . '" at path "' . $path . '" is not a valid image.');
		}

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_TINY, true, false);
		$this->assertEquals(50, $dimensions[0], 'Tiny width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(51, $dimensions[1], 'Tiny height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL, true, false);
		$this->assertEquals(220, $dimensions[0], 'Thumbnail width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(225, $dimensions[1], 'Thumbnail height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_STANDARD, true, false);
		$this->assertEquals(280, $dimensions[0], 'Standard width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(287, $dimensions[1], 'Standard height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_ZOOM, true, false);
		$this->assertEquals(1249, $dimensions[0], 'Zoom width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(1280, $dimensions[1], 'Zoom height ' . $dimensions[1] . ' does not match expected result.');

		$image->delete();

		foreach ($files as $type => $path) {
			$this->assertFalse(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" incorrectly exists after delete() call.');
		}
	}

	public function testImportGif ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		// this test assumes that the php environment has safe mode disabled
		// should really be abstracted but... copy & paste is quicker for now :\

		$now = time();

		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/Nasa_blue_marble.gif.blah', 'original.gif.ext', 'TESTIMPORTGIF', true, false, true);

		// test import went ok on the surface
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');

		// check database record
		$result = $this->fixtures->Query("SELECT * FROM `[|PREFIX|]product_images` WHERE imageid = " . $image->getProductImageId());
		$row = $this->fixtures->Fetch($result);
		if (!$row) {
			throw new Exception("Unable to verify image record in database.");
		}

		// check each known field
		$this->assertEquals('0', $row['imageprodid']);
		$this->assertEquals('TESTIMPORTGIF', $row['imageprodhash']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.gif__[0-9]{5}\\.gif$#', $row['imagefile']);
		$this->assertEquals('1', $row['imageisthumb']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.gif__[0-9]{5}_tiny\\.gif$#', $row['imagefiletiny']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.gif__[0-9]{5}_thumb\\.gif$#', $row['imagefilethumb']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.gif__[0-9]{5}_std\\.gif$#', $row['imagefilestd']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.gif__[0-9]{5}_zoom\\.gif$#', $row['imagefilezoom']);
		$this->assertEquals('', $row['imagedesc']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertTrue((int)$row['imagedateadded'] >= $now, 'imagedateadded is not >= timestamp of import');
		$this->assertEquals('50x51', $row['imagefiletinysize']);
		$this->assertEquals('220x225', $row['imagefilethumbsize']);
		$this->assertEquals('280x287', $row['imagefilestdsize']);
		$this->assertEquals('1249x1280', $row['imagefilezoomsize']);

		// check files exist and are valid images
		$files = array(
			"source" => $image->getAbsoluteSourceFilePath(),
			"tiny" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_TINY),
			"thumb" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL),
			"std" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_STANDARD),
			"zoom" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_ZOOM),
		);

		foreach ($files as $type => $path) {
			$this->assertTrue(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" does not exist import.');
			$this->assertTrue(ISC_PRODUCT_IMAGE::isValidImageFile($path), 'Image file type "' . $type . '" at path "' . $path . '" is not a valid image.');
		}

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_TINY, true, false);
		$this->assertEquals(50, $dimensions[0], 'Tiny width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(51, $dimensions[1], 'Tiny height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL, true, false);
		$this->assertEquals(220, $dimensions[0], 'Thumbnail width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(225, $dimensions[1], 'Thumbnail height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_STANDARD, true, false);
		$this->assertEquals(280, $dimensions[0], 'Standard width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(287, $dimensions[1], 'Standard height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_ZOOM, true, false);
		$this->assertEquals(1249, $dimensions[0], 'Zoom width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(1280, $dimensions[1], 'Zoom height ' . $dimensions[1] . ' does not match expected result.');

		$image->delete();

		foreach ($files as $type => $path) {
			$this->assertFalse(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" incorrectly exists after delete() call.');
		}
	}

	public function testImportPng ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		// this test assumes that the php environment has safe mode disabled
		// should really be abstracted but... copy & paste is quicker for now :\

		$now = time();

		$image = ISC_PRODUCT_IMAGE::importImage(dirname(__FILE__) . '/Nasa_blue_marble.png.blah', 'original.png.ext', 'TESTIMPORTPNG', true, false, true);

		// test import went ok on the surface
		$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Result of importImage was not of type ISC_PRODUCT_IMAGE');

		// check database record
		$result = $this->fixtures->Query("SELECT * FROM `[|PREFIX|]product_images` WHERE imageid = " . $image->getProductImageId());
		$row = $this->fixtures->Fetch($result);
		if (!$row) {
			throw new Exception("Unable to verify image record in database.");
		}

		// check each known field
		$this->assertEquals('0', $row['imageprodid']);
		$this->assertEquals('TESTIMPORTPNG', $row['imageprodhash']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.png__[0-9]{5}\\.png$#', $row['imagefile']);
		$this->assertEquals('1', $row['imageisthumb']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.png__[0-9]{5}_tiny\\.png$#', $row['imagefiletiny']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.png__[0-9]{5}_thumb\\.png$#', $row['imagefilethumb']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.png__[0-9]{5}_std\\.png$#', $row['imagefilestd']);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/original\\.png__[0-9]{5}_zoom\\.png$#', $row['imagefilezoom']);
		$this->assertEquals('', $row['imagedesc']);
		$this->assertEquals('0', $row['imagesort']);
		$this->assertTrue((int)$row['imagedateadded'] >= $now, 'imagedateadded is not >= timestamp of import');
		$this->assertEquals('50x51', $row['imagefiletinysize']);
		$this->assertEquals('220x225', $row['imagefilethumbsize']);
		$this->assertEquals('280x287', $row['imagefilestdsize']);
		$this->assertEquals('1249x1280', $row['imagefilezoomsize']);

		// check files exist and are valid images
		$files = array(
			"source" => $image->getAbsoluteSourceFilePath(),
			"tiny" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_TINY),
			"thumb" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL),
			"std" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_STANDARD),
			"zoom" => $image->getAbsoluteResizedFilePath(ISC_PRODUCT_IMAGE_SIZE_ZOOM),
		);

		foreach ($files as $type => $path) {
			$this->assertTrue(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" does not exist import.');
			$this->assertTrue(ISC_PRODUCT_IMAGE::isValidImageFile($path), 'Image file type "' . $type . '" at path "' . $path . '" is not a valid image.');
		}

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_TINY, true, false);
		$this->assertEquals(50, $dimensions[0], 'Tiny width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(51, $dimensions[1], 'Tiny height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_THUMBNAIL, true, false);
		$this->assertEquals(220, $dimensions[0], 'Thumbnail width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(225, $dimensions[1], 'Thumbnail height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_STANDARD, true, false);
		$this->assertEquals(280, $dimensions[0], 'Standard width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(287, $dimensions[1], 'Standard height ' . $dimensions[1] . ' does not match expected result.');

		$dimensions = $image->getResizedFileDimensions(ISC_PRODUCT_IMAGE_SIZE_ZOOM, true, false);
		$this->assertEquals(1249, $dimensions[0], 'Zoom width ' . $dimensions[0] . ' does not match expected result.');
		$this->assertEquals(1280, $dimensions[1], 'Zoom height ' . $dimensions[1] . ' does not match expected result.');

		$image->delete();

		foreach ($files as $type => $path) {
			$this->assertFalse(file_exists($path), 'Image file type "' . $type . '" at path "' . $path . '" incorrectly exists after delete() call.');
		}
	}
}
