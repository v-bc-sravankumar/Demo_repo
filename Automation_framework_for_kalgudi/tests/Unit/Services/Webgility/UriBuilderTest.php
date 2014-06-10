<?php

class Unit_Services_Webgility_UriBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testAccountEncoding()
    {
        $uriPattern = 'https://example.com/?u=%s';
        $uriBuilder = new \Services\Webgility\UriBuilder(12345);

        // URL-encoded, base64-encoded version of 12345 mixed with $uriPattern.
        $uri = $uriBuilder->buildUri($uriPattern);

        $this->assertEquals(
            $uri,
            'https://example.com/?u=MTIzNDU%3D'
        );
    }
}

