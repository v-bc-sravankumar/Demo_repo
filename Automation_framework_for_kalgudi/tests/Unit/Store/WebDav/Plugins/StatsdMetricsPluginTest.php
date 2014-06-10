<?php

namespace Unit\Store\WebDav\Plugins;

use PHPUnit_Framework_TestCase;
use Sabre\HTTP\Request;
use Store\WebDav\Plugins\StatsdMetricsPlugin;

class StatsdMetricsPluginTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->server = $this->getMock('Sabre\DAV\Server');
        $this->statsd = $this->getMock('Interspire_Statsd', array('increment'));
        $this->plugin = new StatsdMetricsPlugin($this->statsd);
        $this->plugin->initialize($this->server);
    }

    public function testLogsUnknownMethods()
    {
        $this->statsd->expects($this->once())
                     ->method('increment')
                     ->with('webdav.method_hits.UNKNOWN');

        $this->plugin->unknownMethodHandler('FOO', '/foo');
    }

    public function testLogsHits()
    {
        $this->statsd->expects($this->at(0))
                     ->method('increment')
                     ->with('webdav.hits');

        $this->plugin->beforeMethodHandler('PROPFIND', '/foo');
    }

    public function testLogsKnownMethods()
    {
        $this->statsd->expects($this->at(1))
                     ->method('increment')
                     ->with('webdav.method_hits.PROPFIND');

        $this->plugin->beforeMethodHandler('PROPFIND', '/foo');
    }

    public function getHeaderUsageTests()
    {
        $data = array();

        $data[] = array('HTTP_CONTENT_MD5', 'content_md5');
        $data[] = array('HTTP_IF_MATCH', 'if_match');
        $data[] = array('HTTP_IF_MODIFIED_SINCE', 'if_modified_since');
        $data[] = array('HTTP_IF_NONE_MATCH', 'if_none_match');
        $data[] = array('HTTP_IF_RANGE', 'if_range');
        $data[] = array('HTTP_IF_UNMODIFIED_SINCE', 'if_unmodified_since');
        $data[] = array('HTTP_RANGE', 'range');

        return $data;
    }

    /**
     * @dataProvider getHeaderUsageTests
     */
    public function testLogsHeaderUsage($header, $metric)
    {
        $this->server->httpRequest = new Request(array(
            $header => 'foo',
        ));

        $this->statsd->expects($this->at(2))
                     ->method('increment')
                     ->with('webdav.request_header_usage.' . $metric);

        $this->plugin->beforeMethodHandler('PROPFIND', '/foo');
    }
}
