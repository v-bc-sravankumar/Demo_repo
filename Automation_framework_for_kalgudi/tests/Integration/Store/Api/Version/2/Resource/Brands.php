<?php

/**
* these tests can't currently use fixtures because brands data exists in sample data and would be relied upon by
* product-based tests until fixtures can be properly introduced
*
* when fixtures are utilised properly change the tests here so they don't have to manually insert / remove dummy brands
*/

class Unit_Lib_Store_Api_Version_2_Resource_Brands extends Interspire_IntegrationTest
{
	private function _createImportableImage ($filename = 'BrandsImage.jpg')
	{
		// put the image we want to test with into product_images/import
		$source = dirname(__FILE__) . '/' . $filename;
		$import = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/import');
		if (!file_exists($import)) {
			$this->assertTrue(isc_mkdir($import), 'isc_mkdir failed');
		}
		// ISC-5059 tempnam does not like asset paths, replace with uniqid
		$import .= ('/' . uniqid('api'));
		$this->assertTrue(copy($source, $import), 'copy failed');

		return $import;
	}

	private function _getBrandsResource ()
	{
		return new Store_Api_Version_2_Resource_Brands();
	}

	private function _generateName ()
	{
		return 'BRAND_' . mt_rand(0, PHP_INT_MAX);
	}

	private function _createDummyBrand ($brandName = null)
	{
		$brandsResource = $this->_getBrandsResource();

		if ($brandName === null) {
			$brandName = $this->_generateName();
		}

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
</brand>';

		$postRequest = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		$postResult = $brandsResource->postAction($postRequest)->getData(true);

		$this->assertSame(201, $postRequest->getResponse()->getStatus());

		return (int)$postResult['id'];
	}

	private function _deleteBrand ($brandId)
	{
		$brandsResource = $this->_getBrandsResource();

		$deleteRequest = new Interspire_Request();
		$deleteRequest->setUserParam('brands', $brandId);

		$deleteResult = $brandsResource->deleteAction($deleteRequest);

		// this null test is intended (it signals the API to eventually issue a 204 No Content)
		$this->assertNull($deleteResult);

		return true;
	}

	public function testSmoke ()
	{
		// this is a large, procedural test of a basic POST -> GET -> PUT -> DELETE which is only necessary because we
		// can't currently use fixtures for the brands table

		$brandsResource = $this->_getBrandsResource();

		$countResource = new Store_Api_Version_2_Resource_Brands_Count();

		$countRequest = new Interspire_Request();

		$countResult = $countResource->getAction($countRequest)->getData(true);
		$this->assertSame(200, $countRequest->getResponse()->getStatus());
		$this->assertInternalType('int', $countResult['count']);
		$firstCount = $countResult['count'];

		// post a new item so we can check the new count

		$brandName = $this->_generateName();
		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
</brand>';

		$postRequest = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		$postResult = $brandsResource->postAction($postRequest)->getData(true);

		$this->assertSame(201, $postRequest->getResponse()->getStatus());
		$createdId = $postResult['id'];

		// verify the new count is $firstCount + 1

		$countResult = $countResource->getAction($countRequest)->getData(true);
		$this->assertSame(200, $countRequest->getResponse()->getStatus());
		$this->assertSame($firstCount + 1, $countResult['count']);

		// attempt to get the item

		$getRequest = new Interspire_Request();
		$getRequest->setUserParam('brands', $createdId);

		$getResult = $brandsResource->getAction($getRequest)->getData(true);
		$this->assertSame(200, $getRequest->getResponse()->getStatus());
		$this->assertSame($createdId, $getResult['id']);

		// attempt to update via put

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<page_title>foo bar</page_title>
</brand>';

		$putRequest = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$putRequest->setUserParam('brands', $createdId);

		$putResult = $brandsResource->putAction($putRequest)->getData(true);

		$this->assertSame(200, $putRequest->getResponse()->getStatus());
		$this->assertSame($createdId, $putResult['id']);
		$this->assertSame('foo bar', $putResult['page_title']);

		// attempt to delete

		$deleteRequest = new Interspire_Request();
		$deleteRequest->setUserParam('brands', $createdId);

		$deleteResult = $brandsResource->deleteAction($deleteRequest);

		// this null test is intended (it signals the API to eventually issue a 204 No Content)
		$this->assertNull($deleteResult);

		// attempt to get the item again

		$getRequest = new Interspire_Request();
		$getRequest->setUserParam('brands', $createdId);
		// get should return nothing (which would cause other API code to issue a 404)
		$getResult = $brandsResource->getAction($getRequest)->getData(true);
		$this->assertInternalType('array', $getResult, "result not an array");
		$this->assertTrue(empty($getResult), "result not empty");
	}

