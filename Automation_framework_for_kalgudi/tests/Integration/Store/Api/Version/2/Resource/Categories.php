<?php

/**
* these tests can't currently use fixtures because categories data exists in sample data and would be relied upon by
* product-based tests until fixtures can be properly introduced
*
* when fixtures are utilised properly change the tests here so they don't have to manually insert / remove dummy
* categoris
*/

class Unit_Lib_Store_Api_Version_2_Resource_Categories extends Interspire_IntegrationTest
{
	private $_images = array();

	private $_dummyProducts = array();

	private function _createImportableImage ($filename = 'CategoriesImage.jpg')
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

		$this->_images[] = $import;
		return $import;
	}

	private function _getResource ()
	{
		return new Store_Api_Version_2_Resource_Categories();
	}

	private function _getCountResource ()
	{
		return new Store_Api_Version_2_Resource_Categories_Count();
	}

	private $_dummyCategories = array();

	private function _generateName ()
	{
		return 'CATEGORY_' . mt_rand(1, PHP_INT_MAX);
	}

	private function _createDummyCategory ($json = array(), $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$json = array_merge(array(
			'name' => $this->_generateName(),
		), $json);

		$json = Interspire_Json::encode($json);
		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$result = $resource->postAction($request)->getData(true);

		$this->_dummyCategories[] = $result['id'];
		return $result;
	}

	private function _updateCategory ($id, $json, $resource = null)
	{
		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$json = Interspire_Json::encode($json);

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('categories', (int)$id);

		return $resource->putAction($request)->getData(true);
	}

	private function _getCategory ($id)
	{
		$request = new Interspire_Request();
		$request->setUserParam('categories', $id);
		return $this->_getResource()->getAction($request)->getData(true);
	}

	private function _deleteCategory ($id, $resource = null)
	{
		// before deleting, ensure the tree is up to date since testing may mangle it, otherwise we get false
		$nestedset = new Store_Category_Tree();
		$nestedset->rebuildTree();

		if ($resource === null) {
			$resource = $this->_getResource();
		}

		$request = new Interspire_Request();
		$request->setUserParam('categories', (int)$id);
		$resource->deleteAction($request);
	}

	/**
	* put your comment there...
	*
	* @return Store_Category_Tree
	*/
	private function _getMockNestedSet ($methods = array('foo'))
	{
		return $this->getMock('Store_Category_Tree', $methods);
	}

	private function _createDummyProduct ()
	{
		$product = array(
			'prodname' => 'PRODUCT_' . mt_rand(1, PHP_INT_MAX),
			'prodcatids' => '',
		);

		$productId = Store::getStoreDb()->InsertQuery('products', $product);

		$this->assertTrue(isId($productId), "dummy product insert failed");

		$productId = (int)$productId;
		$this->_dummyProducts[] = $productId;
		return $productId;
	}

	public function tearDown ()
	{
		foreach ($this->_images as $image) {
			unlink($image);
		}

		foreach ($this->_dummyProducts as $id) {
			Store::getStoreDb()->DeleteQuery('categoryassociations', 'WHERE productid = ' . $id);
			Store::getStoreDb()->DeleteQuery('products', 'WHERE productid = ' . $id);
		}

		foreach ($this->_dummyCategories as $id) {
			$this->_deleteCategory($id);
		}
	}

	public function testGetList ()
	{
		// can't accurately test this without fixtures support (which would allow us to setup a specific data set) but
		// we can at least do *something* for now

		$categories = array();

		$category = $this->_createDummyCategory();
		$categories[$category['id']] = false;

		$category = $this->_createDummyCategory();
		$categories[$category['id']] = false;

		$list = $this->_getResource()->getAction(new Interspire_Request())->getData();

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
			if (isset($categories[$id])) {
				$categories[$id] = true;
			}
		}

		$this->assertFalse(in_array(false, $categories), "one or more dummy categories were not found in the list");
	}

	public function testGetEntity ()
	{
		$category = $this->_createDummyCategory();

		$request = new Interspire_Request();
		$request->setUserParam('categories', $category['id']);

		$categories = $this->_getResource()->getAction($request)->getData();
		$this->assertInternalType('array', $categories);
		$this->assertSame(1, count($categories));
		$this->assertEquals($category['id'], $categories[0]['id']);
	}

	public function testRootCategoryParentList ()
	{
		$category = $this->_createDummyCategory();

		$request = new Interspire_Request();
		$request->setUserParam('categories', $category['id']);
		$category = $this->_getResource()->getAction($request)->getData(true);

		$this->assertSame(array($category['id']), $category['parent_category_list']);
	}

	public function testChildCategoryParentList ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$grandchild = $this->_createDummyCategory(array('parent_id' => $child['id']));

		$request = new Interspire_Request();
		$request->setUserParam('categories', $child['id']);
		$category = $this->_getResource()->getAction($request)->getData(true);
		$this->assertSame(array($parent['id'], $child['id']), $category['parent_category_list']);

		$request = new Interspire_Request();
		$request->setUserParam('categories', $grandchild['id']);
		$category = $this->_getResource()->getAction($request)->getData(true);
		$this->assertSame(array($parent['id'], $child['id'], $grandchild['id']), $category['parent_category_list']);
	}

	public function testDeleteList ()
	{
		// can't implement this test without fixtures because deleting all categories would affect other product-based
		// tests
		$this->markTestSkipped();
	}

	public function testDeleteEntity ()
	{
		$category = $this->_createDummyCategory();

		$request = new Interspire_Request();
		$request->setUserParam('categories', $category['id']);

		$this->assertNull($this->_getResource()->deleteAction($request));
	}

	public function testGetWithMinIdCondition ()
	{
		$low = $this->_createDummyCategory();
		$high = $this->_createDummyCategory();

		$request = new Interspire_Request(array('min_id' => $high['id']));
		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$item = array_pop($list);
		$this->assertSame($high['id'], $item['id']);
	}

	public function testGetWithMaxIdCondition ()
	{
		$low = $this->_createDummyCategory();
		$high = $this->_createDummyCategory();

		$request = new Interspire_Request(array(
			'min_id' => $low['id'],
			'max_id' => $low['id'],
		));

		$list = $this->_getResource()->getAction($request)->getData();

		$this->assertInternalType('array', $list);
		$this->assertSame(1, count($list));

		$item = array_pop($list);
		$this->assertSame($low['id'], $item['id']);
	}

	public function testCountWithAnyCondition ()
	{
		$low = $this->_createDummyCategory();
		$this->_createDummyCategory();
		$high = $this->_createDummyCategory();

		$request = new Interspire_Request(array(
			'min_id' => $low['id'],
			'max_id' => $high['id'],
		));

		$count = $this->_getCountResource()->getAction($request)->getData(true);

		$this->assertSame(3, $count['count']);
	}

	public function testPostToEntityFails ()
	{
		$body = Interspire_Json::encode(array(
			'name' => 'foo',
		));

		$request = new Interspire_Request();
		$request->setUserParam('categories', 1);

		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$this->_getResource()->postAction($request);
	}

	public function testPutToListFails ()
	{
		$this->setExpectedException('Store_Api_Exception_Resource_MethodNotFound');
		$this->_getResource()->putAction(new Interspire_Request());
	}

	public function testPutMissingEntity ()
	{
		$request = new Interspire_Request();
		$request->setUserParam('categories', 2147483647); // here's hoping we never have 2 billion sample categories

		$result = $this->_getResource()->putAction($request)->getData();
		$this->assertSame(array(), $result);
	}

	public function testPostDuplicateNameSameLevelFails ()
	{
		$parent = $this->_createDummyCategory();

		$name = $this->_generateName();

		$child = $this->_createDummyCategory(array(
			'name' => $name,
			'parent_id' => $parent['id'],
		));

		try {
			$child = $this->_createDummyCategory(array(
				'name' => $name,
				'parent_id' => $parent['id'],
			));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
			$this->assertSame((int)$child['id'], $exception->getDetail('duplicate_category'), "duplicate_category mismatch");
		}
	}

	public function testPutDuplicateNameSameLevelFails ()
	{
		$parent = $this->_createDummyCategory();

		$child_a = $this->_createDummyCategory(array(
			'parent_id' => $parent['id'],
		));

		$child_b = $this->_createDummyCategory(array(
			'parent_id' => $parent['id'],
		));

		$json = Interspire_Json::encode(array(
			'name' => $child_a['name'],
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('categories', $child_b['id']);

		try {
			$this->_getResource()->putAction($request);
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
			$this->assertSame((int)$child_a['id'], $exception->getDetail('duplicate_category'), "duplicate_category mismatch");
		}
	}

	public function testDuplicateNameDifferentLevelsSucceeds ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array(
			'name' => $parent['name'],
			'parent_id' => $parent['id'],
		));
	}

	public function testPostWithoutParent ()
	{
		$category = $this->_createDummyCategory();
		$this->assertSame(0, $category['parent_id']);
	}

	public function testPostWithoutDescription ()
	{
		$category = $this->_createDummyCategory();
		$this->assertSame('', $category['description']);
	}

	public function testLocalImage ()
	{
		$parent = $this->_createDummyCategory();

		$image = basename($this->_createImportableImage());
		$destruct = new Interspire_File_DestructDelete($image);

		$category = $this->_createDummyCategory(array(
			'parent_id' => $parent['id'],
			'image_file' => $image,
		));

		$this->assertNotEmpty($category['image_file'], "image_file field is empty");

		$destination = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $category['image_file']);
		$this->assertFileExists($destination, "image_file doesn't actually exist");

		$original = dirname(__FILE__) . '/CategoriesImage.jpg';
		$this->assertFileNotEquals($original, $destination, "image_file matches import file but shouldn't as it should be resized");

		return $category;
	}

	public function testMissingLocalImage ()
	{
		$parent = $this->_createDummyCategory();

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => 'foo', // hopefully this never exists
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testLocalImageIsDirectory ()
	{
		$parent = $this->_createDummyCategory();

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => '.',
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testLocalImagePathOutsideImportDirectory ()
	{
		$parent = $this->_createDummyCategory();

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => '../../index.php',
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testRemoteImage ()
	{
		$parent = $this->_createDummyCategory();

		$image = 'http://www.google.com/images/srpr/logo4w.png';

		$category = $this->_createDummyCategory(array(
			'parent_id' => $parent['id'],
			'image_file' => $image,
		));

		$this->assertNotEmpty($category['image_file'], "image_file field is empty");

		$destination = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $category['image_file']);
		$this->assertFileExists($destination, "image_file doesn't actually exist");
	}

	public function testMissingRemoteImage ()
	{
		$parent = $this->_createDummyCategory();

		$image = 'http://www.google.com/images/missing_image.jpg';

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => $image,
			));
			$this->setExpectedException('Store_Api_Exception_Resource_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField());
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testInvalidImage ()
	{
		$parent = $this->_createDummyCategory();

		$image = basename($this->_createImportableImage('CategoriesInvalidImage.jpg'));
		$destruct = new Interspire_File_DestructDelete($image);

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => $image,
			));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason information");
		}
	}

	public function testRootCategoryImagesNotAllowed ()
	{
		try {
			$this->_createDummyCategory(array(
				'image_file' => 'foo',
			));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason information");
		}
	}

	public function testPutRemoveImage ()
	{
		$category = $this->testLocalImage();

		$file = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $category['image_file']);

		$json = Interspire_Json::encode(array(
			'image_file' => '',
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('categories', $category['id']);

		$updated = $this->_getResource()->putAction($request)->getData(true);

		$this->assertSame('', $updated['image_file']);
		$this->assertFileNotExists($file);
	}

	public function testPutSameImage ()
	{
		$category = $this->testLocalImage();

		$file = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $category['image_file']);
		$mtime = filemtime($file);

		$json = Interspire_Json::encode(array(
			'image_file' => $category['image_file'],
		));

		$request = new Interspire_Request(array(), array(), array(), array('CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('categories', $category['id']);

		$updated = $this->_getResource()->putAction($request)->getData(true);

		$this->assertSame($category['image_file'], $updated['image_file']);
		$this->assertFileExists($file);
		$this->assertSame($mtime, filemtime($file));
	}

	public function testLinksInDescription ()
	{
		$description = '<a href="' . GetConfig('ShopPathNormal') . '/foo.bar">foo</a>';
		$expected = '<a href="%%GLOBAL_ShopPath%%/foo.bar">foo</a>';

		$category = $this->_createDummyCategory(array('description' => $description));
		$this->assertSame($expected, $category['description']);
	}

	public function testPostInvalidParentFails ()
	{
		try {
			$this->_createDummyCategory(array('parent_id' => 2147483647));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('parent_id', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testPutInvalidParentFails ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));

		try {
			$this->_updateCategory($child['id'], array('parent_id' => 2147483647));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('parent_id', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testParentLoopFails ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));

		try {
			$this->_updateCategory($parent['id'], array('parent_id' => $child['id']));
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('parent_id', $exception->getField(), "invalid field mismatch");
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), "invalid field has no reason");
		}
	}

	public function testPostInheritsVisible ()
	{
		$parent = $this->_createDummyCategory();
		$this->assertTrue($parent['is_visible']);

		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$this->assertTrue($child['is_visible']);
	}

	public function testPostInheritsHidden ()
	{
		$parent = $this->_createDummyCategory(array('is_visible' => false));
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$this->assertFalse($child['is_visible']);
	}

	public function testPostVisibleUnderHiddenParentFails ()
	{
		$parent = $this->_createDummyCategory(array('is_visible' => false));

		try {
			$child = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'is_visible' => true,
			));
			$this->setExpectedException('Store_Api_Exception_Resource_Conflict');
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			$this->assertNotEquals('', $exception->getDetail('conflict_reason'), "conflict has no reason");
		}
	}

	public function testPutVisibleUnderHiddenParentFails ()
	{
		// this really shouldn't be allowed, but, the UI permits it via drag and drop and other reassigning methods, so
		// the API is too for now
		$this->markTestSkipped();
		return;

		/*
		$parent = $this->_createDummyCategory(array('is_visible' => false));
		$child = $this->_createDummyCategory(array('is_visible' => true));

		$this->_updateCategory($child['id'], array('parent_id' => $parent['id']));
		*/
	}

	public function testPutHiddenHidesChildren ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$grandchild = $this->_createDummyCategory(array('parent_id' => $child['id']));

		$this->assertTrue($parent['is_visible'], "parent is hidden");
		$this->assertTrue($child['is_visible'], "child is hidden");
		$this->assertTrue($grandchild['is_visible'], "grandchild is hidden");

		$this->_updateCategory($parent['id'], array('is_visible' => false));

		$parent = $this->_getCategory($parent['id']);
		$child = $this->_getCategory($child['id']);
		$grandchild = $this->_getCategory($grandchild['id']);

		$this->assertFalse($parent['is_visible'], "parent is visible");
		$this->assertFalse($child['is_visible'], "child is visible");
		$this->assertFalse($grandchild['is_visible'], "grandchild is visible");
	}

	public function testPutVisibleShowsParents ()
	{
		$parent = $this->_createDummyCategory(array('is_visible' => false));
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$grandchild = $this->_createDummyCategory(array('parent_id' => $child['id']));

		$this->assertFalse($parent['is_visible'], "parent is visible");
		$this->assertFalse($child['is_visible'], "child is visible");
		$this->assertFalse($grandchild['is_visible'], "grandchild is visible");

		$this->_updateCategory($grandchild['id'], array('is_visible' => true));

		$parent = $this->_getCategory($parent['id']);
		$child = $this->_getCategory($child['id']);
		$grandchild = $this->_getCategory($grandchild['id']);

		$this->assertTrue($grandchild['is_visible'], "grandchild is still hidden");
		$this->assertTrue($child['is_visible'], "child is still hidden");
		$this->assertTrue($parent['is_visible'], "parent is still hidden");
	}

	public function testPostRootCategoryDoesntRebuildNestedSet ()
	{
		$nestedset = $this->_getMockNestedSet(array('rebuildTree'));

		$nestedset
			->expects($this->never())
			->method('rebuildTree');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$parent = $this->_createDummyCategory(array(), $resource);
	}

	public function testPostChildDoesntRebuildNestedSet ()
	{
		$parent = $this->_createDummyCategory();

		$nestedset = $this->_getMockNestedSet(array('rebuildTree'));

		$nestedset
			->expects($this->never())
			->method('rebuildTree');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
	}

	public function testChangeParentRebuildsNestedSet ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory();

		$nestedset = $this->_getMockNestedSet(array('rebuildTree'));

		// really wish there was some way of telling phpunit to execute the original method like ->will(original) so
		// the tree can be rebuilt and still have expects() around it
		$nestedset
			->expects($this->once())
			->method('rebuildTree')
			->will($this->returnValue(true));

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$this->_updateCategory($child['id'], array('parent_id' => $parent['id']), $resource);
	}

	public function testChangeNameRebuildsNestedSet ()
	{
		$category = $this->_createDummyCategory();

		$nestedset = $this->_getMockNestedSet(array('rebuildTree'));

		// really wish there was some way of telling phpunit to execute the original method like ->will(original) so
		// the tree can be rebuilt and still have expects() around it
		$nestedset
			->expects($this->once())
			->method('rebuildTree')
			->will($this->returnValue(true));

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$this->_updateCategory($category['id'], array('name' => $this->_generateName()), $resource);
	}

	public function testChangeSortRebuildsNestedSet ()
	{
		$category = $this->_createDummyCategory();

		$nestedset = $this->_getMockNestedSet(array('rebuildTree'));

		// really wish there was some way of telling phpunit to execute the original method like ->will(original) so
		// the tree can be rebuilt and still have expects() around it
		$nestedset
			->expects($this->once())
			->method('rebuildTree')
			->will($this->returnValue(true));

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$this->_updateCategory($category['id'], array('sort_order' => ++$category['sort_order']), $resource);
	}

	public function testPostAdjustsNestedSet ()
	{
		return $this->markTestSkipped('Mocking and not executing adjustInsertedNode causes this test to fail under MySQL 5.5. Skipping this as an ineffectual test rather than trying to fix it.');

		$nestedset = $this->_getMockNestedSet(array('adjustInsertedNode'));
		$nestedset->rebuildTree();

		$nestedset
			->expects($this->once())
			->method('adjustInsertedNode')
			->with($this->isType('integer'), $this->equalTo(0));

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setNestedSet($nestedset);

		$parent = $this->_createDummyCategory(array(), $resource);
	}

	public function testPostPutUpdatesDatastoreRootCategories ()
	{
		$datastore = $this->getMock('ISC_DATA_STORE', array('UpdateRootCategories'));
		$datastore
			->expects($this->once())
			->method('UpdateRootCategories');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setDataStore($datastore);

		$this->_createDummyCategory(array(), $resource);
	}

	public function testPostUpdatesDatastoreGroupDiscounts ()
	{
		$datastore = $this->getMock('ISC_DATA_STORE', array('UpdateCustomerGroupsCategoryDiscounts'));
		$datastore
			->expects($this->once())
			->method('UpdateCustomerGroupsCategoryDiscounts');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setDataStore($datastore);

		$this->_createDummyCategory(array(), $resource);
	}

	public function testPutSameParentDoesntUpdateDatastoreGroupDiscounts ()
	{
		$parent = $this->_createDummyCategory();
		$category = $this->_createDummyCategory(array('parent_id' => $parent['id']));

		$datastore = $this->getMock('ISC_DATA_STORE', array('UpdateCustomerGroupsCategoryDiscounts'));
		$datastore
			->expects($this->never())
			->method('UpdateCustomerGroupsCategoryDiscounts');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setDataStore($datastore);

		$this->_updateCategory($category['id'], array('parent_id' => $category['parent_id']), $resource);
	}

	public function testChangeParentUpdatesDatastoreGroupDiscounts ()
	{
		$parent_a = $this->_createDummyCategory();
		$parent_b = $this->_createDummyCategory();

		$category = $this->_createDummyCategory(array('parent_id' => $parent_a['id']));

		$datastore = $this->getMock('ISC_DATA_STORE', array('UpdateCustomerGroupsCategoryDiscounts'));
		$datastore
			->expects($this->once())
			->method('UpdateCustomerGroupsCategoryDiscounts');

		$resource = new Store_Api_Version_2_Resource_Categories();
		$resource->setDataStore($datastore);

		$this->_updateCategory($category['id'], array('parent_id' => $parent_b['parent_id']), $resource);
	}

	public function testDeleteOrphanedProductsFails ()
	{
		$nestedset = new Store_Category_Tree();
		$nestedset->rebuildTree();

		$category = $this->_createDummyCategory();
		$productId = $this->_createDummyProduct();

		$associationId = Store::getStoreDb()->InsertQuery('categoryassociations', array(
			'categoryid' => $category['id'],
			'productid' => $productId,
		));

		$this->assertTrue(isId($associationId));

		try {
			$this->_deleteCategory($category['id']);
		} catch (Store_Api_Exception_Resource_Conflict $exception) {
			$this->assertNotEquals($exception->getDetail('conflict_reason'), "conflict has no reason");
			$this->assertSame(array($productId), $exception->getDetail('orphaned_products'), "orphaned products list mismatch");
		}
	}

	public function testDeleteDeletesChildren ()
	{
		$parent = $this->_createDummyCategory();
		$child = $this->_createDummyCategory(array('parent_id' => $parent['id']));
		$this->_deleteCategory($parent['id']);

		$child = $this->_getCategory($child['id']);
		$this->assertInternalType('array', $child);
		$this->assertTrue(empty($child));
	}

	public function testPutWithoutImageDoesNotRemoveImage ()
	{
		$category = $this->testLocalImage();

		$updated = $this->_updateCategory($category['id'], array('name' => $this->_generateName()));

		$this->assertSame($category['image_file'], $updated['image_file']);

		$file = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/' . $category['image_file']);
		$this->assertFileExists($file, "image_file doesn't actually exist");
	}

	public function testPutUpdatesParentCategoryList ()
	{
		// create the categories to test around
		$parent_a = $this->_createDummyCategory();
		$category = $this->_createDummyCategory(array('parent_id' => $parent_a['id']));
		$child = $this->_createDummyCategory(array('parent_id' => $category['id']));

		$parent_b = $this->_createDummyCategory();

		// check that the initial parent list setup is ok
		$this->assertTrue(in_array($parent_a['id'], $category['parent_category_list']), "parent_a not in category parent list");

		$this->assertTrue(in_array($parent_a['id'], $child['parent_category_list']), "parent_a not in child parent list");
		$this->assertTrue(in_array($category['id'], $child['parent_category_list']), "category not in child parent list");

		// move $category to under $parent_b
		$updatedCategory = $this->_updateCategory($category['id'], array('parent_id' => $parent_b['id']));
		$updatedChild = $this->_getCategory($child['id']);

		// check that the after-update parent list setup is ok
		$this->assertNotEquals($category['parent_category_list'], $updatedCategory['parent_category_list']);
		$this->assertNotEquals($child['parent_category_list'], $updatedChild['parent_category_list']);

		$this->assertTrue(in_array($parent_b['id'], $updatedCategory['parent_category_list']), "after update, parent_b not in category parent list");

		$this->assertTrue(in_array($parent_b['id'], $updatedChild['parent_category_list']), "after update, parent_b not in child parent list");
		$this->assertTrue(in_array($category['id'], $updatedChild['parent_category_list']), "after update, category not in child parent list");
	}

	public function testTemporaryImageDeletedAfterOtherError ()
	{
		$parent = $this->_createDummyCategory();

		// force the category resource to generate a known temporary filename so we can test against it
		$temp = Interspire_File::createTemporaryFile('api');
		$resource = $this->getMock('Store_Api_Version_2_Resource_Categories', array('getTempFilename'));
		$resource
			->expects($this->once())
			->method('getTempFilename')
			->will($this->returnValue($temp));

		$image = basename($this->_createImportableImage('CategoriesInvalidImage.jpg'));
		$destruct = new Interspire_File_DestructDelete($image);

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => $image,
			), $resource);
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			// expected error, don't test
		}

		$this->assertFileNotExists($temp);
	}

	public function testCopyLocalFileFailure ()
	{
		$parent = $this->_createDummyCategory();

		// force the category resource to generate a known temporary filename so we can create it read only in advance
		// to cause an error inside the resource

		$temp = Interspire_File::createTemporaryFile('api');
		$resource = $this->getMock('Store_Api_Version_2_Resource_Categories', array('getTempFilename'));
		$resource
			->expects($this->once())
			->method('getTempFilename')
			->will($this->returnValue($temp));

		$destructs = array();

		$image = basename($this->_createImportableImage('CategoriesInvalidImage.jpg'));
		$destructs[] = new Interspire_File_DestructDelete($image);

		touch($temp);
		$destructs[] = new Interspire_File_DestructDelete($temp);

		try {
			$category = $this->_createDummyCategory(array(
				'parent_id' => $parent['id'],
				'image_file' => $image,
			), $resource);
			$this->setExpectedException('Store_Api_Exception_Request_InvalidField');
		} catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertSame('image_file', $exception->getField(), 'invalid field not defined');
			$this->assertNotEquals('', $exception->getDetail('invalid_reason'), 'invalid field has no reason');
		}
	}

	public function testZeroEntityRequestIsEmpty ()
	{
		// requesting /categories/0 should be empty, not a list of brands
		$this->_createDummyCategory();
		$result = $this->_getCategory(0);
		$this->assertTrue(empty($result), 'result is not empty');
	}
}
