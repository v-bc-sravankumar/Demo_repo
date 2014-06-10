<?php

namespace Integration\Language\Loader;

use Language\Loader\RedisLanguageLoader;
use Credis_Cluster;

class RedisLanguageLoaderTest extends \PHPUnit_Framework_TestCase
{
  private $cluster;
  private $namespace;
  private $namespacedKey;
  private $standardSet;
  private $standardKey;

  public function setUp()
  {
   try {
      $cluster = new Credis_Cluster(array(array('host' => '127.0.0.1', 'port' => 6379)));
      $cluster->client(0)->connect();

      $data = array(
        'namespacedVar' => 'foo',
      );
      $this->namespace = uniqid('test') . ':language';
      $this->namespacedKey = $this->namespace . ':en:namespacedlang';
      $cluster->set($this->namespacedKey, json_encode($data));

      $data = array(
        'standardVar' => 'bar',
      );
      $this->standardSet = uniqid('standardlang');
      $this->standardKey = 'en:' . $this->standardSet;
      $cluster->set($this->standardKey, json_encode($data));

      $this->cluster = $cluster;
    }
    catch (\CredisException $exception) {
      $this->markTestSkipped('Redis is not available at 127.0.0.1:6379');
    }
  }

  public function tearDown()
  {
    if ($this->cluster) {
      $this->cluster->del($this->namespacedKey);
      $this->cluster->del($this->standardKey);
    }
  }

  public function testLoadLanguageSetWithoutNamespace()
  {
    $loader = new RedisLanguageLoader($this->cluster);
    $definitions = $loader->loadDefinitionSet($this->standardSet, 'en');
    $this->assertArrayHasKey('standardVar', $definitions);
    $this->assertEquals('bar', $definitions['standardVar']);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadNamespacedLanguageSetWithoutNamespacedLoaderFails()
  {
    $loader = new RedisLanguageLoader($this->cluster);
    $loader->loadDefinitionSet('namespacedlang', 'en');
  }

  public function testLoadLanguageSetWithNamespace()
  {
    $loader = new RedisLanguageLoader($this->cluster, $this->namespace);
    $definitions = $loader->loadDefinitionSet('namespacedlang', 'en');
    $this->assertArrayHasKey('namespacedVar', $definitions);
    $this->assertEquals('foo', $definitions['namespacedVar']);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadUnnamespacedLanguageSetWithNamedspacedLoaderFails()
  {
    $loader = new RedisLanguageLoader($this->cluster, $this->namespace);
    $loader->loadDefinitionSet($this->standardSet, 'en');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadUnknownLanguageSetThrowsException()
  {
    $loader = new RedisLanguageLoader($this->cluster);
    $loader->loadDefinitionSet('foo', 'en');
  }
}
