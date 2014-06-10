<?php

namespace Integration\Admin\Upgrade;

use ISC_ADMIN_UPGRADE;
use ISC_ADMIN_UPGRADE_BASE;

class ISC_ADMIN_UPGRADE_1234567890 extends ISC_ADMIN_UPGRADE_BASE {
    protected $backupTables = array('_unitTests_tableToBackup');
}

class BackupTest extends \Interspire_IntegrationTest
{
    /** @var ISC_ADMIN_UPGRADE_BASE|PHPUnit_Framework_MockObject_MockObject $upgrade */
    private $upgrade;

    private $tableName = '_unitTests_tableToBackup';

    private $tableRecords = array(
        array(1, 'foo', 1.11, 'The quick brown fox jumps over the lazy dog!'),
        array(2, 'bar', 0, 'AManAPlanACanalPanama'),
    );

    public function setup()
    {
        $this->upgrade = new ISC_ADMIN_UPGRADE_1234567890(new ISC_ADMIN_UPGRADE, $this->fixtures->getInstance());
    }

    public function testBackupTableDataProvider()
    {
        return array(
            array('_full_', '', $this->tableRecords),
            array('_partial_', "`id` = '1'", array_slice($this->tableRecords, 0, 1)),
        );
    }

    /**
     * @param string $tablePrefix
     * @param string $conditions
     * @param array $expectedRecords
     * @dataProvider testBackupTableDataProvider
     */
    public function testBackupTable($tablePrefix, $conditions, $expectedRecords)
    {
        $this->createTestTable();

        $result = $this->upgrade->backupTable($this->tableName, $conditions, $tablePrefix);

        $this->assertTrue($result);

        $newTable = $tablePrefix.$this->tableName;

        $result = $this->fixtures->db->Query("SELECT * FROM `" . $newTable . "`" . (!empty($conditions) ? ' WHERE ' . $conditions : ''));

        $this->assertEquals(count($expectedRecords), $this->fixtures->db->CountResult($result));

        $records = array();
        while ($row = $this->fixtures->db->Fetch($result)) {
            $records[] = array_values($row);
        }

        $this->assertEquals($expectedRecords, $records);

        $this->dropTable($this->tableName);
        $this->dropTable($newTable);
    }

    public function testDeletionIncludesPrefix()
    {
        // Mock out the database abstraction.
        $mock = $this->getMock('Db_Mysql', array('Query'));
        $mock->expects($this->once())
            ->method('Query')
            ->with('DROP TABLE IF EXISTS `_backup_1234567890__unitTests_tableToBackup`')
            ->will($this->returnValue(true));

        // Create a new instance of our upgrade class.
        $instance = new ISC_ADMIN_UPGRADE_1234567890(new ISC_ADMIN_UPGRADE, $mock);

        // Call the method.
        $instance->deleteBackupTables();
    }

    public function testTableRestoration()
    {
        $this->createTestTable();

        $query = 'SELECT * FROM ' . $this->tableName;

        // Check how many records we have.
        $count = $this->fixtures->db->CountResult($query);

        // Backup data.
        $this->upgrade->backupTables();

        // Now, nuke the products table.
        $this->fixtures->db->Query('TRUNCATE TABLE ' . $this->tableName);
        $this->assertEquals(0, $this->fixtures->db->CountResult($query));

        // Restore it.
        $this->upgrade->restoreBackupTables();

        // Verify.
        $this->assertEquals($count, $this->fixtures->db->CountResult($query));

        // Make sure the backup table was removed.
        $this->assertFalse($this->upgrade->TableExists('_1234567890_' . $this->tableName));

        $this->dropTable($this->tableName);
    }

    private function createTestTable()
    {
        if($this->fixtures->db->Query("CREATE TABLE IF NOT EXISTS `" . $this->tableName . "` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `col1` varchar(20),
            `col2` decimal(10,2),
            `col3` TEXT,
            PRIMARY KEY(`id`)
        )")) {
            foreach ($this->tableRecords as $row) {
                $this->fixtures->db->Query("INSERT INTO `" . $this->tableName . "` VALUES ('" . implode("', '", $this->fixtures->db->Quote($row)) . "')");
            }

            return;
        }

        $this->fail("Failed to create test table " . $this->tableName);
    }

    private function dropTable($tableName)
    {
        $this->fixtures->db->Query("DROP TABLE IF EXISTS `" . $tableName . "`");
    }
}
