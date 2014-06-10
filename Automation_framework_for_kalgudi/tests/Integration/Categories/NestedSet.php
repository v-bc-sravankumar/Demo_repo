<?php

class Unit_Categories_NestedSet extends Interspire_IntegrationTest
{

	public function testInstallationBuiltNestedCategoriesTree()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on old sample data');

		$expected = array (
		  array (
			'categoryid' => '2',
			'catdepth' => '0',
		  ),
		  array (
			'categoryid' => '7',
			'catdepth' => '1',
		  ),
		  array (
			'categoryid' => '3',
			'catdepth' => '0',
		  ),
		  array (
			'categoryid' => '8',
			'catdepth' => '1',
		  ),
		  array (
			'categoryid' => '1',
			'catdepth' => '0',
		  ),
		  array (
			'categoryid' => '4',
			'catdepth' => '1',
		  ),
		  array (
			'categoryid' => '5',
			'catdepth' => '1',
		  ),
		  array (
			'categoryid' => '9',
			'catdepth' => '1',
		  ),
		  array (
			'categoryid' => '6',
			'catdepth' => '1',
		  ),
		);

		$nestedset = new Store_Category_Tree();
		$tree = $nestedset->getTree(array('categoryid'));

		$this->assertEquals($expected, $tree);
	}

	public function testGetBasicSubTree ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on old sample data');

		$set = new Store_Category_Tree();
		$tree = $set->getTree(array('categoryid'), 1);

		$expected = array(
		  array (
		    'categoryid' => '1',
		    'catdepth' => '0',
		  ),
		  array (
		    'categoryid' => '4',
		    'catdepth' => '1',
		  ),
		  array (
		    'categoryid' => '5',
		    'catdepth' => '1',
		  ),
		  array (
		    'categoryid' => '9',
		    'catdepth' => '1',
		  ),
		  array (
		    'categoryid' => '6',
		    'catdepth' => '1',
		  ),
		);

		$this->assertEquals($expected, $tree);
	}

	public function testGetRootNodesOnly ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on old sample data');


		$set = new Store_Category_Tree();
		$tree = $set->getTree(array('categoryid'), ISC_NESTEDSET_START_ROOT, 0);

		$expected = array (
		  array (
		    'categoryid' => '2',
		    'catdepth' => '0',
		  ),
		  array (
		    'categoryid' => '3',
		    'catdepth' => '0',
		  ),
		  array (
		    'categoryid' => '1',
		    'catdepth' => '0',
		  ),
		);

		$this->assertEquals($expected, $tree);
	}

	public function testSelectingNodeWithHiddenParentReturnsNoResults ()
	{
		// change category visible for just this test
		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 0 WHERE categoryid = 1");

		$set = new Store_Category_Tree();
		$restrictions = array(
			"MIN(`parent`.`catvisible`) = 1",
		);
		$tree = $set->getTree(array('categoryid'), 4, ISC_NESTEDSET_DEPTH_ALL, null, null, true, $restrictions);

		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 1 WHERE categoryid = 1");

		$expected = array();

		$this->assertEquals($expected, $tree);
	}

	public function testSelectingHiddenNodeReturnsNoResults ()
	{
		// change category visible for just this test
		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 0 WHERE categoryid = 4");

		$set = new Store_Category_Tree();
		$restrictions = array(
			"MIN(`parent`.`catvisible`) = 1",
		);
		$tree = $set->getTree(array('categoryid'), 4, ISC_NESTEDSET_DEPTH_ALL, null, null, true, $restrictions);

		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 1 WHERE categoryid = 4");

		$expected = array();

		$this->assertEquals($expected, $tree);
	}

	public function testHiddenNodeIsExcludedFromResults ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on old sample data');

		// change category visible for just this test
		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 0 WHERE categoryid = 1");

		$set = new Store_Category_Tree();
		$restrictions = array(
			"MIN(`parent`.`catvisible`) = 1",
		);
		$tree = $set->getTree(array('categoryid'), ISC_NESTEDSET_START_ROOT, ISC_NESTEDSET_DEPTH_ALL, null, null, true, $restrictions);

		$this->fixtures->Query("UPDATE [|PREFIX|]categories SET catvisible = 1 WHERE categoryid = 1");

		$expected = array (
		  array (
		    'categoryid' => '2',
		    'catdepth' => '0',
		  ),
		  array (
		    'categoryid' => '7',
		    'catdepth' => '1',
		  ),
		  array (
		    'categoryid' => '3',
		    'catdepth' => '0',
		  ),
		  array (
		    'categoryid' => '8',
		    'catdepth' => '1',
		  ),
		);

		$this->assertEquals($expected, $tree);
	}
}
