<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

require_once __DIR__ . '/DocumentMapperTestCase.php';

use Store\Search\Provider\Local\DocumentMapper\AbstractDocumentMapper;
use Bigcommerce\SearchClient\Document\AbstractDocument;

class AbstractDocumentMapperTest extends DocumentMapperTestCase
{
    public function explodeAndFilterCsvDataProvider()
    {
        return array(
            array('', array()),
            array('foo', array('foo')),
            array('foo,bar', array('foo', 'bar')),
            array('foo,,bar', array('foo', 'bar')),
            array(' foo , , bar ', array('foo', 'bar')),
        );
    }

    /**
     * @dataProvider explodeAndFilterCsvDataProvider
     */
    public function testExplodeAndFilterCsv($keywords, $expected)
    {
        $data = array(
            'i_keywords' => $keywords,
        );

        $mapper = new TestDocumentMapper();
        $document = $mapper->mapToDocument($data);

        $this->assertEquals($expected, $document->getKeywords());
    }

    public function testMapToDocumentFromChangesWithNoBeforeDataHasDirtyDataForFields()
    {
        $data = array(
            'id'        => 4,
            'i_name'    => 'My Name',
        );

        $mapper = new TestDocumentMapper();
        $document = $mapper->mapToDocumentFromChanges(null, $data);

        $this->validateDocument($document, true);

        $expected = array(
            'name' => 'My Name',
        );

        $this->assertEquals($expected, $document->getDirtyData());
        $this->assertEquals(4, $document->getId());
    }

    public function testMapToDocumentFromChangesWithNoChangedDataHasNoDirtyData()
    {
        $beforeData = array(
            'id'            => 2,
            'i_name'        => 'My Name',
            'i_keywords'    => 'foo,bar',
        );

        $afterData = $beforeData;

        $mapper = new TestDocumentMapper();
        $document = $mapper->mapToDocumentFromChanges($beforeData, $afterData);

        // should be invalid with no data to update
        $this->assertFalse($document->isValid(true));

        $this->assertEmpty($document->getDirtyData());
        $this->assertEquals(2, $document->getId());
    }

    public function testMapToDocumentFromChangesWithChangedDataHasDirtyDataForChangedFields()
    {
        $beforeData = array(
            'id'            => 6,
            'i_name'        => 'My Name',
            'i_keywords'    => 'foo,bar',
        );

        $afterData = array(
            'id'            => 6,
            'i_name'        => 'My Name',
            'i_keywords'    => 'hello,world',
        );

        $mapper = new TestDocumentMapper();
        $document = $mapper->mapToDocumentFromChanges($beforeData, $afterData);

        $this->validateDocument($document, true);

        $expected = array(
            'keywords' => array('hello', 'world'),
        );

        $this->assertEquals($expected, $document->getDirtyData());
        $this->assertEquals(6, $document->getId());
    }

    public function testMapToDocumentFromChangesWithChangedIdChangesDocumentId()
    {
        $beforeData = array(
            'id'            => 1,
            'i_name'        => 'My Name',
            'i_keywords'    => 'foo,bar',
        );

        $afterData = array(
            'id'            => 10,
            'i_name'        => 'My Name',
            'i_keywords'    => 'hello,world',
        );

        $mapper = new TestDocumentMapper();
        $document = $mapper->mapToDocumentFromChanges($beforeData, $afterData);

        $this->validateDocument($document, true);

        $expected = array(
            'keywords' => array('hello', 'world'),
        );

        $this->assertEquals($expected, $document->getDirtyData());
        $this->assertEquals(10, $document->getId());
    }

    public function testSetGetIterator()
    {
        $iterator = new \ArrayIterator(array());

        $mapper = new TestDocumentMapper();
        $this->assertEquals($mapper, $mapper->setIterator($iterator));

        $this->assertEquals($iterator, $mapper->getIterator());
    }
}

class TestDocumentMapper extends AbstractDocumentMapper
{
    public function mapToDocument($data)
    {
        if (array_key_exists('i_keywords', $data)) {
            $data['i_keywords'] = $this->explodeAndFilterCsv($data['i_keywords']);
        }

        $document = new TestDocument();

        $methodMap = array(
            'id'            => 'setId',
            'i_name'        => 'setName',
            'i_keywords'    => 'setKeywords',
        );

        foreach ($methodMap as $field => $methodName) {
            if (array_key_exists($field, $data)) {
                $document->$methodName($data[$field]);
            }
        }

        return $document;
    }

    public function mapFromDocument(AbstractDocument $document, $forUpdate = false)
    {

    }
}

class TestDocument extends AbstractDocument
{
    protected $fields = array(
        'name',
        'keywords',
    );

    public function setName($name)
    {
        return $this->setField('name', $name);
    }

    public function getName()
    {
        return $this->getField('name');
    }

    public function setKeywords($keywords)
    {
        return $this->setField('keywords', $keywords);
    }

    public function getKeywords()
    {
        return $this->getField('keywords');
    }

    public function getFieldConstraints()
    {
        return array();
    }
}
