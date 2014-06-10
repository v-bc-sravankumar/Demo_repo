<?php

use DataModel\UpdateQuery;

class Unit_DataModel_UpdateQueryTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorBuildsFromValuesArgument()
    {
        $db = $this->getMock('\Db_Base');

        $db->expects($this->any())->method('quote')->will($this->returnArgument(0));
        $db->expects($this->once())->method('updateQuery')->with('test_table', array('foo' => 'bar'), '');

        $query = new UpdateQuery('test_table', array('foo' => 'bar'), array(), $db);
        $query->execute();
    }

    public function testConstructorBuildsFromWhereArgument()
    {
        $db = $this->getMock('\Db_Base');
        $db->expects($this->any())->method('quote')->will($this->returnArgument(0));

        $query = new UpdateQuery('test_table', array('foo' => 'bar'), array('baz' => 'bat'), $db);

        $this->assertEquals(" baz = 'bat'", (string)$query);
    }

    public function testExecuteExecutesSqlUsingGivenDb()
    {
        $db = $this->getMock('\Db_Base');
        $db->expects($this->any())->method('quote')->will($this->returnArgument(0));
        $query = new UpdateQuery('test_table', array('foo' => 'bar'), array('baz' => 'bat'), $db);
        $db->expects($this->once())->method('updateQuery')->with('test_table', array('foo' => 'bar'), " baz = 'bat'");

        $query->execute();
    }
}
