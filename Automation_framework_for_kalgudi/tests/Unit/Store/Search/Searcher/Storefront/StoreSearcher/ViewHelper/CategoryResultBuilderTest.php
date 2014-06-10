<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper;

use Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper\CategoryResultBuilder;

class CategoryResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $category
     * @param int $index
     * @return \Store_UrlGenerator_Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUrlGenerator(array $category = array(), $index = 0)
    {
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_Category')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();


        $urlGenerator
            ->expects($this->at($index))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo($category))
            ->will($this->returnValue('test-link'));


        return $urlGenerator;
    }

    private function getRepository()
    {
        return $this->getMock('\Repository\Categories');
    }

    public function testBuildHtmlResultsForCategoryWithoutParent()
    {
        $category = array(
            'categoryid'  => 23,
            'catname'     => 'My Category',
            'catparentid' => 0,
            'url'         => 'test-link',
        );

        $urlData = $category;
        unset($urlData['catparentid']);

        $repository = $this->getRepository();
        $repository
            ->expects($this->never())
            ->method('getParentCategoriesWithUrl');

        $builder = new CategoryResultBuilder($repository, $this->getUrlGenerator($urlData));

        $this->assertEquals(
            '<a href="test-link">My Category</a>',
            $builder->buildHtmlResults($category)
        );
    }

    public function testBuildHtmlResultsForCategoryWithParents()
    {
        $category = array(
            'categoryid'  => 23,
            'catname'     => 'My Category',
            'catparentid' => 88,
            'url'         => 'test-link',
        );

        $urlData = $category;
        unset($urlData['catparentid']);

        $parentCategories = array(
            array(
                'categoryid' => 44,
                'catname'    => 'Grandparent Category',
                'url'        => 'grand-parent-link',
            ),
            array(
                'categoryid' => 88,
                'catname'    => 'Parent Category',
                'url'        => 'parent-link',
            ),
        );

        $urlGenerator = $this->getUrlGenerator($urlData, 2);
        $urlGenerator
            ->expects($this->at(0))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo($parentCategories[0]))
            ->will($this->returnValue($parentCategories[0]['url']));

        $urlGenerator
            ->expects($this->at(1))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo($parentCategories[1]))
            ->will($this->returnValue($parentCategories[1]['url']));

        $repository = $this->getRepository();
        $repository
            ->expects($this->once())
            ->method('getParentCategoriesWithUrl')
            ->with($this->equalTo($category['catparentid']))
            ->will($this->returnValue($parentCategories));

        $builder = new CategoryResultBuilder($repository, $urlGenerator);

        $expected = '<a href="grand-parent-link">Grandparent Category</a> &gt; '
            . '<a href="parent-link">Parent Category</a> &gt; '
            . '<a href="test-link">My Category</a>';

        $this->assertEquals(
            $expected,
            $builder->buildHtmlResults($category)
        );
    }
}
