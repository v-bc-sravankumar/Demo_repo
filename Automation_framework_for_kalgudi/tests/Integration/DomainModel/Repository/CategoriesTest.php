<?php

use Repository\Categories;

class Integration_DomainModel_Repository_CategoriesTest extends Interspire_IntegrationTest
{
	protected function setUp()
	{
	}

	public function testFindProductsByIdNoResults()
	{
		$repository = new \Repository\Categories();
		$results = $repository->findProductsById(99999);
		$this->assertEquals(count($results), 0);
	}

	public function testFindProductsByIdResults()
	{
		$repository = new \Repository\Categories();
		$results = $repository->findProductsById(1);
		$this->assertNotEquals(count($results), 0);
	}

	public function testGetDeepestLevelCategoryBasic()
	{
		$repository = new \Repository\Categories();
		$deepestCategory = $repository->getDeepestLevelCategory(array(1));
		$this->assertEquals($deepestCategory, 1);

		//test removal of invisible categories
		$this->fixtures->db->Query("UPDATE [|PREFIX|]categories SET catvisible = 0 WHERE categoryid = 1");
		$deepestCategory = $repository->getDeepestLevelCategory(array(1, 2));
		$this->assertEquals($deepestCategory, 2);

		//cleanup
		$this->fixtures->db->Query("UPDATE [|PREFIX|]categories SET catvisible = 1 WHERE categoryid = 1");
	}

	public function testGetDeepestLevelCategoryOneParent()
	{
		$repository = new \Repository\Categories();
		$this->createCategory(array("categoryid" => 999,"catparentid" => 0,"catparentlist" => "1"));
		$this->createCategory(array("categoryid" => 998,"catparentid" => 3, "catparentlist" => "3"));

		$deepestCategory = $repository->getDeepestLevelCategory(array(999, 998));
		$this->assertEquals($deepestCategory, 998);

		$this->removeCategory(999);
		$this->removeCategory(998);
	}

	public function testGetDeepestLevelCategoryMultiple()
	{
		$repository = new \Repository\Categories();
		$this->createCategory(array("categoryid" => 999,"catparentid" => 0,"catparentlist" => "1"));
		$this->createCategory(array("categoryid" => 998,"catparentid" => 3, "catparentlist" => "3"));
		$this->createCategory(array("categoryid" => 997,"catparentid" => 1, "catparentlist" => "1, 21"));
		$this->createCategory(array("categoryid" => 996,"catparentid" => 23, "catparentlist" => "1,21,22,23,24"));

		$deepestCategory = $repository->getDeepestLevelCategory(array(999, 998,997, 996));
		$this->assertEquals($deepestCategory, 996);

		$this->removeCategory(999);
		$this->removeCategory(998);
		$this->removeCategory(997);
		$this->removeCategory(996);
	}

	private function createCategory($data)
	{
		$array = array(
				"catparentid" => 0,
				"catname" => "test",
				"catdesc" => "test",
				"catparentlist" => "1",
				"catvisible" => 1,
		);

		$url = null;
		if (isset($data['url'])) {
			$url = $data['url'];
			unset($data['url']);
		}

		$id = $this->fixtures->db->InsertQuery("categories", array_merge($array, $data));

		if ($url) {
			$customUrl = new Store_CustomUrl();
			$customUrl
				->setTargetType('category')
				->setTargetId($id)
				->setUrl($url)
				->save();
		}

		return $id;
	}

	private function removeCategory($id)
	{
		$this->fixtures->DeleteQuery('categories', "WHERE categoryid =" . $id);

		Store_CustomUrl::find('target_id = ' . $id . ' AND target_type = "category"')->deleteAll();
	}

	public function testFindByIds()
	{
		$category1 = $this->createCategory(array(
			'catparentid' => '1',
			'catname' => 'Category 1',
			'catdesc' => 'Category Description 1',
			'catsort' => '1',
			'catpagetitle' => 'Category Title 1',
			'catmetakeywords' => 'meta,keywords,1',
			'catmetadesc' => 'Category Meta Desc 1',
			'catlayoutfile' => 'category1.html',
			'catparentlist' => '1,1',
			'catimagefile' => 'category1.png',
			'catvisible' => '1',
			'catsearchkeywords' => 'search,keywords,1',
			'url' => '/category-1',
		));

		$category2 = $this->createCategory(array(
			'categoryid' => null,
			'catparentid' => '2',
			'catname' => 'Category 2',
			'catdesc' => 'Category Description 2',
			'catsort' => '2',
			'catpagetitle' => 'Category Title 2',
			'catmetakeywords' => 'meta,keywords,2',
			'catmetadesc' => 'Category Meta Desc 2',
			'catlayoutfile' => 'category2.html',
			'catparentlist' => '2,2',
			'catimagefile' => 'category2.png',
			'catvisible' => '1',
			'catsearchkeywords' => 'search,keywords,2',
			'url' => '/category-2',
		));

		$category3 = $this->createCategory(array(
			'categoryid' => null,
			'catparentid' => '3',
			'catname' => 'Category 3',
			'catdesc' => 'Category Description 3',
			'catsort' => '3',
			'catpagetitle' => 'Category Title 3',
			'catmetakeywords' => 'meta,keywords,3',
			'catmetadesc' => 'Category Meta Desc 3',
			'catlayoutfile' => 'category3.html',
			'catparentlist' => '3,3',
			'catimagefile' => 'category3.png',
			'catvisible' => '1',
			'catsearchkeywords' => 'search,keywords,3',
			'url' => '/category-3',
		));

		$ids = array($category1, $category3);

		$expected = array(
			array(
				'id' => $category1,
				'parent_id' => 1,
				'name' => 'Category 1',
				'description' => 'Category Description 1',
				'sort_order' => 1,
				'page_title' => 'Category Title 1',
				'meta_keywords' => 'meta,keywords,1',
				'meta_description' => 'Category Meta Desc 1',
				'layout_file' => 'category1.html',
				'parent_category_list' => array(1,1),
				'image_file' => 'category1.png',
				'is_visible' => true,
				'search_keywords' => 'search,keywords,1',
				'url' => '/category-1',
			),
			array(
				'id' => $category3,
				'parent_id' => 3,
				'name' => 'Category 3',
				'description' => 'Category Description 3',
				'sort_order' => 3,
				'page_title' => 'Category Title 3',
				'meta_keywords' => 'meta,keywords,3',
				'meta_description' => 'Category Meta Desc 3',
				'layout_file' => 'category3.html',
				'parent_category_list' => array(3,3),
				'image_file' => 'category3.png',
				'is_visible' => true,
				'search_keywords' => 'search,keywords,3',
				'url' => '/category-3',
			),
		);

		$repository = new Categories();
		$categories = $repository->findByIds($ids);

		$this->assertEquals($expected, iterator_to_array($categories));

		$this->removeCategory($category1);
		$this->removeCategory($category2);
		$this->removeCategory($category3);
	}
}
