<?php

namespace Integration\Controllers;

use Store_Config;

class LayoutControllerTest extends \PHPUnit_Framework_TestCase
{
    private $originalConfig;

    public function setUp()
    {
        $this->originalConfig = array(
            'enableMobileTemplateDevices' => Store_Config::get('enableMobileTemplateDevices'),
            'enableMobileTemplateDevicesMUI' => Store_Config::get('enableMobileTemplateDevicesMUI'),
            'enableMobileTemplate' => Store_Config::get('enableMobileTemplate'),
        );
    }

    public function tearDown()
    {
        foreach ($this->originalConfig as $setting => $value) {
            Store_Config::override($setting, $value);
        }
    }

    public function testSaveMobileTemplateSettings()
    {
        $layout = $this->getMockBuilder('ISC_ADMIN_LAYOUT')
                       ->disableOriginalConstructor()
                       ->setMethods(null)
                       ->getMock();

        $log = $this->getMockBuilder('Debug\LegacyLogger')
                    ->setMethods(array('logAdminAction'))
                    ->getMock();

        $layout->setLog($log);

        Store_Config::override('enableMobileTemplateDevices', null);
        Store_Config::override('enableMobileTemplateDevicesMUI', null);
        Store_Config::override('enableMobileTemplate', null);

        $post = array(
            'enableMobileTemplateDevicesMUI' => array('tablet'),
            'enableMobileTemplateDevices' => array('iphone', 'ipad'),
            'enableMobileTemplate' => true,
        );

        $request = new \Interspire_Request(null, $post);
        $value = $layout->saveMobileTemplateSettingsAction($request);

        $this->assertEquals(
            $post['enableMobileTemplateDevicesMUI'],
            Store_Config::get('enableMobileTemplateDevicesMUI')
        );

        $this->assertEquals(
            $post['enableMobileTemplateDevices'],
            Store_Config::get('enableMobileTemplateDevices')
        );

        $this->assertTrue(Store_Config::get('enableMobileTemplate'));
    }
}