	public function testGetList ()
	{
		// can't accurately test this without fixtures support (which would allow us to setup a specific data set) but
		// we can at least do *something* for now

		// create two brands, get the list of all brands and ensure the two we created are on there
		// note: this will fail if the brands ever fall off the first page during testing

		$brands = array(
			$this->_createDummyBrand() => false,
			$this->_createDummyBrand() => false,
		);

		$listRequest = new Interspire_Request();
		$listResource = $this->_getBrandsResource();

		$list = $listResource->getAction($listRequest)->getData();
		$this->assertInternalType('array', $list);
		$this->assertFalse(empty($list));

		if (count($list) == Store_Api::ITEMS_PER_PAGE_DEFAULT) {
			// for now if the paging limit is reached (probably by running this test repeatedly on an installed store)
			// then we can't test the rest -- skip (this shouldn't happen on bamboo!)
			$this->markTestSkipped();
			return;
		}

		foreach ($list as $item) {
			$id = (int)$item['id'];
			if (isset($brands[$id])) {
				$brands[$id] = true;
			}
		}

		foreach ($brands as $id => $found) {
			$this->_deleteBrand($id);
		}

		$this->assertFalse(in_array(false, $brands), "one or more dummy brands were not found in the list");
	}

	public function testGetListLimit ()
	{
		// create at least three brands and test that a get with a limit of 2 fetches only two brands
		$brands = array(
			$this->_createDummyBrand(),
			$this->_createDummyBrand(),
			$this->_createDummyBrand(),
		);

		// though we don't need to check that they were the brands we created, any result will do
		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request();
		$request->setUserParam('limit', 2);

		$list = $resource->getAction($request)->getData();

		$this->assertInternalType('array', $list, 'return type mismatch');
		$this->assertSame(2, count($list), 'list count mismatch');
		foreach ($list as $item) {
			// basic content check ensuring the list is returning actual items
			$this->assertTrue(isset($item['id']), 'list content mismatch');
		}

		foreach ($brands as $brandId) {
			$this->_deleteBrand($brandId);
		}
	}

	public function testGetListPaging ()
	{
		// we need at least 3 brands to properly test this
		$brands = array(
			$this->_createDummyBrand(),
			$this->_createDummyBrand(),
			$this->_createDummyBrand(),
		);

		// though we don't need to check that they were the brands we created, any result will do

		$resource = $this->_getBrandsResource();
		$countResource = new Store_Api_Version_2_Resource_Brands_Count();

		// first get a count of all brands in the db

		$request = new Interspire_Request();
		$countResult = $countResource->getAction($request)->getData(true);
		$this->assertGreaterThanOrEqual(count($brands), $countResult['count'], 'not enough brands in list -- did the inserts fail?');
		$count = (int)$countResult['count'];

		// using a limit of 2, iterate through each page ensuring that pages are different and there's the correct
		// number of pages
		$limit = 2;
		$expectedPages = (int)ceil($count / $limit);

		// yes this test will grow in time with data but it's really not built to be run over and over on the same db
		// without it being cleared

		$previousPage = null;
		for ($page = 1; $page <= $expectedPages; $page++) {
			$request = new Interspire_Request();
			$request->setUserParam('limit', $limit)
				->setUserParam('page', $page);
			$result = $resource->getAction($request)->getData();
			if (empty($result)) {
				break;
			}

			if ($previousPage !== null) {
				$this->assertNotSame($previousPage, $result, "result of page " . $page . " is the same as previous page");
			}

			$previousPage = $result;
		}

		$this->assertSame($expectedPages + 1, $page, "page request loop did not reach expected page limit");

		$request = new Interspire_Request();
		$request->setUserParam('limit', $limit)
			->setUserParam('page', $page);
		$result = $resource->getAction($request)->getData();
		$this->assertTrue(empty($result), "page after expected page limit should be empty but isn't");

		foreach ($brands as $brandId) {
			$this->_deleteBrand($brandId);
		}
	}

