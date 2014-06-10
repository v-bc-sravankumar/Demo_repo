<?php

class Unit_DomainModel_Repository_GiftCertificatesTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		$this->selectQuery = $this->getMock('\DataModel_SelectQuery', array('where'), array(), '', $callConstructor = false);

		$factory = new FakeQueryFactory($this->selectQuery);
		$this->repository = $this->getMock('\Repository\GiftCertificates', array('getResults'), $constructorArgs = array($factory));
	}

	public function testFindMatchingKeyword()
	{
		$this->selectQuery->expects($this->exactly(4))
					->method('where')
					->with(
						$this->logicalOr(
							$this->equalTo('giftcertcode'),
							$this->equalTo('giftcerttoemail'),
							$this->equalTo('giftcertfromemail'),
							$this->equalTo('CONCAT(c.custconfirstname, \' \', c.custconlastname)')
						),
						$this->equalTo(' LIKE '),
						$this->equalTo('%foo%')
					);

		$filter = new \DomainModel\Query\Filter(
			array('keyword' => 'foo')
		);
		$pager = new \DomainModel\Query\Pager(1, 50);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$this->repository->findMatching($filter, $pager, $sorter);
	}

	public function testFindMatchingDate()
	{
		$this->selectQuery->expects($this->once())
			->method('where')
			->with(
				$this->equalTo('giftcertpurchasedate'),
				$this->equalTo('<='),
				$this->equalTo(123)
			);

		$filter = new \DomainModel\Query\Filter(
			array('giftcertpurchasedate:max' => 123)
		);
		$pager = new \DomainModel\Query\Pager(1, 50);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$this->repository->findMatching($filter, $pager, $sorter);
	}

	public function testFindMatchingMultiple()
	{
		$this->selectQuery->expects($this->exactly(5))
					->method('where')
					->with(
						$this->logicalOr(
							$this->equalTo('giftcertcode'),
							$this->equalTo('giftcerttoemail'),
							$this->equalTo('giftcertfromemail'),
							$this->equalTo('CONCAT(c.custconfirstname, \' \', c.custconlastname)'),
							$this->equalTo('giftcertpurchasedate')
						),
						$this->logicalOr(
							$this->equalTo(' LIKE '),
							$this->equalTo('<=')
						),
						$this->logicalOr(
							$this->equalTo('%foo%'),
							$this->equalTo(123)
						)
					);

		$filter = new \DomainModel\Query\Filter(
			array('keyword' => 'foo', 'giftcertpurchasedate:max' => 123)
		);
		$pager = new \DomainModel\Query\Pager(1, 50);
		$sorter = new \DomainModel\Query\Sorter('id', 'asc');
		$this->repository->findMatching($filter, $pager, $sorter);
	}
}

class FakeQueryFactory
{
	private $_mock;

	public function __construct($mock)
	{
		$this->_mock = $mock;
	}

	public function create($select)
	{
		return $this->_mock;
	}
}

