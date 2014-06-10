<?php

namespace Integration\Model\Content;

use Content\Pages;
use Content\Page;
use DomainModel\Query\Filter;
use DomainModel\Query\Pager;
use DomainModel\Query\Sorter;

class PagesTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->pages = new Pages();
	}

	public function testCreatePageWithPlainContent()
	{
		$page = new Page();
		$page->setTitle("About My Store");
		$page->setContent("<p>My store sells more.</p>");
		$page->setCustomUrl("/about/");
		$page->setStatus(1);
		$page->setType(0);
		$page->setLayoutFile("page.html");
		$page->setSort(4);

		$this->pages->save($page);

		$url = $page->getCustomUrl();

		$this->assertInstanceOf("Store_CustomUrl", $url);
		$this->assertEquals("/about/", $url->getUrl());

		return $page->getId();
	}

	/**
	 * @depends testCreatePageWithPlainContent
	 */
	public function testFindById($id)
	{
		$page = $this->pages->findById($id);

		$this->assertEquals("About My Store", $page->getTitle());
		$this->assertEquals("page.html", $page->getLayoutFile());
	}

	public function testFindMatchingTitle()
	{
		$filter = new Filter(array(
			"title" => "About My Store",
		));

		$pages = $this->pages->findMatching($filter, new Pager, new Sorter);

		$this->assertEquals(1, $pages->getTotalItems());
	}
}