<?php

namespace Unit\Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper;

use Bigcommerce\SearchClient\Document\PageDocument;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PageResultBuilder;

class PageResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Store_Settings
     */
    private function getSettings()
    {
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'ShopPath'    => 'http://foo.com',
            'ShopPathSSL' => 'https://foo.com',
        )));
        $settings->load();

        return $settings;
    }

    public function testBuildHtmlResultsForLink()
    {
        /** @var \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject $template */
        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        // Assert that the page title, URL and content are set correctly.
        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with($this->equalTo('PageTitle'), $this->equalTo('test-title'));
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with($this->equalTo('PageURL'), $this->equalTo('test-link'));
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with($this->equalTo('PageSmallContent'), $this->equalTo(''));

        // Assert that the snippet is rendered.
        $template
            ->expects($this->at(3))
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultAJAXPage'))
            ->will($this->returnValue('test-html'));

        $page = array(
            'pagetitle'   => 'test-title',
            'pagetype'    => 1, // link
            'pagelink'    => 'test-link',
            'pagecontent' => 'test-content',
        );

        $builder = new PageResultBuilder($template, $this->getSettings(), new \Store_UrlGenerator_Page());

        $this->assertEquals('test-html', $builder->buildHtmlResults($page));
    }

    public function testBuildHtmlResultsForNonLink()
    {
        /** @var \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject $template */
        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        // Assert that the page title, URL, content and content are set correctly.
        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with($this->equalTo('PageTitle'), $this->equalTo('test-title'));
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with($this->equalTo('PageURL'), $this->equalTo('test-link'));
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with(
                $this->equalTo('PageSmallContent'),
                $this->equalTo('http://foo.com https://foo.com this is some other ...')
            );

        // Assert that the snippet is rendered.
        $template
            ->expects($this->at(3))
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultAJAXPage'))
            ->will($this->returnValue('test-html'));

        /** @var \Store_UrlGenerator_Page|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_Page')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();

        $page = array(
            'pagetitle'   => 'test-title',
            'pagetype'    => 0, // non-link
            'pagecontent' => '%%GLOBAL_ShopPath%% %%GLOBAL_ShopPathSSL%% this is some other text so that we reach the',
        );

        $urlGenerator
            ->expects($this->at(0))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo($page))
            ->will($this->returnValue('test-link'));

        $builder = new PageResultBuilder($template, $this->getSettings(), $urlGenerator);

        $this->assertEquals('test-html', $builder->buildHtmlResults($page));
    }
}
