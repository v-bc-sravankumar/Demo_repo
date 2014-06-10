<?php

class Unit_Lib_Interspire_Cache_Memcache extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->memcache = $this->getMock("Memcache", array(
            "set",
        ));
        $this->cache    = new Interspire_Cache_Memcache($this->memcache);
    }

    public function testNullExpiry()
    {
        $this->memcache->expects($this->once())
                       ->method("set")
                       ->with("foo", "bar", 0, 0)
                       ->will($this->returnValue(true));

        $this->cache->set("foo", "bar", null);
    }

    public function testZeroExpiry()
    {
        $this->memcache->expects($this->once())
                       ->method("set")
                       ->with("foo", "bar", 0, 0)
                       ->will($this->returnValue(true));

        $this->cache->set("foo", "bar", 0);
    }

    public function testLowExpiry()
    {
        $time = time();

        $this->memcache->expects($this->once())
                       ->method("set")
                       ->with("foo", "bar", 0, $this->greaterThanOrEqual($time + 1))
                       ->will($this->returnValue(true));

        $this->cache->set("foo", "bar", 1);
    }

    public function testHighExpiry()
    {
        // cache is only concerned with working with relative values

        $time = time();

        $this->memcache->expects($this->once())
                       ->method("set")
                       ->with("foo", "bar", 0, $this->greaterThanOrEqual($time + $time))
                       ->will($this->returnValue(true));

        $this->cache->set("foo", "bar", $time);
    }
}
