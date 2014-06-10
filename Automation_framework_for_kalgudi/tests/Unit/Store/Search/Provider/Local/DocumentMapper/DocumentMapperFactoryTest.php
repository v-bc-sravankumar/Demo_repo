<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

use Store\Search\Provider\Local\DocumentMapper\DocumentMapperFactory;
use Bigcommerce\SearchClient\Provider\ProviderInterface;

class DocumentMapperFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function typeDataProvider()
    {
        return array(
            array(ProviderInterface::TYPE_BRAND, 'BrandDocumentMapper'),
            array(ProviderInterface::TYPE_CATEGORY, 'CategoryDocumentMapper'),
            array(ProviderInterface::TYPE_POST, 'PostDocumentMapper'),
            array(ProviderInterface::TYPE_PAGE, 'PageDocumentMapper'),
            array(ProviderInterface::TYPE_PRODUCT, 'ProductDocumentMapper'),
        );
    }

    /**
     * @dataProvider typeDataProvider
     */
    public function testGetMapperForType($type, $mapperName)
    {
        $mapper = DocumentMapperFactory::getMapperForType($type);

        $mapperClass = 'Store\\Search\\Provider\\Local\\DocumentMapper\\' . $mapperName;

        $this->assertInstanceOf($mapperClass, $mapper);
    }

    /**
     * @expectedException \Bigcommerce\SearchClient\Exception\UnknownTypeException
     */
    public function testGetMapperForUnknownTypeThrowsException()
    {
        DocumentMapperFactory::getMapperForType('foo');
    }
}
