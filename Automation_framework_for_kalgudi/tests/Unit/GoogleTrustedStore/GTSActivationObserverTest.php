<?php

use Feature\GoogleTrustedStores\GTSActivationObserver;

class GTSActivationObserverTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->config = \Store_Config::getInstance();
    }

    public function tearDown()
    {
        unset($this->config);
    }

    public function testGenerateHashEnabled()
    {
        $observerMock = $this->getMock('Feature\GoogleTrustedStores\GTSActivationObserver', array('getCurrentHash', 'generateHash'));
        $observerMock->expects($this->any())
        ->method('getCurrentHash')
        ->will($this->returnValue('0000'));

        \Store_Feature::override('GoogleTrustedStores', true);

        $observerMock->expects($this->once())->method('generateHash');
        $result = $observerMock->observe($this->config);
        $this->assertTrue($result);
    }

    public function testGenerateHashEnabled2()
    {
        $observerMock = $this->getMock('Feature\GoogleTrustedStores\GTSActivationObserver', array('getCurrentHash', 'generateHash'));
        $observerMock->expects($this->any())
        ->method('getCurrentHash')
        ->will($this->returnValue('9999'));

        \Store_Feature::override('GoogleTrustedStores', true);

        $observerMock->expects($this->never())->method('generateHash');
        $result = $observerMock->observe($this->config);
        $this->assertFalse($result);

    }

    public function testGenerateHashDisabled()
    {
        $observerMock = $this->getMock('Feature\GoogleTrustedStores\GTSActivationObserver', array('getCurrentHash', 'generateHash'));
        \Store_Feature::override('GoogleTrustedStores', false);

        $observerMock->expects($this->any())
        ->method('getCurrentHash')
        ->will($this->returnValue('8888'));

        $observerMock->expects($this->never())
        ->method('generateHash')
        ->will($this->returnValue(true));

        $result = $observerMock->observe($this->config);
        $this->assertFalse($result);
    }
}