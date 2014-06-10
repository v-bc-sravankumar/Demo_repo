<?php

namespace Unit\App\Model\Content;

use Content\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetSearchKeywords()
    {
        $page = new Page();
        $this->assertEquals($page, $page->setSearchKeywords('foo,bar'));
        $this->assertEquals('foo,bar', $page->getSearchKeywords());
    }
}
