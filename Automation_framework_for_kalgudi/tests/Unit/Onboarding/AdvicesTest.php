<?php

use Onboarding\Advices;
use Onboarding\Advice;

class Unit_Onboarding_AdvicesTest extends PHPUnit_Framework_TestCase
{

    protected function createMockKeyStoreForGet()
    {
        $mockKeyStore = $this->getMockBuilder('\Interspire_KeyStore')->setMethods(array('get'))->disableOriginalConstructor()->getMock();
        $mockKeyStore
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('onboarding_welcome:is_viewed'))
            ->will($this->returnValue('1'));
        return $mockKeyStore;
    }

    public function testFindByNameReturnsExpectedAdvice()
    {
        $mockKeyStore = $this->createMockKeyStoreForGet();

        $advices = new Advices($mockKeyStore);
        $advice = $advices->findByName('welcome');

        $expected = new Advice('welcome', true, 2);

        $this->assertEquals($expected, $advice);
    }

    public function testFindByIdReturnsExpectedAdvice()
    {
        $mockKeyStore = $this->createMockKeyStoreForGet();

        $advices = new Advices($mockKeyStore);
        $advice = $advices->findById(2);

        $expected = new Advice('welcome', true, 2);

        $this->assertEquals($expected, $advice);
    }

    public function testAllReturnsAllAdvices()
    {
        $mockKeyStore = $this->getMockBuilder('\Interspire_KeyStore')->setMethods(array('multiGet'))->disableOriginalConstructor()->getMock();
        $mockKeyStore
            ->expects($this->once())
            ->method('multiGet')
            ->with($this->equalTo(array(
                'onboarding_tab_prompt:is_viewed',
                'onboarding_welcome:is_viewed',
            )))
            ->will($this->returnValue(array(
                'onboarding_tab_prompt:is_viewed' => '1',
        )));

        $advices = new Advices($mockKeyStore);

        $advices = $advices->all();

        $expected = array(
            new Advice('tab_prompt', true, 1),
            new Advice('welcome', false, 2),
        );

        $this->assertEquals($expected, $advices);
    }

    public function testSavePersistsExpectedDataToKeyStore()
    {
        $mockKeyStore = $this->getMockBuilder('\Interspire_KeyStore')->setMethods(array('get', 'set'))->disableOriginalConstructor()->getMock();
        $mockKeyStore
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('onboarding_tab_prompt:is_viewed'))
            ->will($this->returnValue(null));
        $mockKeyStore
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('onboarding_tab_prompt:is_viewed'), $this->equalTo(1))
            ->will($this->returnValue(true));


        $advices = new Advices($mockKeyStore);
        $advice = $advices->findById(1);
        $advice->setViewed(true);
        $advices->save($advice);
    }
}
