<?php

namespace Unit\Lib\Store;

class FingerprintTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_keyStore = $this->getMockBuilder('\Predis\Client')
            ->setMethods(array('get', 'set'))
            ->getMock();

        $this->_statsd = $this->getMockBuilder('Store_Statsd')
            ->setMethods(array('increment'))
            ->getMock();

        $this->_fingerprint = new \Store\Fingerprint(
            $this->_keyStore,
            $this->_statsd,
            'namespace');
    }

    public function testGet()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('namespace:/test-path.gif'))
            ->will($this->returnValue(12345));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_READ_HIT));

        $result = $this->_fingerprint->get('/test-path.gif');

        $this->assertEquals(12345, $result);
    }

    public function testMinimumTimestamp()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('namespace:/test-path.gif'))
            ->will($this->returnValue(12345));

        $this->_statsd = $this->getMock('Store_Statsd');

        $this->_fingerprint = new \Store\Fingerprint(
            $this->_keyStore,
            $this->_statsd,
            'namespace',
            23456
            );

        $result = $this->_fingerprint->get('/test-path.gif');

        $this->assertEquals(23456, $result);
    }

    public function testFingerprintExceedsMinimumTimestamp()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('namespace:/test-path.gif'))
            ->will($this->returnValue(34567));

        $this->_statsd = $this->getMock('Store_Statsd');

        $this->_fingerprint = new \Store\Fingerprint(
            $this->_keyStore,
            $this->_statsd,
            'namespace',
            23456
            );

        $result = $this->_fingerprint->get('/test-path.gif');

        $this->assertEquals(34567, $result);
    }

    public function testGetNoLeadingSlash()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('namespace:/test-path.gif'))
            ->will($this->returnValue(12345));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_READ_HIT));

        $result = $this->_fingerprint->get('test-path.gif');

        $this->assertEquals(12345, $result);
    }

    public function testGetEmpty()
    {
        $this->_keyStore
            ->expects($this->never())
            ->method('get');

        $this->_statsd
            ->expects($this->never())
            ->method('increment');

        $result = $this->_fingerprint->get('');

        $this->assertEquals(false, $result);
    }

    public function testGetInvalid()
    {
        $this->_keyStore
            ->expects($this->never())
            ->method('get');

        $this->_statsd
            ->expects($this->never())
            ->method('increment');

        $result = $this->_fingerprint->get('/test-path.invalid');

        $this->assertEquals(false, $result);
    }

    public function testGetNonExistent()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('namespace:/test-path.gif'))
            ->will($this->returnValue(false));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_READ_MISS));

        $result = $this->_fingerprint->get('/test-path.gif');

        $this->assertFalse($result);
    }

    public function testSet()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('set')
            ->with($this->equalTo('namespace:/test-path.gif'), $this->greaterThan(0))
            ->will($this->returnValue(true));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_WRITE_SUCCESS));

        $result = $this->_fingerprint->set('/test-path.gif');

        $this->assertEquals(true, $result);
    }

    public function testSetError()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('set')
            ->with($this->equalTo('namespace:/test-path.gif'), $this->greaterThan(0))
            ->will($this->returnValue(false));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_WRITE_FAIL));

        $result = $this->_fingerprint->set('/test-path.gif');

        $this->assertEquals(false, $result);
    }

    public function testSetNoLeadingSlash()
    {
        $this->_keyStore
            ->expects($this->at(0))
            ->method('set')
            ->with($this->equalTo('namespace:/test-path.gif'), $this->greaterThan(0))
            ->will($this->returnValue(true));

        $this->_statsd
            ->expects($this->at(0))
            ->method('increment')
            ->with($this->equalTo(\Store\Fingerprint::METRIC_WRITE_SUCCESS));

        $result = $this->_fingerprint->set('test-path.gif');

        $this->assertEquals(true, $result);
    }

    public function testSetEmpty()
    {
        $this->_keyStore
            ->expects($this->never())
            ->method('set');

        $this->_statsd
            ->expects($this->never())
            ->method('increment');

        $result = $this->_fingerprint->set('');

        $this->assertTrue($result);
    }

    public function testSetInvalid()
    {
        $this->_keyStore
            ->expects($this->never())
            ->method('set');

        $this->_statsd
            ->expects($this->never())
            ->method('increment');

        $result = $this->_fingerprint->set('/test-path.invalid');

        $this->assertTrue($result);
    }
}
