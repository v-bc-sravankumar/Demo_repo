<?php

namespace Unit\Interspire;

use PHPUnit_Framework_TestCase;
use Interspire_File;
use org\bovigo\vfs\vfsStream;

class InterspireFileTest extends PHPUnit_Framework_TestCase
{
    public function testCopyFileToTemporaryFileIfUrlForFile()
    {
        $path = Interspire_File::copyFileToTemporaryFileIfUrl('/foo/bar.txt');
        $this->assertEquals('/foo/bar.txt', $path);
    }

    public function testCopyFileToTemporaryFileIfUrlForUrl()
    {
        vfsStream::setup();
        vfsStream::create(array(
            'foo' => array(
                'bar.txt' => 'foobar',
            ),
        ));

        $path = Interspire_File::copyFileToTemporaryFileIfUrl(vfsStream::url('foo/bar.txt'));

        $this->assertFileExists($path);
        $this->assertStringStartsWith(realpath(sys_get_temp_dir()), $path);
        $this->assertEquals('foobar', file_get_contents($path));

        unlink($path);
    }

    public function testCopyFileToTemporaryFileIfUrlWithPrefix()
    {
        vfsStream::setup();
        vfsStream::create(array(
            'foo' => array(
                'bar.txt' => 'foobar',
            ),
        ));

        $path = Interspire_File::copyFileToTemporaryFileIfUrl(vfsStream::url('foo/bar.txt'), 'prefix-');

        $this->assertStringStartsWith('prefix-', basename($path));

        unlink($path);
    }

    public function testCopyFileToTemporaryFileIfUrlWithDirectory()
    {
        vfsStream::setup();
        vfsStream::create(array(
            'foo' => array(
                'bar.txt' => 'foobar',
            ),
        ));

        $dir = sys_get_temp_dir() . '/' . uniqid('testdir-');
        mkdir($dir);

        $path = Interspire_File::copyFileToTemporaryFileIfUrl(vfsStream::url('foo/bar.txt'), '', $dir);

        $this->assertStringStartsWith(realpath($dir), $path);

        unlink($path);
        rmdir($dir);
    }
}
