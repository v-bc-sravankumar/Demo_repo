<?php

use Store\Preference;
use Store\Preference\Driver\Dummy;
use Preference\Browser\Base;

class Unit_App_Preference_Browser_BaseTest extends PHPUnit_Framework_TestCase
{
	private $preference = null;

	public function setUp()
	{
	 	$this->preference = new Preference(new Dummy());
		$this->preference
			->define('default', 'limit', 20)
			->define('default', 'sort', 'id')
			->define('default', 'direction', 'desc')
			->define('explicit', 'limit', 30)
			->define('explicit', 'sort', 'name')
			->define('explicit', 'direction', 'asc');
		$this->preference = new Base($this->preference);
	}

	private function makeGetRequest($get = array())
	{
		return new Interspire_Request($get, array(), array(), array(), array());
	}

	public function testGetPagerRequestValueFromNoPreference()
	{
		$this->preference = new Base(new Preference(new Dummy()));

		$pager = $this->preference->getPager($this->makeGetRequest(array('limit' => 10)));
		$this->assertEquals(10, $pager->limit());
	}

	public function testGetPagerDefaultValueFromDefaultPreference()
	{
		$pager = $this->preference->getPager($this->makeGetRequest());
		$this->assertEquals(20, $pager->limit());
	}

	public function testGetPagerDefaultValueFromExplicitPreference()
	{
		$pager = $this->preference->getPager($this->makeGetRequest(), 'explicit');
		$this->assertEquals(30, $pager->limit());
	}

	public function testGetPagerRequestValueFromDefaultPreference()
	{
		$pager = $this->preference->getPager($this->makeGetRequest(array('limit' => 10)));
		$this->assertEquals(10, $pager->limit());

		$pager = $this->preference->getPager($this->makeGetRequest());
		$this->assertEquals(10, $pager->limit());
	}

	public function testGetPagerRequestValueFromExplicitPreference()
	{
		$pager = $this->preference->getPager($this->makeGetRequest(array('limit' => 100, 'preference' => 'explicit')));
		$this->assertEquals(100, $pager->limit());

		$pager = $this->preference->getPager($this->makeGetRequest(array('preference' => 'explicit')));
		$this->assertEquals(100, $pager->limit());
	}

	public function testGetSorterRequestValueFromNoPreference()
	{
		$this->preference = new Base(new Preference(new Dummy()));
		$sorter = $this->preference->getSorter($this->makeGetRequest(array('sort'=> 'code', 'direction' => 'asc')));
		$this->assertEquals('code', $sorter->field());
		$this->assertEquals('asc', $sorter->direction());
	}

	public function testGetSorterDefaultValueFromDefaultPreference()
	{
		$sorter = $this->preference->getSorter($this->makeGetRequest());
		$this->assertEquals('id', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());
	}

	public function testGetSorterDefaultValueFromExplicitPreference()
	{
		$sorter = $this->preference->getSorter($this->makeGetRequest(), 'explicit');
		$this->assertEquals('name', $sorter->field());
		$this->assertEquals('asc', $sorter->direction());
	}

	public function testGetSorterRequestValueFromDefaultPreference()
	{
		$sorter = $this->preference->getSorter($this->makeGetRequest(array('sort' => 'date', 'direction' => 'desc')));
		$this->assertEquals('date', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());

		$sorter = $this->preference->getSorter($this->makeGetRequest());
		$this->assertEquals('date', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());
	}

	public function testGetSorterRequestValueFromExplicitPreference()
	{
		$sorter = $this->preference->getSorter($this->makeGetRequest(array('sort' => 'date', 'direction' => 'desc', 'preference' => 'explicit')));
		$this->assertEquals('date', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());

		$sorter = $this->preference->getSorter($this->makeGetRequest(array('preference' => 'explicit')));
		$this->assertEquals('date', $sorter->field());
		$this->assertEquals('desc', $sorter->direction());
	}

}
