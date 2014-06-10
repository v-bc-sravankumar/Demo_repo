<?php

use DataModel\IdentityMap;
use Repository\Products;
use Store\Product;

class Integration_DomainModel_Repository_ProductDownloadsTest extends Interspire_IntegrationTest
{
	private static $productId;
	private static $productId2;
	private static $productHash = '0cc175b9c0f1b6a831c399e269772661';
	private static $productHash2 = '1dd286c8b102c7523dc89ba90943c345';
	private static $sorter;
	private static $pager;
	private static $repository;

	public static function setUpBeforeClass()
	{
		self::$sorter = new \DomainModel\Query\Sorter('id', 'desc');
		self::$pager = new DomainModel\Query\Pager(1, 10);
		self::$repository = new \Repository\ProductDownloads();
		$fixtures = Interspire_DataFixtures::getInstance();

		$data = array(
			'prodname' => 'test',
			'prodcatids' => '',
			'proddateadded' => time(),
			'prodlastmodified' => time(),
		);
		self::$productId = $fixtures->InsertQuery('products', $data);
		self::$productId2 = $fixtures->InsertQuery('products', array_merge($data, array('prodname' => 'unrelated product')));

		// downloads related to first (tested) product
		$fixtures->InsertQuery('product_downloads', array('productid' => self::$productId, 'downfile' => 'file_id_1.txt'));
		$fixtures->InsertQuery('product_downloads', array('productid' => self::$productId, 'downfile' => 'file_id_2.txt'));
		$fixtures->InsertQuery('product_downloads', array('productid' => self::$productId, 'downfile' => 'file_id_3.txt'));
		$fixtures->InsertQuery('product_downloads', array('prodhash' => self::$productId, 'downfile' => 'file_id_hash_1.txt'));
		$fixtures->InsertQuery('product_downloads', array('prodhash' => self::$productId, 'downfile' => 'file_id_hash_2.txt'));
		$fixtures->InsertQuery('product_downloads', array('prodhash' => self::$productHash, 'downfile' => 'file_hash_1.txt'));

		// downloads related to second product (shouldn't appear in results)
		$fixtures->InsertQuery('product_downloads', array('productid' => self::$productId2, 'downfile' => 'unrelated_1.txt'));
		$fixtures->InsertQuery('product_downloads', array('prodhash' => self::$productId2, 'downfile' => 'unrelated_2.txt'));
		$fixtures->InsertQuery('product_downloads', array('prodhash' => self::$productHash2, 'downfile' => 'unrelated_3.txt'));
	}

	public static function tearDownAfterClass()
	{
		$fixtures = Interspire_DataFixtures::getInstance();
		$fixtures->Query(sprintf("DELETE FROM product_downloads WHERE productid IN (%d, %d) OR prodhash IN (%d, %d, '%s', '%s')",
			self::$productId, self::$productId2, self::$productId, self::$productId2, self::$productHash, self::$productHash2
		));
		$fixtures->Query(sprintf("DELETE FROM products WHERE productid IN (%d, %d)", self::$productId, self::$productId2));
	}

	public function testFindByProductIdentifierFetchesDataProperly()
	{
		$productDownloadsById = self::$repository->findByProductIdentifier(self::$productId, false, self::$pager, self::$sorter);
		$productDownloadsByIdAsHash = self::$repository->findByProductIdentifier(self::$productId, true, self::$pager, self::$sorter);
		$productDownloadsByMd5AsHash = self::$repository->findByProductIdentifier(self::$productHash, true, self::$pager, self::$sorter);

		$this->assertEquals(3, count($productDownloadsById));
		$this->assertEquals(2, count($productDownloadsByIdAsHash));
		$this->assertEquals(1, count($productDownloadsByMd5AsHash));
	}

	public function testFindByProductIdentifierThrowsExceptionOnInvalidId()
	{
		$this->setExpectedException("InvalidArgumentException", "Can't use '" . self::$productHash . "' as productIdentifier.");
		self::$repository->findByProductIdentifier(self::$productHash, false, self::$pager, self::$sorter);
	}

	public function testFindByProductIdentifierThrowsExceptionOnNullInput()
	{
		$this->setExpectedException("InvalidArgumentException");
		self::$repository->findByProductIdentifier(null, false, self::$pager, self::$sorter);
	}

	public function testFindByProductIdentifierThrowsExceptionOnNonStringNonIntIdentifier()
	{
		$this->setExpectedException("InvalidArgumentException", "Can't use 'Array' as productIdentifier.");
		self::$repository->findByProductIdentifier(array('hello world'), true, self::$pager, self::$sorter);
	}
}
