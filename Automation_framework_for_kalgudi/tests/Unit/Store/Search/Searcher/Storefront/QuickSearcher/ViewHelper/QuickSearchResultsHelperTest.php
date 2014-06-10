<?php

namespace Unit\Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper;

use Bigcommerce\SearchClient\Hit\Hit;
use Bigcommerce\SearchClient\Hit\HitIterator;
use Bigcommerce\SearchClient\Hit\HitParserInterface;
use Bigcommerce\SearchClient\Result\Result;
use DataModel\ArrayIterator;
use Language\LanguageManager;
use Store\Search\Provider\Local\DocumentMapper\PageDocumentMapper;
use Store\Search\Searcher\DomainHitIterator;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PageResultBuilder;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PostResultBuilder;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\ProductResultBuilder;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\QuickSearchResultsHelper;

class QuickSearchResultsHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyResults()
    {
        $page = array(
            'pageid'             => '9',
            'pagetitle'          => 'My Page',
            'pagecontent'        => 'Some content',
            'pagetype'           => '0',
            'pagelink'           => 'http://google.com',
            'pagedesc'           => 'Meta description',
            'pagemetatitle'      => 'Page Title',
            'pagestatus'         => '1',
            'pagecustomersonly'  => '0',
            'pagesearchkeywords' => 'key,word',
            'url'                => '/my-page',
        );

        $pageMapper   = new PageDocumentMapper();
        $pageDocument = $pageMapper->mapToDocument($page);

        /** @var PageResultBuilder|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this
            ->getMockBuilder('Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PageResultBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder
            ->expects($this->at(0))
            ->method('buildHtmlResults')
            ->with($this->equalTo($page))
            ->will($this->returnValue('test-html'));

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
        )));

        /** @var PostResultBuilder $postBuilder */
        $postBuilder = $this
            ->getMockBuilder('Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\PostResultBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ProductResultBuilder $productBuilder */
        $productBuilder = $this
            ->getMockBuilder('Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\ProductResultBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var LanguageManager $languageManager */
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var QuickSearchResultsHelper|\PHPUnit_Framework_MockObject_MockObject $helper */
        $helper = new QuickSearchResultsHelper(
            $builder,
            $postBuilder,
            $productBuilder,
            $languageManager,
            $settings
        );

        $hit = new Hit($pageDocument);
        $hitIterator = new HitIterator(
            new ArrayIterator(array($hit)),
            new TestHitParser()
        );

        /** @var Result|\PHPUnit_Framework_MockObject_MockObject $result */
        $result = $this
            ->getMockBuilder('Bigcommerce\SearchClient\Result\Result')
            ->disableOriginalConstructor()
            ->setMethods(array('getHits'))
            ->getMock();
        $result
            ->expects($this->at(0))
            ->method('getHits')
            ->will($this->returnValue($hitIterator));

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<response><result>test-html</result></response>\n",
            $helper->generateXmlForSearchResults(
                new DomainHitIterator($result->getHits()),
                'test-query'
            )
        );
    }
}

class TestHitParser implements HitParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        return $data;
    }
}
