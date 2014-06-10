<?php

class DataModel_SelectQueryTest extends PHPUnit_Framework_TestCase
{
    private function getSelectQuery()
    {
        return new DataModel_SelectQuery('', new Db_Bridge(new Db_Stub()));
    }

    public function testWhereClauseOnly()
    {
        $query = $this->getSelectQuery();
        $query->whereEquals("name", "foo")->whereNotEquals("title", "bar");

        $this->assertEquals(" WHERE name = 'foo' AND title != 'bar'", (string)$query);
    }

    public function testOrderByOnly()
    {
        $query = $this->getSelectQuery();
        $query->orderBy('name', 'desc');

        $this->assertEquals(" ORDER BY name DESC", (string)$query);
    }

	public function testOrConjunctionSkippedOnSinglePredicate()
	{
		$query = $this->getSelectQuery();
		$query->where('name', '=', 'foo', 'OR');

		$this->assertEquals(" WHERE name = 'foo'", (string)$query);
	}

	public function testOrConjunctionSeparates()
	{
		$query = $this->getSelectQuery();
		$query->where('name', '=', 'foo', 'OR')->where('title', '=', 'bar', 'OR');

		$this->assertEquals(" WHERE name = 'foo' OR title = 'bar'", (string)$query);
	}

	public function testSqlFunctionsInPredicates()
	{
		$query = $this->getSelectQuery();
		$query->where("CONCAT(name,title,url)", "=", 'foo');

		$this->assertEquals(" WHERE CONCAT(name,title,url) = 'foo'", (string)$query);
	}

	public function testLessThanEqualToOperator()
	{
		$query = $this->getSelectQuery();
		$query->where('foo', '<=', 'bar');

		$this->assertEquals(" WHERE foo <= 'bar'", (string)$query);
	}

	public function testGreaterThanEqualToOperator()
	{
		$query = $this->getSelectQuery();
		$query->where('bar', '>=', 'foo');

		$this->assertEquals(" WHERE bar >= 'foo'", (string)$query);
	}

    public function testWhereSubQuery()
    {
        $query = $this->getSelectQuery();
        $query->whereSubQuery('foo', 'SELECT id FROM bar');

        $this->assertEquals(" WHERE foo IN (SELECT id FROM bar)", (string)$query);
    }

    public function testWhereSubQueryAsAdditionalClause()
    {
        $query = $this->getSelectQuery();
        $query->whereEquals('bar', 'baz');
        $query->whereSubQuery('foo', 'SELECT id FROM bar', 'OR');

        $this->assertEquals(" WHERE bar = 'baz' OR foo IN (SELECT id FROM bar)", (string)$query);
    }

    public function testWhereNull()
    {
        $query = $this->getSelectQuery();
        $query->whereNull('foo');
        $this->assertEquals(" WHERE foo IS NULL", (string)$query);
    }

    public function testWhereNotNull()
    {
        $query = $this->getSelectQuery();
        $query->whereNotNull('foo');
        $this->assertEquals(" WHERE foo IS NOT NULL", (string)$query);
    }

    public function testWhereIn()
    {
        $query = $this->getSelectQuery();
        $query->whereIn('foo', array('hello', 'world'));
        $this->assertEquals(" WHERE foo IN ('hello', 'world')", (string)$query);
    }
}
