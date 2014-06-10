<?php

namespace Unit\Language\Loader;

use Language\Loader\IniLanguageLoader;
use org\bovigo\vfs\vfsStream;

class IniLanguageLoaderTest extends \PHPUnit_Framework_TestCase
{
  private $loader;

  public function setUp()
  {
    $structure = array(
      'language' => array(
        'en' => array(
          'common.ini',
          'admin' => array(
            'products.ini',
          ),
        ),
      ),
    );
    vfsStream::setup('/', 0755, $structure);

    $data = '
      Foo="bar"
      WithVars="hello :var"
    ';
    file_put_contents(vfsStream::url('language/en/common.ini'), $data);

    file_put_contents(vfsStream::url('language/en/bad.ini'), 'true=""');

    $this->loader = new IniLanguageLoader(array(vfsStream::url('language')));
  }

  public function testLoadKnownLanguageSet()
  {
    $expected = array(
      'Foo' => 'bar',
      'WithVars' => 'hello :var',
    );

    $definitions = $this->loader->loadDefinitionSet('common', 'en');
    $this->assertEquals($expected, $definitions);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadUnknownLanguageSetThrowsException()
  {
    $this->loader->loadDefinitionSet('foo', 'en');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadBadSyntaxLanguageThrowsException()
  {
    $this->loader->loadDefinitionSet('bad', 'en');
  }
}
