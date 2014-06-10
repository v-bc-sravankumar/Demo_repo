<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper;

use Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper\PageResultBuilder;

class PageResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $page
     * @param bool $used
     * @return \Store_UrlGenerator_Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUrlGenerator(array $page = array(), $used = true)
    {
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_Page')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();

        if ($used) {
            $urlGenerator
                ->expects($this->at(0))
                ->method('getStoreFrontUrl')
                ->with($this->equalTo($page))
                ->will($this->returnValue('test-link'));
        }

        return $urlGenerator;
    }

    private function getTemplate($assignments)
    {
         $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        $x = 0;
        foreach ($assignments as $key => $value) {
            $template
                ->expects($this->at($x))
                ->method('Assign')
                ->with($this->equalTo($key), $this->equalTo($value));

            $x++;
        }

        return $template;
    }

    public function testLinkPage()
    {
        $page = array(
            'pagetitle' => 'My Page',
            'pagetype'  => 1,
            'pagelink'  => 'http://www.google.com',
        );

        $assignments = array(
            'PageTitle'        => $page['pagetitle'],
            'PageSmallContent' => '',
            'PageURL'          => $page['pagelink'],
        );

        $template     = $this->getTemplate($assignments);
        $urlGenerator = $this->getUrlGenerator(array(), false);

        $builder = new PageResultBuilder($template, $urlGenerator);
        $builder->buildHtmlResults($page);
    }

    public function testOtherPage()
    {
        $page = array(
            'pageid'      => 4,
            'pagetitle'   => 'My Page',
            'pagetype'    => 2,
            'pagecontent' => 'Page content here',
            'pageurl'     => 'test-link',
        );

        $assignments = array(
            'PageTitle'        => $page['pagetitle'],
            'PageSmallContent' => $page['pagecontent'],
            'PageURL'          => $page['pageurl'],
        );

        $template     = $this->getTemplate($assignments);
        $urlGenerator = $this->getUrlGenerator($page);

        $builder = new PageResultBuilder($template, $urlGenerator);
        $builder->buildHtmlResults($page);
    }

    public function testPageWithLongContentIsTrimmedWithElipsis()
    {
        $page = array(
            'pageid'      => 4,
            'pagetitle'   => 'My Page',
            'pagetype'    => 2,
            'pagecontent' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
laboris nisi ut aliquip ex ea commodo consequat.',
            'pageurl'     => 'test-link',
        );

        $trimmedContent = substr($page['pagecontent'], 0, 199) . ' ...';

        $assignments = array(
            'PageTitle'        => $page['pagetitle'],
            'PageSmallContent' => $trimmedContent,
            'PageURL'          => $page['pageurl'],
        );

        $template     = $this->getTemplate($assignments);
        $urlGenerator = $this->getUrlGenerator($page);

        $builder = new PageResultBuilder($template, $urlGenerator);
        $builder->buildHtmlResults($page);
    }

    public function testPageWithLongContentIsTrimmedWithoutElipsis()
    {
        $page = array(
            'pageid'      => 4,
            'pagetitle'   => 'My Page',
            'pagetype'    => 2,
            'pagecontent' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
laboris nisi u. Hi.',
            'pageurl'     => 'test-link',
        );

        $trimmedContent = substr($page['pagecontent'], 0, 199);

        $assignments = array(
            'PageTitle'        => $page['pagetitle'],
            'PageSmallContent' => $trimmedContent,
            'PageURL'          => $page['pageurl'],
        );

        $template     = $this->getTemplate($assignments);
        $urlGenerator = $this->getUrlGenerator($page);

        $builder = new PageResultBuilder($template, $urlGenerator);
        $builder->buildHtmlResults($page);
    }
}
