<?php

class DomainModel_QueryFilterTest extends PHPUnit_Framework_TestCase
{

	public function testEmptyFilter()
	{
		$filter = new DomainModel\Query\Filter(array());

		$this->assertEquals(array(), $filter->criteria());
	}

	public function testEqualsCriteria()
	{
		$filter = new DomainModel\Query\Filter(array("name"=>"foo"));
		$name = new stdClass;
		$name->field = "name";
		$name->operator = "=";
		$name->value = "foo";

		$this->assertEquals(array($name), $filter->criteria());
	}

	public function testInvalidFieldFilter()
	{
		$filter = new DomainModel\Query\Filter(array("name"=>"foo", "title"=>"Foo"));
		$name = new stdClass;
		$name->field = "name";
		$name->operator = "=";
		$name->value = "foo";

		$this->assertEquals(array($name), $filter->criteria(array("name")));
	}

	/*public function testNotEqualsCriteria()
	{
		$filter = new DomainModel_Query_Filter(array("name:not"=>"foo"));
		$name = new stdClass;
		$name->field = "name";
		$name->operator = "!=";
		$name->value = "foo";

		$this->assertEquals($filter->criteria(), array($name));
	}*/

}