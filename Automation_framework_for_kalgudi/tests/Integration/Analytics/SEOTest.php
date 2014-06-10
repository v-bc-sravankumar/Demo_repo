<?php

namespace Integration\Analytics;

use \Analytics\SEO;

class SEOTest extends \PHPUnit_Framework_TestCase
{
    public function testParseReferer_Google()
    {
        $referer = "http://www.google.com/";
        $this->assertEquals('Google', SEO::parseReferer($referer));
    }

    public function testParseReferer_NotFound()
    {
        $referer = "http://www.newsearchengine.com/";
        $this->assertEquals('http://www.newsearchengine.com/', SEO::parseReferer($referer));
    }

    public function testParseReferer_Empty()
    {
        $referer = "";
        $this->assertEquals('', SEO::parseReferer($referer));
    }

    public function testParseReferer_Null()
    {
        $referer = null;
        $this->assertEquals(null, SEO::parseReferer($referer));
    }

    public function testGetDomainType_NoAlternateUrls()
    {
        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array('SERVER_NAME' => \Store_Config::get('ShopPath'))
        );
        $this->assertEquals('primary', SEO::getDomainType($request));

        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array('SERVER_NAME' => 'https://foo.com/')
        );
        $this->assertEquals('primary', SEO::getDomainType($request));
    }

    public function testGetDomainType_AlternateUrls()
    {
        $backup = \Store_Config::get('AlternateUrls');
        $urls = array('https://foo.com/');
        \Store_Config::override('AlternateUrls', $urls);

        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array('SERVER_NAME' => \Store_Config::get('ShopPath'))
        );
        $this->assertEquals('primary', SEO::getDomainType($request));

        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array('SERVER_NAME' => 'https://foo.com/')
        );
        $this->assertEquals('alternate', SEO::getDomainType($request));

        \Store_Config::override('AlternateUrls', $backup);
    }

    public function assertTrackBotClosure_Google($event)
    {
        $expected = array(
            "user_agent" => "Googlebot/2.1 (+http://www.googlebot.com/bot.html)",
            "bot_name" => "Googlebot",
            "domain_type" => "primary",
            "is_robots_ssl_customised" => true
        );

        $this->assertEquals($expected, $event->data);
        $event->stopPropagation();
        \Interspire_Event::unbind(\Store_Event::EVENT_SERVER_PAGE_LOAD, array($this, 'assertTrackBotClosure_Google'));
    }

    public function testTrackBot_Google()
    {
        \Interspire_Event::bind(array(\Store_Event::EVENT_SERVER_PAGE_LOAD), array($this, 'assertTrackBotClosure_Google'));

        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array(
                'SERVER_NAME' => 'http://foo.com/',
                'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
            )
        );
        SEO::trackBot($request);
    }

    public function assertTrackBotClosure_Other($event)
    {
        $expected = array(
            "user_agent" => "MyBot/2.1 (+http://www.mybot.com/bot.html)",
            "bot_name" => "Other",
            "domain_type" => "primary",
            "is_robots_ssl_customised" => true
        );

        $this->assertEquals($expected, $event->data);
        $event->stopPropagation();
        \Interspire_Event::unbind(\Store_Event::EVENT_SERVER_PAGE_LOAD, array($this, 'assertTrackBotClosure_Other'));
    }

    public function testTrackBot_UnkownBot()
    {
        \Interspire_Event::bind(array(\Store_Event::EVENT_SERVER_PAGE_LOAD), array($this, 'assertTrackBotClosure_Other'));

        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array(
                'SERVER_NAME' => 'http://foo.com/',
                'HTTP_USER_AGENT' => 'MyBot/2.1 (+http://www.mybot.com/bot.html)',
            )
        );
        SEO::trackBot($request);
    }

    public function testTrackSearchReferer_NoReferer()
    {
        $request = new \Interspire_Request();
        $this->assertNull(SEO::trackSearchReferer($request));
    }

    public function assertTrackSearchRefererClosure_Google($event)
    {
        $expected = array(
            "referer_url" => "http://www.google.com/",
            "referer" => "Google",
            "domain_type" => "primary",
        );

        $this->assertEquals($expected, $event->data);
        $event->stopPropagation();
        \Interspire_Event::unbind(\Store_Event::EVENT_SEARCH_HIT, array($this, 'assertTrackSearchRefererClosure_Google'));
    }

    public function testTrackSearchReferer_Google()
    {
        \Interspire_Event::bind(array(\Store_Event::EVENT_SEARCH_HIT), array($this, 'assertTrackSearchRefererClosure_Google'));
        $request = new \Interspire_Request(
            array(),
            array(),
            array(),
            array(
                'HTTP_REFERER' => 'http://www.google.com/',
            )
        );
        SEO::trackSearchReferer($request);
    }
}