<?php

namespace Unit\Controllers;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Store_Config;

class LayoutControllerTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    public function setUp()
    {
        $filesystem = array(
            'root' => array(
                'Classic' => array(
                    'config.php' => 'a',
                    'Styles' => array(
                        'styles.css' => 'b'
                    ),
                    'default.html' => 'c'
                ),
                'theme2' => array(
                    'index.html' => 'd',
                    'my_dir1' => array(
                        'content' => array(
                            'foo.jpg' => 'e',
                        ),
                    )
                ),
                '__custom' => array(
                    'index.html' => 'f',
                    'my_dir1' => array(
                        'content' => array(
                            'foo.jpg' => 'g',
                        ),
                    )
                ),
                '__custom_empty' => array()
            )
        );

        $this->root = vfsStream::setup('/', 0755, $filesystem)->getChild('root');
    }

    public function testWipeCustomDirectory()
    {
        // mock layout as we can't construct ISC_ADMIN_LAYOUT
        $layout = $this->getMockBuilder('ISC_ADMIN_LAYOUT')
            ->setMethods(array('wipeCustomDirectory'))
            ->disableOriginalConstructor()
            ->getMock();

        // mock for Theme::getCustomThemePath() call
        $theme = $this->getMockClass('Theme', array('getCustomThemePath'));
        $theme::staticExpects($this->any())
            ->method('getCustomThemePath')
            ->will($this->returnValue(vfsStream::url('root/__custom')));
        $layout->setThemeClass($theme);

        // finally call (private) wipeCustomDirectory method
        $class = new \ReflectionClass('ISC_ADMIN_LAYOUT');
        $method = $class->getMethod('wipeCustomDirectory');
        $method->setAccessible(true);
        $method->invoke($layout);

        // test if template directories exist, but content has been deleted
        $this->assertTrue($this->root->hasChild('Classic'));
        $this->assertTrue($this->root->hasChild('theme2'));
        $this->assertTrue($this->root->hasChild('__custom'));
        $this->assertFalse($this->root->getChild('__custom')->hasChildren());
    }

    public function testCopyTemplateToCustom() {
        // mock layout as we can't construct ISC_ADMIN_LAYOUT
        $layout = $this->getMockBuilder('ISC_ADMIN_LAYOUT')
            ->setMethods(array('copyTemplateToCustom'))
            ->disableOriginalConstructor()
            ->getMock();

        // mock for Theme::getCustomThemePath() and Theme::getCurrentThemePath() call
        $theme = $this->getMockClass('Theme', array('getCustomThemePath', 'getCurrentThemePath'));
        $theme::staticExpects($this->any())
            ->method('getCustomThemePath')
            ->will($this->returnValue(vfsStream::url('root/__custom_empty')));
        $theme::staticExpects($this->any())
            ->method('getCurrentThemePath')
            ->will($this->returnValue(vfsStream::url('root/Classic')));
        $layout->setThemeClass($theme);

        // finally call (private) copyTemplateToCustom method
        $class = new \ReflectionClass('ISC_ADMIN_LAYOUT');
        $method = $class->getMethod('copyTemplateToCustom');
        $method->setAccessible(true);
        $method->invoke($layout);

        // test if it copied allowed files
        $custom = $this->root->getChild('__custom_empty');
        $this->assertTrue($custom->hasChild('Styles'));
        $this->assertTrue($custom->getChild('Styles')->hasChild('styles.css'));
        $this->assertTrue($custom->hasChild('default.html'));
        // test if it did NOT copy disallowed file extension
        $this->assertFalse($custom->hasChild('config.php'));
    }

}
