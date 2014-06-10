<?php

class Admin_CategoriesTest extends Interspire_IntegrationTest
{

	public function setUp()
	{
		$GLOBALS['ISC_CLASS_ADMIN_AUTH'] = GetClass('ISC_ADMIN_AUTH');
	}

	public function testEditingParentSetsCorrectChildren()
	{
		$parentA = $this->addCategory(array('catparentid' => 0));
		$parentB = $this->addCategory(array('catparentid' => 0));

		$child1 = $this->addCategory(array('catparentid' => $parentA));
		$child2 = $this->addCategory(array('catparentid' => $parentB));

		$post = array (
					"categoryId" => $child1,
					"catparentid" => $parentB,
				);

		$nestedSet = $this->saveUpdatedCategory($post);

		$categories1 = $nestedSet->getChildren(
			array('categoryid'),
			$parentA,
			''
		);

		$childrenOfA = array();
		foreach ($categories1 as $category) {
			$childrenOfA[] = $category['categoryid'];
		}

		$categories2 = $nestedSet->getChildren(
				array('categoryid'),
				$parentB,
				''
		);

		$childrenOfB = array();
		foreach ($categories2 as $category) {
			$childrenOfB[] = $category['categoryid'];
		}

		$this->assertFalse(in_array($child1, $childrenOfA));
		$this->assertTrue(in_array($child1, $childrenOfB));

		//cleanup
		$this->fixtures->DeleteQuery('categories', "WHERE catname LIKE 'ADMIN_CATEGORY_TEST_%'");
		unset($_POST["categoryId"]);
		unset($_POST["catparentid"]);
	}

	public function testEditingSortOrderSetsCorrectOrder()
	{
		$parentA = $this->addCategory(array('catparentid' => 0));

		$child1 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 1), 'AAA');
		$child2 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 2), 'BBB');
		$child3 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 3), 'CCC');

		$post = array (
				"categoryId" => $child1,
				"catsort" => 3,
		);

		$nestedSet = $this->saveUpdatedCategory($post);

		$post = array (
				"categoryId" => $child3,
				"catsort" => 1,
		);

		$nestedSet = $this->saveUpdatedCategory($post);

		$tree = $nestedSet->getTree(
				array('categoryid', 'catparentid'),
				$parentA
				);

		$this->assertEquals($tree[0]['catparentid'], 0);

		$this->assertEquals($tree[1]['catparentid'], $parentA);
		$this->assertEquals($tree[2]['catparentid'], $parentA);
		$this->assertEquals($tree[3]['catparentid'], $parentA);

		$this->assertEquals($tree[1]['categoryid'], $child3);
		$this->assertEquals($tree[2]['categoryid'], $child2);
		$this->assertEquals($tree[3]['categoryid'], $child1);

		//cleanup
		$this->fixtures->DeleteQuery('categories', "WHERE catname LIKE 'ADMIN_CATEGORY_TEST_%'");
		unset($_POST["categoryId"]);
		unset($_POST["catparentid"]);
	}

	public function testEditingNameSetsCorrectOrder()
	{
		$parentA = $this->addCategory(array('catparentid' => 0));

		$child1 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 1), 'ZZZ');
		$child2 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 2), 'BBB');
		$child3 = $this->addCategory(array('catparentid' => $parentA, 'catsort' => 3), 'CCC');

		$post = array (
				"categoryId" => $child1,
				"catsort" => 3,
		);

		$nestedSet = $this->saveUpdatedCategory($post);

		$nestedSet = new Store_Category_Tree;
		$tree = $nestedSet->getTree(
				array('categoryid', 'catparentid'),
				$parentA
				);

		$this->assertEquals($tree[0]['catparentid'], 0);

		$this->assertEquals($tree[1]['catparentid'], $parentA);
		$this->assertEquals($tree[2]['catparentid'], $parentA);
		$this->assertEquals($tree[3]['catparentid'], $parentA);

		$this->assertEquals($tree[1]['categoryid'], $child2);
		$this->assertEquals($tree[2]['categoryid'], $child3);
		$this->assertEquals($tree[3]['categoryid'], $child1);

		//cleanup
		$this->fixtures->DeleteQuery('categories', "WHERE catname LIKE 'ADMIN_CATEGORY_TEST_%'");
		unset($_POST["categoryId"]);
		unset($_POST["catparentid"]);
	}

	private function saveUpdatedCategory($post)
	{
		$_POST = array_merge($_POST, $post);

		$admin = $this->getMock('ISC_ADMIN_CATEGORY',array('SaveUpdatedCategory', 'showMessage'));

		$admin->expects($this->any())
			->method('showMessage')
			->will($this->returnValue(''));

		$class = new ReflectionClass("ISC_ADMIN_CATEGORY");
		$method = $class->getMethod("SaveUpdatedCategory");
		$method->setAccessible(true);

		$result = $method->invoke($admin);

		$nestedSet = new Store_Category_Tree;

		return $nestedSet;
	}

	private function addCategory($data, $name = false)
	{
		$category = array(
				'catname' => $this->generateName($name),
				'catparentid'=> 0,
				'catsort' => 0,
				);

		$id= $this->fixtures->InsertQuery('categories', array_merge($category, $data));

		return $id;
	}

	private function generateName($name = false)
	{
		if ($name) {
			return 'ADMIN_CATEGORY_TEST_' .$name;
		}
		else {
			return 'ADMIN_CATEGORY_TEST_' . mt_rand(1, PHP_INT_MAX);
		}
	}

}
