<?php

class Unit_General extends PHPUnit_Framework_TestCase
{
    public function testRedirectProvider()
    {
        return array(
            array('/test', '/test?useTheme=test&siteColor=test'),
            array('/test?ToDo=test', '/test?ToDo=test&useTheme=test&siteColor=test'),
        );

    }
    /**
     * @dataProvider testRedirectProvider
     */
    public function testRedirect($redirectTo, $expected)
    {
        $request = new Interspire_Request(array(
            'useTheme' => 'test',
            'siteColor' => 'test',
        ));

        $url = constructRedirectUrl($redirectTo, $request);
        $this->assertEquals(
            $expected,
            $url
        );
    }
}