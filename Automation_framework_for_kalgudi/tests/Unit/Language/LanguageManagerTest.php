<?php

namespace Unit\Language;

use Language\LanguageManager;
use Language\Loader\ArrayLanguageLoader;

class LanguageManagerTest extends \PHPUnit_Framework_TestCase
{
  private $manager;

  public function setUp()
  {
    $this->manager = new LanguageManager('en', new ArrayLanguageLoader($this->getTestData()));
  }

  private function getTestData()
  {
    return array(
      'en' => array(
        'set' => array(
          'foo' => 'bar',
          'with_vars' => 'hello :var'
        ),
      ),
    );
  }

  public function testLoadKnownDefinitionSet()
  {
    $this->manager->load('set');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadUnknownDefinitionSetThrowsException()
  {
    $this->manager->load('unknown');
  }

  public function testGetForDefinedVar()
  {
    $this->manager->load('set');
    $this->assertEquals('bar', $this->manager->get('foo'));
  }

  public function testGetForUndefiniedVar()
  {
    $this->manager->load('set');
    $this->assertEquals('', $this->manager->get('undefined'));
  }

  public function testGetWithReplacements()
  {
    $this->manager->load('set');
    $this->assertEquals('hello world', $this->manager->get('with_vars', array('var' => 'world')));
  }

  public function testGetWithoutForcedReloadReturnsOriginalDefinition()
  {
    $loader = new ArrayLanguageLoader($this->getTestData());
    $manager = new LanguageManager('en', $loader);

    $manager->load('set');
    $loader->addLanguageSet('set', 'en', array('foo' => 'foo'));
    $manager->load('set');
    $this->assertEquals('bar', $manager->get('foo'));
  }

  public function testGetWithForcedReloadReturnsNewDefinition()
  {
    $loader = new ArrayLanguageLoader($this->getTestData());
    $manager = new LanguageManager('en', $loader);

    $manager->load('set');
    $loader->addLanguageSet('set', 'en', array('foo' => 'foo'));
    $manager->load('set', true);
    $this->assertEquals('foo', $manager->get('foo'));
  }

  public function testGetInstanceForAdmin()
  {
    $instance = LanguageManager::getInstance('admin');
    $this->assertInstanceOf('\Language\LanguageManager', $instance);
  }

  public function testGetInstanceForStorefront()
  {
    $instance = LanguageManager::getInstance('front');
    $this->assertInstanceOf('\Language\LanguageManager', $instance);
  }

  /**
   * @expectedException UnexpectedValueException
   */
  public function testGetInstanceForUnknownInstanceThrowsException()
  {
    $instance = LanguageManager::getInstance('foo');
    $this->assertInstanceOf('\Language\LanguageManager', $instance);
  }
}
