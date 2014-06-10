<?php

namespace Tests\Unit\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyNoIndexNoFollow_Success()
    {
        $config = array('Feature_CanonicalLink' => true, 'Feature_NoIndexNoFollow' => true, 'AlternateURLs' => array('http://foo.com'));
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $template = new \TEMPLATE('ISC_LANG', $settings);

        $request = new \Interspire_Request(array(), array(), array(), array('SERVER_NAME' => 'foo.com'));
        $template->applyNoIndexNoFollow($request);
        $this->assertEquals("<meta name='robots' content='noindex, nofollow' />", $template->_GetRobotsTag());
    }

    public function testApplyNoIndexNoFollow_FeatureOff()
    {
        $config = array('Feature_CanonicalLink' => true, 'Feature_NoIndexNoFollow' => false);
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $template = new \TEMPLATE('ISC_LANG', $settings);

        $template->applyNoIndexNoFollow(new \Interspire_Request());
        $this->assertEmpty($template->_GetRobotsTag());
    }

    public function testApplyNoIndexNoFollow_CanonicalLink_FeatureOff()
    {
        $config = array('Feature_CanonicalLink' => false, 'Feature_NoIndexNoFollow' => true);
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $template = new \TEMPLATE('ISC_LANG', $settings);

        $template->applyNoIndexNoFollow(new \Interspire_Request());
        $this->assertEmpty($template->_GetRobotsTag());
    }

    public function testApplyNoIndexNoFollow_AlternateURL_mismatch()
    {
        $config = array('Feature_CanonicalLink' => true, 'Feature_NoIndexNoFollow' => true, 'AlternateURLs' => array('http://store-foo.mybigcommerce.com'));
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $template = new \TEMPLATE('ISC_LANG', $settings);

        $request = new \Interspire_Request(array(), array(), array(), array('SERVER_NAME' => 'mystore.com'));
        $template->applyNoIndexNoFollow($request);
        $this->assertEmpty($template->_GetRobotsTag());
    }

    public function testApplyNoIndexNoFollow_hasCanonicalLink()
    {
        $config = array('Feature_CanonicalLink' => true, 'Feature_NoIndexNoFollow' => true, 'AlternateURLs' => array('http://store-foo.mybigcommerce.com'));
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $template = new \TEMPLATE('ISC_LANG', $settings);
        $template->SetCanonicalLink('http://foo.com');

        $request = new \Interspire_Request(array(), array(), array(), array('SERVER_NAME' => 'mystore.com'));
        $template->applyNoIndexNoFollow($request);
        $this->assertEmpty($template->_GetRobotsTag());
    }
}