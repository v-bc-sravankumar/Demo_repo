<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper;

use Utilities\Links;
use Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper\BrandResultBuilder;

class BrandResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Links|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLinks()
    {
        $links = $this
            ->getMockBuilder('Utilities\Links')
            ->disableOriginalConstructor()
            ->setMethods(array('brandLink'))
            ->getMock();
        $links
            ->expects($this->at(0))
            ->method('brandLink')
            ->with($this->equalTo('test-name'))
            ->will($this->returnValue('test-link'));

        return $links;
    }

    public function testBuildHtmlResultsWithBrandObject()
    {
        $brand = new \Store_Brand();
        $brand->setName('test-name');

        $builder = new BrandResultBuilder($this->getLinks());

        $this->assertEquals(
            '<a href="test-link">test-name</a>',
            $builder->buildHtmlResults($brand)
        );
    }

    public function testBuildHtmlResultsWithBrandArray()
    {
        $brand = array('brandname' => 'test-name');

        $builder = new BrandResultBuilder($this->getLinks());

        $this->assertEquals(
            '<a href="test-link">test-name</a>',
            $builder->buildHtmlResults($brand)
        );
    }
}
