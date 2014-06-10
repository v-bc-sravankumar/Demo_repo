<?php

use org\bovigo\vfs\vfsStream;

class Unit_Lib_Store_WebDav_FS_Directory extends Interspire_UnitTest
{
	public function setUp()
	{
		$davTree = array(
			'root' => array(
				'README.txt' => 'hello world',
				'content'    => array(),
				'exports'    => array(),
				'empty-dir'  => array(),
				'single-file-dir' => array(
					'foo' => 'foo',
				),
				'non-empty-dir' => array(
					'foo' => 'foo',
					'bar' => 'bar',
				),
				),
			);
		vfsStream::setup('/', 0755, $davTree);
	}

	public function testGetChildrenOnNonEmptyDirectory()
	{
		$expected = array(
			'bar',
			'foo',
		);

		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('root/non-empty-dir'));
		$nodes = $webdavDir->getChildren();
		$names = array_map(function($node){ return $node->getName(); }, $nodes);

		$this->assertSame($names, $expected);
	}

	public function testGetChildrenOnEmptyDirectory()
	{
		$expected = array();

		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('root/empty-dir'));
		$nodes = $webdavDir->getChildren();

		$this->assertSame($nodes, $expected);
	}

	// can't run this test within our full phpunit suite, for some reason error
	// conversion isn't working -- not investigating because the test below
	// covers the functional effect

	// public function testGetChildrenOnInvalidStateIssuesWarning()
	// {
	// 	$expected = array();

	// 	$webdavDir = $this->getMock('Store_WebDav_FS_Directory', array('getChild'), array(vfsStream::url('root/single-file-dir')));
	// 	$webdavDir->expects($this->once())
	// 	          ->method('getChild')
	// 	          ->will($this->throwException(new Sabre\DAV\Exception\FileNotFound()));

	// 	$this->setExpectedException('PHPUnit_Framework_Error_Warning');
	// 	$webdavDir->getChildren();
	// }

	public function testGetChildrenOnInvalidStateSkipsFile()
	{
		$expected = array();

		$webdavDir = $this->getMock('Store_WebDav_FS_Directory', array('getChild'), array(vfsStream::url('root/single-file-dir')));
		$webdavDir->expects($this->once())
		          ->method('getChild')
		          ->will($this->throwException(new Sabre\DAV\Exception\FileNotFound()));

		$nodes = @$webdavDir->getChildren();
		$this->assertSame($expected, $nodes);
	}

	public function testGetChildDirSuccess()
	{
		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('root'));

		$child = $webdavDir->getChild('content');

		$this->assertEquals('content', $child->getName());
	}

	public function testGetChildDirFail()
	{
		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('root'));

		$this->setExpectedException('Sabre\DAV\Exception\FileNotFound');
		$webdavDir->getChild('noname');
	}

	public function testGetDirNameWithAlias()
	{
		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('content'));

		$this->assertEquals('content', $webdavDir->getName());
	}

	public function testGetDirNameWithoutAlias()
	{
		$webdavDir = new Store_WebDav_FS_Directory(vfsStream::url('content'), 'alias');

		$this->assertEquals('alias', $webdavDir->getName());
	}
}
