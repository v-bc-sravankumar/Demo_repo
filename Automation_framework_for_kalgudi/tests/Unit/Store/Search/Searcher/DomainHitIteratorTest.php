<?php

namespace Unit\Store\Search\Searcher;

use Store\Search\Searcher\DomainHitIterator;
use Bigcommerce\SearchClient\Hit\HitIterator;
use Bigcommerce\SearchClient\Hit\Hit;
use Bigcommerce\SearchClient\Document\BrandDocument;
use Bigcommerce\SearchClient\Provider\ProviderInterface;

class DomainHitIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testCurrentParsesHitToDomainData()
    {
        $brandData = array(
            'id'         => 23,
            'name'       => 'My Brand',
            'page_title' => 'Page Title',
            'keywords'   => array('foo', 'bar'),
            'image_file' => 'brand.png',
        );

        $results = new \ArrayIterator(array($brandData));

        $hitParser = $this->getMock('\Bigcommerce\SearchClient\Hit\HitParserInterface');
        $hitParser
            ->expects($this->at(0))
            ->method('parse')
            ->with($this->equalTo($brandData))
            ->will($this->returnCallback(function ($data) {
                $document = new BrandDocument($data);
                return new Hit($document, 54.33);
            }));

        $hitIterator = new HitIterator($results, $hitParser);
        $domainHitIterator = new DomainHitIterator($hitIterator);
        $domainHitIterator->rewind();

        $hitData = $domainHitIterator->current();

        $this->assertEquals(54.33, $hitData['score']);
        $this->assertEquals(ProviderInterface::TYPE_BRAND, $hitData['type']);

        $brand = $hitData['data'];
        $this->assertInstanceOf('\Store_Brand', $brand);

        $this->assertEquals($brandData['id'], $brand->getId());
        $this->assertEquals($brandData['name'], $brand->getName());
        $this->assertEquals($brandData['page_title'], $brand->getPageTitle());
        $this->assertEquals($brandData['keywords'], explode(',', $brand->getSearchKeywords()));
        $this->assertEquals($brandData['image_file'], $brand->getImageFileName());
    }
}
