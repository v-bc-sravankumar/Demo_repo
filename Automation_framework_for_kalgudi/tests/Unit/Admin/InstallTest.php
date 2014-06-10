<?php

class Unit_Interspire_InstallTest extends PHPUnit_Framework_TestCase
{
    const MARCH_1_2014_12AM=1393632000;
    const MARCH_10_2014_12AM=1394409600;
    const MARCH_3_2014_12PM=1393848000;
    const MARCH_5_2014_12PM=1394020800;

    public function testSplitBy20Percent()
    {
        $results = array();
        for ($i = 0; $i < 500; $i++) {
            $results[] = ISC_ADMIN_INSTALL::splitBy(0.2) ? 'a' : 'b';
        }
        $result = array_count_values($results);
        $this->assertLessThan($result['b'], $result['a']);
        $this->assertLessThanOrEqual(.3, $result['a'] / 500, 'Split was not within tolerances');
        $this->assertGreaterThanOrEqual(.1, $result['a'] / 500, 'Split was not within tolerances');
    }

    public function testPercentOfDateRange()
    {
        \Store_Config::override('InstallDate', self::MARCH_5_2014_12PM);
        $this->assertEquals(0.50, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_1_2014_12AM, self::MARCH_10_2014_12AM));
        $this->assertEquals(0, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_5_2014_12PM, self::MARCH_10_2014_12AM));
        $this->assertEquals(1, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_1_2014_12AM, self::MARCH_5_2014_12PM));
        \Store_Config::override('InstallDate', self::MARCH_1_2014_12AM);
        $this->assertEquals(0, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_5_2014_12PM, self::MARCH_10_2014_12AM));
        \Store_Config::override('InstallDate', self::MARCH_5_2014_12PM);
        $this->assertEquals(1, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_1_2014_12AM, self::MARCH_3_2014_12PM));
        $this->assertEquals(1, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_1_2014_12AM, self::MARCH_1_2014_12AM));
        $this->assertEquals(0, ISC_ADMIN_INSTALL::percentOfDateRange(self::MARCH_10_2014_12AM, self::MARCH_10_2014_12AM));
    }
}