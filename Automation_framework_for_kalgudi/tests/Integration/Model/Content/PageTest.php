<?php

namespace Integration\Model\Content;

use Content\Page;
use Test\FixtureTest;

/**
 * @group nosample
 */
class PageTest extends FixtureTest
{
    public function testSaveDoesntOverwriteUrlIfCustomUrlInstanceNotSet()
    {
        $page = new Page();
        $page
            ->setTitle('Page')
            ->setCustomUrl('/test-page-url');

        $this->assertTrue($page->save(), 'Failed to save page');

        $id = $page->getId();

        $page = new Page();

        if (!$page->load($id)) {
            $this->fail('Failed to load page');
        }

        $page
            ->setTitle('New Title')
            ->save();

        $this->assertEquals('/test-page-url', $page->getCustomUrl()->getUrl());

        $page->delete();
    }

    public function testSaveDoesntLoadCustomUrlIfInstanceNotSet()
    {
        $page = new TestPage();
        $page->setTitle('Test');

        $this->assertTrue($page->save(), 'Failed to save page');
        $this->assertFalse($page->returnCustomUrl());

        $page->delete();
    }

    public function testCustomUrlDoesntGetSavedTwice()
    {
        $page = new Page();
        $page
            ->setTitle('Page')
            ->setCustomUrl('/test-page-url')
            ->save();

        $id = $page->getId();

        $page = new Page();
        $page->load($id);
        $page
            ->setTitle('New Title')
            ->setCustomUrl('/test-page-url-2')
            ->save();

        $query = \Store_CustomUrl::findByContent(\Store_CustomUrl::TARGET_TYPE_PAGE, $page->getId());
        $this->assertEquals(1, $query->count());

        $page->delete();
    }

    public function testFindByIds()
    {
        $pages = $this->loadFixture('pages');

        $randomElements = array_rand($pages, 3);

        $pageIds = array();
        $randomPages = array();
        foreach ($randomElements as $index) {
            $page = $pages[$index];

            $randomPages[] = $page;
            $pageIds[] = $page->getId();
        }

        $pagesByIds = Page::findByIds($pageIds);

        $this->assertEquals(3, $pagesByIds->count());

        foreach ($randomPages as $randomPage) {
            $pageFound = false;
            foreach ($pagesByIds as $page) {
                if ($page->getId() == $randomPage->getId()) {
                    $pageFound = true;
                    break;
                }
            }

            if (!$pageFound) {
                $this->fail('Page ' . $randomPage->getId() . ' was not found in result.');
            }

            $randomPageData = $randomPage->toArray();
            unset($randomPageData['pagensetleft']);
            unset($randomPageData['pagensetright']);

            $pageData = $page->toArray();
            unset($pageData['pagensetleft']);
            unset($pageData['pagensetright']);

            $this->assertEquals($randomPageData, $pageData);
        }
    }
}

class TestPage extends Page
{
    public function returnCustomUrl()
    {
        return $this->customUrl;
    }
}