	public function testDeleteList ()
	{
		// can't implement this test without fixtures because deleting all brands would affect other product-based tests
		$this->markTestSkipped();
	}

	public function testGetWithNameCondition ()
	{
		$brandName = $this->_generateName();
		$brandId = $this->_createDummyBrand($brandName);

		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request(array('name' => $brandName));
		$list = $resource->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$brand = array_pop($list);
		$this->assertEquals($brandId, $brand['id']);
		$this->assertEquals($brandName, $brand['name']);

		$this->_deleteBrand($brandId);
	}

	public function testGetWithMinIdCondition ()
	{
		// create two brands and ensure min_id returns only one
		$brandLow = $this->_createDummyBrand();
		$brandHigh = $this->_createDummyBrand();

		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request(array('min_id' => $brandHigh));
		$list = $resource->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$brand = array_pop($list);
		$this->assertEquals($brandHigh, $brand['id']);

		$this->_deleteBrand($brandLow);
		$this->_deleteBrand($brandHigh);
	}

	public function testGetWithMaxIdCondition ()
	{
		// create two brands and ensure max_id returns only one
		// this also uses min_id which is unavoidable for now without fixture support
		$brandLow = $this->_createDummyBrand();
		$brandHigh = $this->_createDummyBrand();

		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request(array('min_id' => $brandLow, 'max_id' => $brandLow));
		$list = $resource->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$brand = array_pop($list);
		$this->assertEquals($brandLow, $brand['id']);

		$this->_deleteBrand($brandLow);
		$this->_deleteBrand($brandHigh);
	}

	public function testCountWithAnyCondition ()
	{
		$brandName = $this->_generateName();
		$brandId = $this->_createDummyBrand($brandName);

		$resource = new Store_Api_Version_2_Resource_Brands_Count();

		$request = new Interspire_Request(array('name' => $brandName));
		$data = $resource->getAction($request)->getData(true);

		$this->assertInternalType('array', $data);
		$this->assertSame(1, $data['count']);

		$this->_deleteBrand($brandId);
	}

	public function testPostToEntityFails ()
	{
		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request();
		$request->setUserParam('brands', 1);

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$resource->postAction($request);
	}

	public function testPutNewImage ()
	{
		// create a brand with no image
		$brandId = $this->_createDummyBrand();

		// PUT a new image to it
		$import = $this->_createImportableImage();
		$destruct = new Interspire_File_DestructDelete($import);

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<image_file>' . basename($import) . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brandId);
		$result = $this->_getBrandsResource()->putAction($request)->getData(true);

		$this->assertFalse(empty($result['image_file']), "image_file is empty");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $result['image_file']);
		$this->assertFileExists($imageFile, "image_file doesn't actually exist");

