<?php
class Unit_Store_NumberTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getIdTestData
     * @param unknown $value
     * @param unknown $expected
     */
    public function testIsId($value, $expected) {
        $actual = Store_Number::isId($value);
        $this->assertEquals($expected, $actual);
    }

    public function getIdTestData() {
		return array(
            array(1, true),
            array('1', true),
            array(0, false),
            array(0.1, false),
            array('999.99', false),
            array(-1, false),
            array('-1', false),
            array('one', false),
        );

    }
}