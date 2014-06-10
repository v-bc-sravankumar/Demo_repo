<?php
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Console\Commands\Upgrade\CreateCommand;

/**
 * We create dummy classes for these tests because replacing the values of protected variables before the constructor
 * is called is painful.
 */
class ISC_ADMIN_UPGRADE_123 extends ISC_ADMIN_UPGRADE_BASE {
    protected $backupTables = array('products');
}
class ISC_ADMIN_UPGRADE_456 extends ISC_ADMIN_UPGRADE_BASE  {
    protected $backupTables = array('products' => 'prodname = "Foo Product"');
    protected function shouldBackupTables() { return false; }
}

class UpgradeBackupTablesTest extends PHPUnit_Framework_TestCase
{
    private $upgradeClass;
    private $db;

    public function setUp()
    {
        $this->upgradeClass = new ISC_ADMIN_UPGRADE();
        $this->db = $GLOBALS['ISC_CLASS_DB'];
    }

    public function testDeletionIncludesPrefix()
    {
        // Mock out the database abstraction.
        $mock = $this->getMock('Db_Mysql', array('Query'));
        $mock->expects($this->once())
            ->method('Query')
            ->with('DROP TABLE IF EXISTS `_backup_123_products`')
            ->will($this->returnValue(true));

        // Create a new instance of our upgrade class.
        $instance = new ISC_ADMIN_UPGRADE_123($this->upgradeClass, $mock);

        // Call the method.
        $instance->deleteBackupTables();
    }

    public function testTableRestoration()
    {
        $query = 'SELECT productid FROM products';

        // Instantiate.
        $instance = new ISC_ADMIN_UPGRADE_123($this->upgradeClass);

        // Check how many products we have.
        $count = $this->db->CountResult($query);

        // Backup data.
        $instance->backupTables();

        // Now, nuke the products table.
        $this->db->Query('TRUNCATE TABLE `products`');
        $this->assertEquals(0, $this->db->CountResult($query));

        // Restore it.
        $instance->restoreBackupTables();

        // Verify.
        $this->assertEquals($count, $this->db->CountResult($query));

        // Make sure the backup table was removed.
        $this->assertFalse($instance->TableExists('_backup_123_products'));
    }

    public function testConditionsAppliedToBackupTableContents()
    {
        $query = 'SELECT productid, prodname FROM _backup_456_products';

        // Instantiate.
        $instance = new ISC_ADMIN_UPGRADE_456($this->upgradeClass);

        // Create a suitable product.
        $this->db->Query('INSERT INTO products (prodname) VALUES ("Foo Product")');

        // Backup.
        $instance->backupTables();

        // Verify only 1 row was stored
        $this->assertEquals(1, $this->db->CountResult($query));
        $resource = $this->db->Query($query);
        $row = $this->db->Fetch($resource);
        $this->assertEquals('Foo Product', $row['prodname']);

        // Cleanup.
        $instance->deleteBackupTables();
        $this->db->Query('DELETE FROM products WHERE productid = ' . $row['productid']);
    }

    public function testConditionsAppliedWhenRestoringTableContents()
    {
        $query = 'SELECT productid FROM products';

        // Instantiate.
        $instance = new ISC_ADMIN_UPGRADE_456($this->upgradeClass);

        // Create a suitable product.
        $this->db->Query('INSERT INTO products (prodname) VALUES ("Foo Product")');

        // Count the products.
        $count = $this->db->CountResult($query);

        // Backup.
        $instance->backupTables();

        // Delete the new product.
        $this->db->Query('DELETE FROM products WHERE prodname = "Foo Product"');
        $this->assertEquals($count - 1, $this->db->CountResult($query));

        // Restore.
        $instance->restoreBackupTables();

        // Make sure the original products were left intact.
        $this->assertEquals($count, $this->db->CountResult($query));

        // Make sure the backup table was still deleted.
        $this->assertFalse($instance->TableExists('_backup_456_products'));
    }

    public function testBackupsCanBeSkipped()
    {
        // Instantiate.
        $instance = new ISC_ADMIN_UPGRADE_456($this->upgradeClass);

        // Ensure the backupTables method was not added to the list.
        $this->assertFalse(in_array('backupTables', $instance->steps));
    }
}