		$this->_deleteBrand($brandId);
	}

	public function testPutReplaceImage ()
	{
		// create a brand with an image
		$brand = $this->testLocalImage(false);
		$brandId = $brand['id'];

		// PUT a new image to it
		$import = $this->_createImportableImage();
		$destruct = new Interspire_File_DestructDelete($import);

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<image_file>' . basename($import) . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brandId);
		$result = $this->_getBrandsResource()->putAction($request)->getData(true);

		$this->assertFalse(empty($result['image_file']), "image_file is empty");
		$this->assertNotSame($brand['image_file'], $result['image_file'], "image_file string has not changed");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $result['image_file']);
		$this->assertFileExists($imageFile, "image_file doesn't actually exist");

		$this->_deleteBrand($brandId);
	}

	public function testPutMissingEntity ()
	{
		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request();
		$request->setUserParam('brands', PHP_INT_MAX);

		$this->setExpectedException('Store_Api_Exception_Resource_ResourceNotFound');
		$resource->putAction($request)->getData();
	}

	public function testPutToListFails ()
	{
		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request();

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$resource->putAction($request);
	}

	public function testDeleteMissingEntity ()
	{
		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request();
		$request->setUserParam('brands', PHP_INT_MAX);

		$this->setExpectedException('Store_Api_Exception_Resource_ResourceNotFound');
		$resource->deleteAction($request);
	}

	public function testDeleteWithImage ()
	{
		// this feels a bit dodgy but the first half of this test would really be identical to testLocalImage, so...
		$brand = $this->testLocalImage(false);

		// delete the brand and ensure the file resource is also deleted
		$existingFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $brand['image_file']);

		$this->_deleteBrand($brand['id']);

		$this->assertFileNotExists($existingFile);
	}

	public function testDeleteRemovesProductBrands ()
	{
		// test that deleting a brand also removes it from products
		$brandId = $this->_createDummyBrand();

		// create a dummy product record - this doesn't have to be functional in any way
		$product = array(
			'prodname' => 'PRODUCT_' . mt_rand(1, PHP_INT_MAX),
			'prodcatids' => '',
			'prodbrandid' => $brandId,
		);

		$productId = Store::getStoreDb()->InsertQuery('products', $product);
		$this->assertTrue(isId($productId));
		$this->_deleteBrand($brandId);

		$product = Store::getStoreDb()->FetchRow("
			SELECT
				prodbrandid
			FROM
				[|PREFIX|]products
			WHERE
				productid = " . $productId . "
		");

		$this->assertInternalType('array', $product);
		$this->assertSame('0', $product['prodbrandid']);

		Store::getStoreDb()->DeleteQuery('products', 'WHERE productid = ' . $productId);
	}

	public function testDeleteRemovesSearchTerms ()
	{
		// test that deleting a brand also deletes brand_search records
		$brandId = $this->testPostAddsSearchTerms(false);
		$this->_deleteBrand($brandId);

		$query = "
			SELECT
				COUNT(*)
			FROM
				[|PREFIX|]brand_search
			WHERE
				brandid = " . $brandId . "
		";

		$result = Store::getStoreDb()->Query($query);
		$this->assertInternalType('resource', $result, "query fail");

		$count = Store::getStoreDb()->FetchOne($result);
		$this->assertEquals(0, $count, "brand_search count mismatch");
	}

	public function testRemoteImage ()
	{
		// for now this is using beast as the image source
		// if this test ever fails check connectivity between bamboo and beast or move this image url to somewhere
		// bamboo can always access

		$url = 'http://www.google.com/images/srpr/logo4w.png';
		$brandName = $this->_generateName();

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>' . $url . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$result = $this->_getBrandsResource()->postAction($request)->getData(true);
		$this->assertInternalType('array', $result);
		$this->assertFalse(empty($result['image_file']), "image_file is empty");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $result['image_file']);
		$this->assertFileExists($imageFile, "image_file doesn't actually exist");

		$this->_deleteBrand($result['id']);
	}

	public function testMissingRemoteImage ()
	{
		// for now this is using beast as the image source
		// if this test ever fails check connectivity between bamboo and beast or move this image url to somewhere
		// bamboo can always access

		$url = 'http://www.google.com/images/missing_image.jpg';
		$brandName = $this->_generateName();

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>' . $url . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		try {
			$this->_getBrandsResource()->postAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testLocalImage ($delete = true)
	{
		$resource = $this->_getBrandsResource();
		$brandName = $this->_generateName();

		$import = $this->_createImportableImage();
		$destruct = new Interspire_File_DestructDelete($import);

		$imageFile = basename($import);

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>' . $imageFile . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$result = $resource->postAction($request)->getData(true);
		$this->assertInternalType('array', $result);
		$this->assertFalse(empty($result['image_file']), "image_file is empty");

		$imageFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $result['image_file']);
		$this->assertFileExists($imageFile, "image_file doesn't actually exist");
		$this->assertFileNotEquals($import, $imageFile, "image_file matches import file but shouldn't as it should be resized");

		if ($delete) {
			$this->_deleteBrand($result['id']);
		}

		return $result;
	}

	public function testMissingLocalImage ()
	{
		$resource = $this->_getBrandsResource();
		$brandName = $this->_generateName();

		$imageFile = 'foo.bar'; // hopefully this never exists...

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>' . $imageFile . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		try {
			$resource->postAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testLocalImageIsDirectory ()
	{
		$resource = $this->_getBrandsResource();
		$brandName = $this->_generateName();

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>.</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		try {
			$resource->postAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testLocalImagePathOutsideImportDirectory ()
	{
		$resource = $this->_getBrandsResource();
		$brandName = $this->_generateName();

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>../../index.php</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		try {
			$resource->postAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testSameImage ()
	{
		// this feels a bit dodgy but the first half of this test would really be identical to testLocalImage, so...
		$brand = $this->testLocalImage(false);

		// PUT an update to the brand with the same image_file and check that the file does not get removed or modified
		$existingFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $brand['image_file']);
		$existingMtime = filemtime($existingFile);

		// do the PUT update
		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<image_file>' . $brand['image_file'] . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brand['id']);

		$result = $this->_getBrandsResource()->putAction($request)->getData(true);

		$this->assertSame($brand['image_file'], $result['image_file'], "image_file mismatch");
		$this->assertFileExists($existingFile);
		$this->assertSame($existingMtime, filemtime($existingFile), "mtime mismatch");
		$this->_deleteBrand($brand['id']);
	}

	public function testRemoveImage ()
	{
		$brand = $this->testLocalImage(false);

		$existingFile = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $brand['image_file']);

		// do the PUT update
		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<image_file></image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brand['id']);

		$result = $this->_getBrandsResource()->putAction($request)->getData(true);

		$this->assertTrue(empty($result['image_file']), "image_file is not empty");
		$this->assertFileNotExists($existingFile, "image file was not deleted");

		$this->_deleteBrand($brand['id']);
	}

	public function testPostDuplicateBrandNameFails ()
	{
		$brandName = $this->_generateName();
		$this->_createDummyBrand($brandName);

		try {
			$this->_createDummyBrand($brandName);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}

	public function testPutDuplicateBrandNameFails ()
	{
		// create two brands
		$brandName_A = $this->_generateName();
		$brandName_B = $this->_generateName();

		$brandId_A = $this->_createDummyBrand($brandName_A);
		$brandId_B = $this->_createDummyBrand($brandName_B);

		// try to rename one to the other's name
		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName_A . '</name>
</brand>';

		$resource = $this->_getBrandsResource();

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brandId_B);

		try {
			$resource->putAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			// we catch this manually instead of using phpunit because we need to test the field name
			$this->_deleteBrand($brandId_A);
			$this->_deleteBrand($brandId_B);
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "invalid field has no reason information");
		}
	}

	public function testPostAddsSearchTerms ($delete = true)
	{
		$name = $this->_generateName();
		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $name . '</name>
<page_title>page title</page_title>
<search_keywords>search key words</search_keywords>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$brand = $this->_getBrandsResource()->postAction($request)->getData(true);

		$this->assertTrue(isId($brand['id']));

		$query = "
			SELECT
				COUNT(*)
			FROM
				[|PREFIX|]brand_search
			WHERE
				brandid = " . $brand['id'] . "
		";

		$result = Store::getStoreDb()->Query($query);
		$this->assertInternalType('resource', $result, "query fail");

		$count = Store::getStoreDb()->FetchOne($result);
		$this->assertEquals(1, $count, "brand_search count mismatch");

		if ($delete) {
			$this->_deleteBrand($brand['id']);
		}

		return $brand['id'];
	}

	public function testPutUpdatesSearchTerms ()
	{
		$brandId = $this->_createDummyBrand();

		$query = "
			SELECT
				*
			FROM
				[|PREFIX|]brand_search
			WHERE
				brandid = " . $brandId . "
		";

		$iterator = new Db_QueryIterator(Store::getStoreDb(), $query);
		$results = iterator_to_array($iterator);
		$this->assertInternalType('array', $results);
		$this->assertSame(1, count($results));
		$search = array_pop($results);
		$this->assertSame('', $search['brandpagetitle']);
		$this->assertSame('', $search['brandsearchkeywords']);

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<page_title>Foo Bar</page_title>
<search_keywords>Search Keywords</search_keywords>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);
		$request->setUserParam('brands', $brandId);

		$result = $this->_getBrandsResource()->putAction($request)->getData(true);
		$this->assertInternalType('array', $result);

		$results = iterator_to_array($iterator);
		$this->assertInternalType('array', $results);
		$this->assertSame(1, count($results));
		$search = array_pop($results);
		$this->assertSame('Foo Bar', $search['brandpagetitle']);
		$this->assertSame('Search Keywords', $search['brandsearchkeywords']);

		$this->_deleteBrand($brandId);
	}

	public function testInvalidImage ()
	{
		$resource = $this->_getBrandsResource();
		$brandName = $this->_generateName();

		$import = $this->_createImportableImage('BrandsInvalidImage.jpg');
		$destruct = new Interspire_File_DestructDelete($import);

		$imageFile = basename($import);

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<brand>
<name>' . $brandName . '</name>
<image_file>' . $imageFile . '</image_file>
</brand>';

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/xml'), $body);

		try {
			$resource->postAction($request)->getData(true);
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField'); // to get phpunit to fail here
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testPutWithoutImageDoesNotRemoveImage ()
	{
		$brand = $this->testLocalImage(false);

		$body = Interspire_Json::encode(array(
			'name' => $this->_generateName(),
		));

		$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/json'), $body);
		$request->setUserParam('brands', $brand['id']);

		$updated = $this->_getBrandsResource()->putAction($request)->getData(true);

		$this->assertSame($brand['image_file'], $updated['image_file']);

		$file = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $brand['image_file']);
		$this->assertFileExists($file, "image_file doesn't actually exist");
	}

	public function testTemporaryImageDeletedAfterOtherError ()
	{
		// force the brand resource to generate a known temporary filename so we can test against it
		$temp = Interspire_File::createTemporaryFile('api');
		$resource = $this->getMock('Store_Api_Version_2_Resource_Brands', array('getTempFilename'));
		$resource
			->expects($this->once())
			->method('getTempFilename')
			->will($this->returnValue($temp));

		$image = basename($this->_createImportableImage('BrandsInvalidImage.jpg'));
		$destruct = new Interspire_File_DestructDelete($image);

		try {
			$name = $this->_generateName();

			$body = Interspire_Json::encode(array(
				'name' => $name,
				'image_file' => $image,
			));

			$request = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/json'), $body);
			$resource->postAction($request);

			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// expected error, don't test
		}

		$this->assertFileNotExists($temp);
	}

	public function testCopyLocalFileFailure ()
	{
		$this->markTestIncomplete();
	}

	public function testZeroEntityRequestIsEmpty ()
	{
		// requesting /brands/0 should be empty, not a list of brands
		$this->_createDummyBrand();

		$request = new Interspire_Request();
		$request->setUserParam('brands', 0);

		$result = $this->_getBrandsResource()->getAction($request)->getData(true);
		$this->assertTrue(empty($result), 'result is not empty');
	}
}
