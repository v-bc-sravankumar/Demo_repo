<?php

class Unit_Lib_DataModel_UnitOfWork extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();

		// the tests in this class rely on a clean transaction so commit anything in progress but ignore any error
		$this->fixtures->CommitAllTransactions();
	}

	public function testNewUnitOfWorkStartsTransaction ()
	{
		$this->assertSame(0, $this->fixtures->getTransactionCounter(), "transaction counter mismatch before creating instance");
		$work = new DataModel_UnitOfWork;
		$this->assertSame(1, $this->fixtures->getTransactionCounter(), "transaction counter mismatch after creating instance");
		$this->assertTrue($work->commitWork());
		$this->assertSame(0, $this->fixtures->getTransactionCounter(), "transaction counter mismatch after committing work");
	}
}
