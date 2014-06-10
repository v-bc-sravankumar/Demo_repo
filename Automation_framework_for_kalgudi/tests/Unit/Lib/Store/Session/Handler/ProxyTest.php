<?php

namespace Unit\Lib\Store\Session\Handler;

use \PHPUnit_Framework_TestCase;
use \Store\Session\Handler\Proxy;

class ProxyTest extends PHPUnit_Framework_TestCase
{
    private function mockHandler($method, $id, $return = true, $mock = null)
    {
        $mock = $mock ?: $this->getMock('\Store_Session_Handler_Redis');
        $mock->expects($this->once())
            ->method($method)
            ->with($this->equalTo($id))
            ->will($this->returnValue($return));

        return $mock;
    }

    private function mockWriteHandler($id, $data, $return, $mock = null)
    {
        $mock = $mock ?: $this->getMock('\Store_Session_Handler_Redis');
        $mock->expects($this->once())
            ->method('set')
            ->with($this->equalTo($id), $this->equalTo($data))
            ->will($this->returnValue($return));

        return $mock;
    }

    private function mockStatsd($events)
    {
        $events = is_array($events) ? $events : array($events);
        $mock = $this->getMock('\Store_Statsd');
        foreach($events as $index => $event) {
            $mock->expects($this->at($index))
                ->method('increment')
                ->with($this->equalTo($event));
        }
        return $mock;
    }

    public function testReadNotFound()
    {
        $proxy = new Proxy(
            $this->mockHandler('get', 'id', false),
            $this->mockHandler('get', 'id', false)
        );

        $this->assertFalse($proxy->get('id'));
    }

    public function testReadFromPrimary()
    {
        $proxy = new Proxy(
            $this->mockHandler('get', 'id', 'data'),
            null,
            $this->mockStatsd(Proxy::PRIMARY_READ_HIT)
        );

        $this->assertEquals('data', $proxy->get('id'));
    }

    public function testCopyOnReadFromSecondaryFail()
    {
        $proxy = new Proxy(
            $this->mockHandler('get', 'id', false,
                $this->mockWriteHandler('id', 'data', false)),
            $this->mockHandler('get', 'id', 'data'),
            $this->mockStatsd(array(
                Proxy::SECONDARY_READ_HIT,
                Proxy::PRIMARY_MIGRATE_FAIL,
            ))
        );

        $this->assertEquals('data', $proxy->get('id'));
    }

    public function testCopyOnReadFromSecondarySuccess()
    {
        $proxy = new Proxy(
            $this->mockHandler('get', 'id', false,
                $this->mockWriteHandler('id', 'data', true)),
            $this->mockHandler('get', 'id', 'data',
                $this->mockHandler('destroy', 'id', true)),
            $this->mockStatsd(array(
                Proxy::SECONDARY_READ_HIT,
                Proxy::PRIMARY_MIGRATE_SUCCESS,
            ))
        );

        $this->assertEquals('data', $proxy->get('id'));
    }

    public function testWrite()
    {
        $proxy = new Proxy($this->mockWriteHandler('id', 'data', true), null);
        $this->assertTrue($proxy->set('id', 'data'));
    }

    public function testDestory()
    {
        $proxy = new Proxy($this->mockHandler('destroy', 'id'), null);
        $this->assertTrue($proxy->destroy('id'));
    }

    public function testGarbageCollect()
    {
        $proxy = new Proxy($this->mockHandler('garbageCollect', 100), null);
        $this->assertTrue($proxy->garbageCollect(100));
    }

    public function testExistsOnPrimary()
    {
        $proxy = new Proxy($this->mockHandler('exists', 'id', true), null);
        $this->assertTrue($proxy->exists('id'));
    }

    public function testExistsOnSecondary()
    {
        $proxy = new Proxy(
            $this->mockHandler('exists', 'id', false),
            $this->mockHandler('exists', 'id', true)
        );
        $this->assertTrue($proxy->exists('id'));
    }

    public function testNotExists()
    {
        $proxy = new Proxy(
            $this->mockHandler('exists', 'id', false),
            $this->mockHandler('exists', 'id', false)
        );
        $this->assertFalse($proxy->exists('id'));
    }
}
