<?php

namespace Unit\Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper;

use Bigcommerce\SearchClient\Document\PostDocument;
use Content\Blog\Post;
use Guzzle\Common\Exception\InvalidArgumentException;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PostResultBuilder;

class PostResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTemplate()
    {
        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        return $template;
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Post must be an instance of Content\Blog\Post (stdClass given).
     */
    public function testExceptionWhenNonPostObject()
    {
        $builder = new PostResultBuilder($this->getTemplate(), new \Store_UrlGenerator_News());
        $builder->buildHtmlResults(new \stdClass());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Post must be an instance of Content\Blog\Post (array given).
     */
    public function testExceptionWhenNonObject()
    {
        $builder = new PostResultBuilder($this->getTemplate(), new \Store_UrlGenerator_News());
        $builder->buildHtmlResults(array());
    }

    public function testGeneration()
    {
        $template = $this->getTemplate();
        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with($this->equalTo('NewsTitle'), $this->equalTo('test-title'));
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with($this->equalTo('NewsURL'), $this->equalTo('http://foo.com'));
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with(
                $this->equalTo('NewsSmallContent'),
                $this->equalTo('this is some content that we hope will be over 50 ...')
            );
        $template
            ->expects($this->at(3))
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultAJAXNews'))
            ->will($this->returnValue('test-html'));

        /** @var \Store_UrlGenerator_News|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_News')
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();
        $urlGenerator
            ->expects($this->at(0))
            ->method('getStoreFrontUrl')
            ->with($this->equalTo(array('newsid' => 123, 'url' => 'test-url')))
            ->will($this->returnValue('http://foo.com'));

        $customUrl = $this
            ->getMockBuilder('Store_CustomUrl')
            ->setMethods(array('getUrl'))
            ->getMock();
        $customUrl
            ->expects($this->at(0))
            ->method('getUrl')
            ->will($this->returnValue('test-url'));

        /** @var Post|\PHPUnit_Framework_MockObject_MockObject $post */
        $post = $this
            ->getMockBuilder('Content\Blog\Post')
            ->setMethods(array('getCustomUrl'))
            ->getMock();
        $post
            ->expects($this->at(0))
            ->method('getCustomUrl')
            ->will($this->returnValue($customUrl));
        $post->setId(123);
        $post->setTitle('test-title');
        $post->setBody('this is some content that we hope will be over 50 characters');

        $builder = new PostResultBuilder($template, $urlGenerator);
        $html    = $builder->buildHtmlResults($post);

        $this->assertEquals('test-html', $html);
    }
}
