<?php
namespace Unit\Lib\Db\Tools;

use \PHPUnit_Framework_TestCase as TestCase;
use Db\Tools\TableDumper;
use stdClass;

class TableDumperTest extends TestCase
{

    public function testDumpToArrayFormattedAsExpected()
    {

        $row['id'] = 1;
        $row['field1'] = 'value1';
        $row['field2'] = '';

        $db = $this->getMock('Db_Mysql', array('query', 'fetch'));

        $db
            ->expects($this->once())
            ->method('query')
            ->with($this->equalTo('SELECT * FROM `table_name` WHERE this="that" AND another<=1'))
            ->will($this->returnValue(true));

        $db
            ->expects($this->any())
            ->method('fetch')
            ->will($this->onConsecutiveCalls($row, false));

        $loader = new TableDumper($db);
        $data = $loader->dumpToArray('table_name', array('this="that"', 'another<=1'));

        $expected = new \stdClass();
        $expected->id = 1;
        $expected->field1 = 'value1';
        $expected->field2 = '';

        $this->assertEquals($expected, array_pop($data));

    }

}