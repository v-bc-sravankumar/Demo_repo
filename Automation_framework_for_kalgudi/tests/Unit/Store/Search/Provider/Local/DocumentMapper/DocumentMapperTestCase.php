<?php

namespace Unit\Store\Search\Provider\Local\DocumentMapper;

use Bigcommerce\SearchClient\Exception\DocumentValidationException;

class DocumentMapperTestCase extends \PHPUnit_Framework_TestCase
{
    protected function validateDocument($document, $forUpdate = false)
    {
        try {
            $document->validate($forUpdate);
        }
        catch (DocumentValidationException $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
