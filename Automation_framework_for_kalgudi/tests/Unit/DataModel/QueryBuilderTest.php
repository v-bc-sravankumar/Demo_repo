<?php

class Unit_DataModel_QueryBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testWhereAcceptsStringArrayValue()
    {
        $db = $this->getMock('\Db_Base');

        $db->expects($this->once())->method('quote')->will($this->returnArgument(0));

        $queryBuilder = $this->getMockForAbstractClass('\DataModel\QueryBuilder',
            array('SELECT * FROM example', $db));

        $queryBuilder->where('col1', 'IN', array('value1', 'value2'));

        $this->assertEquals("SELECT * FROM example WHERE col1 IN ('value1', 'value2')", $queryBuilder->__toString());
    }
}