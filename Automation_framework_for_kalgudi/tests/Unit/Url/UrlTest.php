<?php

use Url\Url;

class Unit_Url_UrlTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideUrlData
     * @param $primitive
     */
    public function testToStringReturnsStringRepresentationOfUrl($primitive)
    {
        $url = new Url($primitive);
        $this->assertEquals($primitive, (string)$url);
    }

    /**
     * @dataProvider provideRelativeUrlData
     * @param string $primitive
     * @param string $expected
     */
    public function testToRelativeReturnsRelativeUrl($primitive, $expected)
    {
        $url = new Url($primitive);
        $this->assertEquals($expected, (string)$url->toRelativeUrl());
    }

    public function testGetParameterReturnsCorrectParameter()
    {
        $url = new Url('http://username:password@hostname/path?arg=value#anchor');
        $this->assertEquals($url->getParameter('arg'), 'value');
    }

    public function provideUrlData()
    {
        return array(
            array(''),
            array('path'),
            array('path?arg=value'),
            array('path?arg=value#anchor'),
            array('/path'),
            array('/path?arg=value'),
            array('/path?arg=value#anchor'),
            array('http://username:password@hostname'),
            array('http://username:password@hostname/'),
            array('http://username:password@hostname/path'),
            array('http://username:password@hostname/path?arg=value'),
            array('http://username:password@hostname/path?arg=value#anchor'),
        );
    }

    public function provideRelativeUrlData()
    {
        return array(
            array('', ''),
            array('path', 'path'),
            array('path?arg=value', 'path?arg=value'),
            array('path?arg=value#anchor', 'path?arg=value#anchor'),
            array('/path', '/path'),
            array('/path?arg=value', '/path?arg=value'),
            array('/path?arg=value#anchor', '/path?arg=value#anchor'),
            array('http://username:password@hostname', ''),
            array('http://username:password@hostname/', '/'),
            array('http://username:password@hostname/path', '/path'),
            array('http://username:password@hostname/path?arg=value', '/path?arg=value'),
            array('http://username:password@hostname/path?arg=value#anchor', '/path?arg=value#anchor'),
        );
    }
}