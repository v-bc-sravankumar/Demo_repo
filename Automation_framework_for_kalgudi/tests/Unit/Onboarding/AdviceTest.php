<?php

use Onboarding\Advice;

class Unit_Onboarding_AdviceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @param $name
     * @param $isViewed
     * @param $id
     * @dataProvider adviceData
     */
    public function testAdviceConstructorCreatesExpectedAdvice($name, $isViewed, $id)
    {
        $advice = new Advice($name, $isViewed, $id);
        $this->assertEquals($name, $advice->getName());
        $this->assertEquals($isViewed, $advice->isViewed());
        $this->assertEquals($id, $advice->getId());
    }

    public function testGetNameEqualsNameSet()
    {
        $advice = new Advice('test');
        $advice->setName('test1');
        $this->assertEquals('test1', $advice->getName());
    }

    public function testIsViewedEqualsViewedSet()
    {
        $advice = new Advice('test');
        $advice->setViewed(true);
        $this->assertEquals(true, $advice->isViewed());
        $advice->setViewed(false);
        $this->assertEquals(false, $advice->isViewed());
    }

    public function testGetIdEqualsIdSet()
    {
        $advice = new Advice('test');
        $advice->setId(1);
        $this->assertEquals(1, $advice->getId());
    }

    public function testNonAlphanumericNamesAreFiltered()
    {
        $name = "test'";
        $expected = 'test';
        $advice = new Advice($name);
        $this->assertEquals($expected, $advice->getName());
    }

    public function adviceData()
    {
        return array(
            array('test1', false, null),
            array('test2', false, -1),
            array('test3', false, 0),
            array('test4', false, 1),
            array('test5', false, 2),
            array('test6', false, 100),
            array('test7', true, 100),
        );
    }
}
