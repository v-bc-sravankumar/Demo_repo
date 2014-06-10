<?php

namespace Integration\Language\Loader;

use Language\Loader\FrontLanguageLoader;
use org\bovigo\vfs\vfsStream;
use Store_Config;

class FrontLanguageLoaderTest extends \PHPUnit_Framework_TestCase
{
  protected $customLanguageFile;

  public function __construct()
  {
    $this->customLanguageFile = ISC_BASE_PATH . '/language/en/custom_front_language.ini';
  }

  public function tearDown()
  {
    file_put_contents($this->customLanguageFile, '');
  }

  /**
   * tests that when we have customized front language and redis is not available, it will load from the custom_front_language.ini file.
   */
  public function testLoadCustomFrontLanguageFromFile()
  {
    $data = '
      customVar="foo"
    ';
    file_put_contents($this->customLanguageFile, $data);

    $loader = new FrontLanguageLoader();
    $definitions = $loader->loadDefinitionSet('front_language', 'en');

    $this->assertArrayHasKey('customVar', $definitions);
    $this->assertEquals('foo', $definitions['customVar']);
  }

  public function testLoadCustomFrontLanguageFromRedis()
  {
    unlink($this->customLanguageFile);
    $this->assertFileNotExists($this->customLanguageFile);

    $hostingId = uniqid('s');

    // ensure redis is available first and create some data
    try {
      $client = new \Predis\Client();
      $client->connect();

      $key = 's' . $hostingId . ':language:en:front_language';

      $data = array(
        'redisVar' => 'foobar',
      );

      $client->set($key, json_encode($data));
    }
    catch (\CredisException $exception) {
      $this->markTestSkipped('Redis is not available at 127.0.0.1:6379');
      return;
    }

    Store_Config::override('HostingId', $hostingId);
    Store_Config::override('Feature_RedisFrontLanguage', true);

    $loader = new FrontLanguageLoader();
    $definitions = $loader->loadDefinitionSet('front_language', 'en');

    $this->assertArrayHasKey('redisVar', $definitions);
    $this->assertEquals('foobar', $definitions['redisVar']);

    Store_Config::override('HostingId', Store_Config::getOriginal('HostingId'));
    Store_Config::override('Feature_RedisFrontLanguage', Store_Config::getOriginal('Feature_RedisFrontLanguage'));

    $client->del($key);
  }

  public function testCustomFrontLanguageOverridesFrontLanguage()
  {
    $data = '
      Product="foo"
    ';
    file_put_contents($this->customLanguageFile, $data);

    $loader = new FrontLanguageLoader();
    $definitions = $loader->loadDefinitionSet('front_language', 'en');

    $this->assertEquals('foo', $definitions['Product']);
  }

  public function testLoadMissingCustomFrontLanguageDoesntThrowException()
  {
    unlink($this->customLanguageFile);
    $this->assertFileNotExists($this->customLanguageFile);

    $loader = new FrontLanguageLoader();
    $definitions = $loader->loadDefinitionSet('front_language', 'en');

    $this->assertNotEmpty($definitions);
  }

  public function testLoadOtherLanguageFile()
  {
    $loader = new FrontLanguageLoader();
    $definitions = $loader->loadDefinitionSet('common', 'en');
    $this->assertArrayHasKey('OrderNumber', $definitions);
    $this->assertEquals('Order Number', $definitions['OrderNumber']);
  }
}
