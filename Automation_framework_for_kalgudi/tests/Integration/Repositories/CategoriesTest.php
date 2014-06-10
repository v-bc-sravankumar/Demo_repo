<?php

namespace Integration\Repositories;

use Repository\Categories;

/**
 * @group nosample
 */
class CategoriesTest extends \PHPUnit_Framework_TestCase
{
    private $createdCategories = array();
    private $gateway;

    public function __construct()
    {
        $this->gateway = new \Store_Category_Gateway();
    }

    public function tearDown()
    {
        if (!empty($this->createdCategories)) {
            $this->gateway->multiDelete($this->createdCategories);
        }
    }

    private function createCategory($category)
    {
        $default = array_fill_keys(array(
            'catname',
            'catdesc',
            'catparentid',
            'catviews',
            'catsort',
            'catpagetitle',
            'catmetakeywords',
            'catmetadesc',
            'catsearchkeywords',
            'catlayoutfile',
            'catparentlist',
            'catimagefile',
            'cataltcategoriescache',
            'cat_enable_optimizer',
            'google_ps_enabled',
        ), '');

        $category = array_merge($default, $category);

        foreach ($category as $field => $value) {
            $_POST[$field] = $value;
        }

        $id = $this->gateway->create();

        if (!$id) {
            $this->fail('Failed to save category: ' . $this->gateway->error);
        }

        $this->createdCategories[] = $id;

        $category['categoryid'] = $id;
        return $category;
    }

    public function testGetParentCategoriesWithUrl()
    {
        $grandParentCategory = array(
            'catname'               => 'Grandparent Category',
            'catparentid'           => 0,
            'category_custom_url'   => '/grand-category',
        );
        $grandParentCategory = $this->createCategory($grandParentCategory);

        $parentCategory = array(
            'catname'               => 'Parent Category',
            'catparentid'           => $grandParentCategory['categoryid'],
            'category_custom_url'   => '/parent-category',
        );
        $parentCategory = $this->createCategory($parentCategory);

        $category = array(
            'catname'               => 'Category',
            'catparentid'           => $parentCategory['categoryid'],
            'category_custom_url'   => '/category',
        );
        $category = $this->createCategory($category);

        $tree = new \Store_Category_Tree();
        $tree->rebuildTree();

        $expected = array(
            array(
                'categoryid' => $grandParentCategory['categoryid'],
                'catname'    => $grandParentCategory['catname'],
                'url'        => $grandParentCategory['category_custom_url'],
            ),
            array(
                'categoryid' => $parentCategory['categoryid'],
                'catname'    => $parentCategory['catname'],
                'url'        => $parentCategory['category_custom_url'],
            ),
        );

        $repository = new Categories();
        $parentCategories = $repository->getParentCategoriesWithUrl($category['catparentid']);

        $this->assertEquals($expected, $parentCategories);
    }
}
