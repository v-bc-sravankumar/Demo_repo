<?php

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class Unit_Lib_Store_WebDav_FS_File extends Interspire_UnitTest
{
	private $webdavReadMeFile;

	public function setUp()
	{
		$davTree = array(
			'/' => array(
				'README.txt' => 'hello world',
				'content'    => array(),
				'exports'    => array()
			),
		);

        vfsStreamWrapper::register();
		$dir = vfsStream::setup('root', 0755, $davTree);
        $children = $dir->getChildren();
        $url = vfsStream::url('/README.txt');
		$this->webdavReadMeFile = new Store_WebDav_FS_File($url);
	}

	public function testGetETag()
	{
		$this->assertFileExists(vfsStream::url('/README.txt'));

		$this->assertNull($this->webdavReadMeFile->getETag());
	}

	public function testGetPhysicalPath()
	{
		$this->assertEquals(vfsStream::url('/README.txt'), $this->webdavReadMeFile->getPhysicalPath());
	}

	public function testGetContentType()
	{
		$this->assertEquals(Interspire_Download::getMimeTypeForFilename(vfsStream::url('/README.txt')), $this->webdavReadMeFile->getContentType());
	}
}
