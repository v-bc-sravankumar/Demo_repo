<?php

namespace Tests\Unit\Template;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

if (!defined('PRODUCT_ID')) {
    // shut up, template class, I want to run you without init.php
    define('PRODUCT_ID', 'ISC');
}

class FileExistsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $structure = array(
            'directory' => array(
                'file_a' => '',
                'file_b' => '',
            ),
        );

        vfsStreamWrapper::register();
        vfsStream::setup('root', null, $structure);
    }

    public function mockTemplate()
    {
        // disable constructor because it calls on GetConfig
        $mock = $this->getMockBuilder('\TEMPLATE')
                     ->setMethods(null)
                     ->disableOriginalConstructor()
                     ->getMock();

        return $mock;
    }

    public function testFileExists()
    {
        $t = $this->mockTemplate();
        $this->assertTrue($t->indexedFileExists('vfs://directory', 'file_a'));
    }

    public function testFileNotExists()
    {
        $t = $this->mockTemplate();
        $this->assertFalse($t->indexedFileExists('vfs://directory', 'file_c'));
    }

    public function testIndexedFileExists()
    {
        $t = $this->mockTemplate();
        $t->indexedFileExists('vfs://directory', 'file_b');
        unlink('vfs://directory/file_b');
        $this->assertTrue($t->indexedFileExists('vfs://directory', 'file_b'));
    }

    public function testIndexedFileNotExists()
    {
        $t = $this->mockTemplate();
        $t->indexedFileExists('vfs://directory', 'file_c');
        file_put_contents('vfs://directory/file_c', '');
        $this->assertFalse($t->indexedFileExists('vfs://directory', 'file_c'));
    }
}
