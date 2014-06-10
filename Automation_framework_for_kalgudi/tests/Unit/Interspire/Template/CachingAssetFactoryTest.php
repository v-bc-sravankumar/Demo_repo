<?php

namespace Unit\Interspire\Template;

use Interspire\Template\CachingAssetFactory;
use org\bovigo\vfs\vfsStream;

class CachingAssetFactoryTest extends \PHPUnit_Framework_TestCase
{
	private $_mockBuilder;

	public function __construct()
	{
		$this->_mockBuilder = $this
					->getMockBuilder('\Assetic\Cache\CacheInterface')
					->setMethods(array('has', 'get'));
	}

	public function setUp()
	{
		vfsStream::setup();
		vfsStream::create(array(
			'asset' => array(
				'test.txt' => 'nonCachedContent',
			),
		));
	}

	public function testCacheMiss()
	{
		$mockCache = $this->_mockBuilder->getMockForAbstractClass();
		$mockCache->expects($this->any())->method('has')->will($this->returnValue(false));
		$assetFactory = new CachingAssetFactory($mockCache, vfsStream::url('asset'));
		$asset = $assetFactory->createAsset('test.txt');
		$asset->load();
		$this->assertEquals($asset->getContent(), 'nonCachedContent');
	}

	public function testCacheHit()
	{
		$mockCache = $this->_mockBuilder->getMockForAbstractClass();
		$mockCache->expects($this->any())->method('has')->will($this->returnValue(true));
		$mockCache->expects($this->any())->method('get')->will($this->returnValue('cachedContent'));
		$assetFactory = new CachingAssetFactory($mockCache, vfsStream::url('asset'));
		$asset = $assetFactory->createAsset('test.txt');
		$asset->load();
		$this->assertEquals($asset->getContent(), 'cachedContent');
	}
}
