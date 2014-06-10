<?php

require_once __DIR__.'/../../../../../vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php';

use Services\Themes\BuilderTemplates\BuilderTemplates;

class BuilderTemplatesTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var BuilderTemplates
	 */
	private $builderTemplates;

	/**
	 * @var string
	 */
	private $baseDir;

	/**
	 * @var string
	 */
	private $cacheDir;

	/**
	 * @var string
	 */
	private $templateDir;

	private $existingTemplatePath;

	public function setUp()
	{
		org\bovigo\vfs\vfsStream::setup('root');
		$this->builderTemplates = new BuilderTemplates();
		$this->baseDir = org\bovigo\vfs\vfsStream::url('root');
		$this->builderTemplates->setBaseDir($this->baseDir);
		$this->builderTemplates->setProductVersionCode(1);

		$this->templateDir = $this->baseDir . '/templates';
		mkdir($this->templateDir);

		$this->existingTemplatePath = Theme::getRepoBasePath();
		Theme::setRepoBasePath($this->templateDir);

		$this->cacheDir = $this->baseDir . '/cache/';
		mkdir($this->cacheDir);
		$this->builderTemplates->setCacheDir($this->cacheDir);

		Store_Config::override('ShopPath', 'http://foo.com');
	}

	public function tearDown()
	{
		Theme::setRepoBasePath($this->existingTemplatePath);
	}

	public function testListInstalledTemplatesReturnsInstalledTemplates()
	{
		mkdir($this->templateDir . '/Streetlight');
		mkdir($this->templateDir . '/Streetlight/Previews');
		file_put_contents($this->templateDir. '/Streetlight/Previews/Gainsboro.jpg', '');
		file_put_contents($this->templateDir. '/Streetlight/Previews/Manatee.jpg', '');

		mkdir($this->templateDir . '/Tumbleweed');
		mkdir($this->templateDir . '/Tumbleweed/Previews');
		file_put_contents($this->templateDir. '/Tumbleweed/Previews/Vanilla.jpg', '');
		file_put_contents($this->templateDir. '/Tumbleweed/Previews/Zaffre.jpg', '');

		$configMock = $this->getMockClass('\Store_Config', array('get'));
		$configMock::staticExpects($this->any())
			->method('get')
			->with($this->equalTo('ShopPathNormal'))
			->will($this->returnValue('http://test.com'));

		$themeMock = $this->getMockClass('\Theme', array('readThemeConfiguration'));
		$themeMock::staticExpects($this->any())
			->method('readThemeConfiguration')
			->will($this->returnValue(array(
				'Features' => \Theme::FEATURE_SET_MODERNUI,
				'DemoStore' => 'demostore',
		)));

		$this->builderTemplates->setConfigClass($configMock);
		$this->builderTemplates->setThemeClass($themeMock);

		$expected = array(
			array(
				'id' => 'Streetlight',
				'name' => 'Streetlight',
				'color' => 'Gainsboro',
				'preview' => '/img/theme-thumbs/Streetlight-Gainsboro.jpg',
				'previewFull' => Theme::getRepoURL('Streetlight', '/Previews/Gainsboro.jpg'),
				'features' => Theme::FEATURE_SET_MODERNUI,
				'isInstalled' => true,
				'demoStore' => 'demostore',
				'partner' => null,
				'featuresTextArray' => null,
				'devicesText' => null,
				'descriptionHtml' => null,
			),
			array(
				'id' => 'Streetlight',
				'name' => 'Streetlight',
				'color' => 'Manatee',
				'preview' => '/img/theme-thumbs/Streetlight-Manatee.jpg',
				'previewFull' => Theme::getRepoURL('Streetlight', '/Previews/Manatee.jpg'),
				'features' => Theme::FEATURE_SET_MODERNUI,
				'isInstalled' => true,
				'demoStore' => 'demostore',
				'partner' => null,
				'featuresTextArray' => null,
				'devicesText' => null,
				'descriptionHtml' => null,
			),
			array(
				'id' => 'Tumbleweed',
				'name' => 'Tumbleweed',
				'color' => 'Vanilla',
				'preview' => '/img/theme-thumbs/Tumbleweed-Vanilla.jpg',
				'previewFull' => Theme::getRepoURL('Tumbleweed', '/Previews/Vanilla.jpg'),
				'features' => Theme::FEATURE_SET_MODERNUI,
				'isInstalled' => true,
				'demoStore' => 'demostore',
				'partner' => null,
				'featuresTextArray' => null,
				'devicesText' => null,
				'descriptionHtml' => null,
			),
			array(
				'id' => 'Tumbleweed',
				'name' => 'Tumbleweed',
				'color' => 'Zaffre',
				'preview' => '/img/theme-thumbs/Tumbleweed-Zaffre.jpg',
				'previewFull' => Theme::getRepoURL('Tumbleweed', '/Previews/Zaffre.jpg'),
				'features' => Theme::FEATURE_SET_MODERNUI,
				'isInstalled' => true,
				'demoStore' => 'demostore',
				'partner' => null,
				'featuresTextArray' => null,
				'devicesText' => null,
				'descriptionHtml' => null,
			),
		);

		$actual = $this->builderTemplates->listInstalledTemplates();

		$this->assertEquals($expected, $actual);
	}

	public function testBuildUrlCorrectlyInsertsVersionCode()
	{
		$expected = 'http://example.com/1/2';
		$actual = $this->builderTemplates->buildUrl('http://example.com/%%VERSION%%/%%R1%%', array('r1' => 2));

		$this->assertEquals($expected, $actual);
	}

	public function testListDownloadableTemplatesReturnsCachedTemplates()
	{
		$this->builderTemplates->setDownloadTemplates(true);

		file_put_contents($this->cacheDir. 'remote_templates.xml', $this->getXmlTemplatesSample());

		$expected = array(
			array(
				'id' => 'Bedlam',
				'name' => 'Bedlam',
				'color' => 'Carnelian',
				'preview' => 'http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian_thumb.jpg',
				'previewFull' => 'http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian.jpg',
				'features' => 0,
				'isInstalled' => false,
			),
		);
		$actual = $this->builderTemplates->listDownloadableTemplates();

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \Services\Themes\ServiceException
	 */
	public function testListDownloadableTemplatesThrowsServiceExceptionWithInvalidXml()
	{
		$this->builderTemplates->setDownloadTemplates(true);

		file_put_contents($this->cacheDir. 'remote_templates.xml', ')@#(%Y<>?@#%<?>@#^&$#%<>?');

		$this->builderTemplates->listDownloadableTemplates();
	}

	public function testListDownloadableTemplatesReturnsRemoteTemplates()
	{
		$this->builderTemplates->setDownloadTemplates(true);

		$configMock = $this->getMockClass('\Store_Config', array('get'));
		$configMock::staticExpects($this->any())
			->method('get')
			->will($this->returnCallback(function () {
				$args = func_get_args();
				if ($args[0] == 'TemplateURL') {
					return 'http://test.com';
				} else if ($args[0] == 'DisableTemplateDownloading') {
					return false;
				}
			}));

		$httpMock = $this->getMockClass('\Interspire_Http', array('sendRequest'));
		$httpMock::staticExpects($this->any())
			->method('sendRequest')
			->with($this->equalTo('http://test.com'))
			->will($this->returnValue($this->getXmlTemplatesSample()));

		$this->builderTemplates->setConfigClass($configMock);
		$this->builderTemplates->setHttpClass($httpMock);
		$this->builderTemplates->setChmod(function () {}); // chmod only works with real files

		$expected = array(
			array(
				'id' => 'Bedlam',
				'name' => 'Bedlam',
				'color' => 'Carnelian',
				'preview' => 'http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian_thumb.jpg',
				'previewFull' => 'http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian.jpg',
				'features' => 0,
				'isInstalled' => false,
			),
		);
		$actual = $this->builderTemplates->listDownloadableTemplates();
		$this->assertEquals($expected, $actual);
	}

	private function getXmlTemplatesSample()
	{
		return '
			<templates>
				<template>
					<id>Bedlam</id>
					<name>Bedlam</name>
					<version>10.0</version>
					<colors>
						<color>
							<name>Carnelian</name>
							<hex>transparent</hex>
							<preview>http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian_thumb.jpg</preview>
							<previewFull>http://www.buildertemplates.com/isc/templates/previews/Bedlam_carnelian.jpg</previewFull>
						</color>
					</colors>
				</template>
			</templates>
		';
	}

}
