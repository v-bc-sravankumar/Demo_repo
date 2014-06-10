<?php

namespace Unit\Controllers;

class BloggingControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testBlogHomeCanonicalLink_Null()
    {
        $config = array('Feature_CanonicalLink' => false, 'ShopPathNormal' => 'http://foo.com');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $controller = new \Storefront\BloggingController(new \Interspire_Request(), $settings, new \stdClass());
        $this->assertNull($controller->getBlogHomeCanonicalLink());
    }

    public function testBlogHomeCanonicalLink_NonNull()
    {
        $config = array('Feature_CanonicalLink' => true, 'ShopPathNormal' => 'http://foo.com');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $controller = new \Storefront\BloggingController(new \Interspire_Request(), $settings, new \stdClass());
        $this->assertEquals($config['ShopPathNormal'].'/blog/', $controller->getBlogHomeCanonicalLink());
    }

    public function testBlogPostCanonicalLink_NonNull()
    {
        $config = array('Feature_CanonicalLink' => true, 'ShopPathNormal' => 'http://foo.com');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $request = new \Interspire_Request(array(), array(), array(), array('REQUEST_URI' => '/blog/hello-world/'));

        $controller = new \Storefront\BloggingController($request, $settings, new \stdClass());
        $this->assertEquals('http://foo.com/blog/hello-world/', $controller->getBlogPostCanonicalLink());
    }

    public function testBlogPostCanonicalLink_Null()
    {
        $config = array('Feature_CanonicalLink' => false, 'ShopPathNormal' => 'http://foo.com');
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $controller = new \Storefront\BloggingController(new \Interspire_Request(), $settings, new \stdClass());
        $this->assertNull($controller->getBlogPostCanonicalLink());
    }
}