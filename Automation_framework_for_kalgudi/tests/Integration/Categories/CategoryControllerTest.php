<?php

namespace Integration\Categories;

use ISC_CATEGORY;

class CategoryControllerTest extends \PHPUnit_Framework_TestCase
{
    private $categoryController;

    public function setUp()
    {
        $this->categoryController = $this
            ->getMockBuilder('ISC_CATEGORY')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testEmptyLayoutDefaultsToCategory()
    {
        $this->categoryController->setLayoutFile('');
        $this->assertEquals('category', $this->categoryController->getLayoutFile());
    }

    public function testMissingLayoutDefaultsToCategory()
    {
        $this->categoryController->setLayoutFile('foo.html');
        $this->assertEquals('category', $this->categoryController->getLayoutFile());
    }

    public function testSetLayoutStripsExtension()
    {
        $this->categoryController->setLayoutFile('default.html');
        $this->assertEquals('default', $this->categoryController->getLayoutFile());
    }
}
