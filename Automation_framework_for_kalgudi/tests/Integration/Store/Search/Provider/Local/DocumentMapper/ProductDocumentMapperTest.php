<?php

namespace Integration\Store\Search\Provider\Local\DocumentMapper;

use Store\Search\Provider\Local\DocumentMapper\ProductDocumentMapper;
use Test\FixtureTest;

/**
 * @group nosample
 */
class ProductDocumentMapperTest extends FixtureTest
{
    public function testMapWithBrandIdWithoutBrandNameAddsBrandName()
    {
        $brands = $this->loadFixture('brands');
        $brand = reset($brands);

        $data = array(
            'productid'   => 12,
            'prodbrandid' => $brand->getId(),
        );

        $mapper = new ProductDocumentMapper();
        $document = $mapper->mapToDocument($data);

        $this->assertEquals(12, $document->getId());
        $this->assertEquals($brand->getId(), $document->getBrandId());
        $this->assertEquals($brand->getName(), $document->getBrandName());
    }
}
