<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper;

use Content\Blog\Post;
use Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper\PostResultBuilder;

class PostResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildHtmlResults()
    {
        /** @var \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject $template */
        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with($this->equalTo('NewsTitle'), 'test-title');
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with(
                $this->equalTo('NewsSmallContent'),
                $this->equalTo(
                    'we need to get this to be more than 200 characters, which was harder than I thought '.
                    'we need to get this to be more than 200 characters, which was harder than I thought '.
                    'we need to get this to be more ...'
                )
            );
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with($this->equalTo('NewsURL'), 'test-url');
        $template
            ->expects($this->at(3))
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultNews'))
            ->will($this->returnValue('test-html'));

        $post = new Post();
        $post->setId(123);
        $post->setTitle('test-title');
        $post->setBody(
            'we need to get this to be more than 200 characters, which was harder than I thought '.
            'we need to get this to be more than 200 characters, which was harder than I thought '.
            'we need to get this to be more than 200 characters, which was harder than I thought'
        );
        $post->setCustomUrl('test-custom-url');

        /** @var \Store_UrlGenerator_News|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_News')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();
        $urlGenerator
            ->expects($this->at(0))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo(array(
                'newsid' => 123,
                'url'    => 'test-custom-url',
            )))
            ->will($this->returnValue('test-url'));

        $builder = new PostResultBuilder($template, $urlGenerator);

        $this->assertEquals('test-html', $builder->buildHtmlResults($post));
    }
}
