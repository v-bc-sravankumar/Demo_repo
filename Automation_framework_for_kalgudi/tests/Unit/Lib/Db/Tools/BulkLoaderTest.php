<?php
namespace Unit\Lib\Db\Tools;

use \PHPUnit_Framework_TestCase as TestCase;
use Db\Tools\BulkLoader;
use stdClass;

class BulkLoaderTest extends TestCase
{

    public function testTransactionCompletesAndQueryFormattedAsExpected()
    {

        $row = new stdClass();
        $row->id = 1;
        $row->field1 = 'value';
        $row->field2 = '';
        $row->field3 = null;

        $data = new stdClass();
        $data->table_name[] = $row;

        $db = $this->getMock('Db_Mysql', array('StartTransaction', 'Query', 'CommitTransaction'));

        $db
            ->expects($this->once())
            ->method('StartTransaction');

        $db
            ->expects($this->once())
            ->method('Query')
            ->with($this->equalTo('INSERT LOW_PRIORITY INTO `table_name` (id,field1,field2,field3) VALUES ("1","value","","")'))
            ->will($this->returnValue(true));

        $db
            ->expects($this->once())
            ->method('CommitTransaction');

        $loader = new BulkLoader($db);
        $loader->loadFromArray($data);

    }

    public function testTransactionRollsBackAsExpected()
    {

        $row = new stdClass();
        $row->id = 1;
        $row->field1 = 'value';
        $row->field2 = '';
        $row->field3 = null;

        $data = new stdClass();
        $data->table_name[] = $row;

        $db = $this->getMock('Db_Mysql', array('StartTransaction', 'Query', 'CommitTransaction', 'RollbackTransaction'));

        $db
            ->expects($this->once())
            ->method('Query')
            ->will($this->returnValue(false));

        $db
            ->expects($this->never())
            ->method('CommitTransaction');

        $loader = new BulkLoader($db);
        $loader->loadFromArray($data);

    }

}
