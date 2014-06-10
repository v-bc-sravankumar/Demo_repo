<?php

namespace Integration\Model\Content;

use Content\Blog\Posts;
use Content\Blog\Post;
use Content\Blog\Tags;
use Content\Pages;
use Content\Page;

class BlogPostsTest extends \PHPUnit_Framework_TestCase
{
	private $createdPosts = array();

	public function setUp()
	{
		$this->posts = new Posts();
		$this->pages = new Pages();
		$this->tags = new Tags();
	}

	public function tearDown()
	{
		foreach ($this->createdPosts as $post) {
			$post->delete();
		}
	}

	public function testCreateFirstPost()
	{
		$post = new Post();
		$post->setTitle("Welcome to Blogging!");
		$post->setBody("<p>My store sells more.</p>");
		$post->setCustomUrl("/blog/welcome");
		$post->setTags(array("Welcome"));
		$this->posts->save($post);

		$url = $post->getCustomUrl();

		$this->assertInstanceOf("Store_CustomUrl", $url);
		$this->assertEquals("/blog/welcome", $url->getUrl());
		$this->assertEquals(array("Welcome"), $post->getTags());

		return $post->getId();
	}

	/**
	 * @depends testCreatePageWithPlainContent
	 */
	public function testFindById($id)
	{
		$post = $this->posts->findById($id);

		$this->assertEquals("Welcome to Blogging!", $post->getTitle());

		return $id;
	}

	private function createPost()
	{
		$post = new Post();
		$post->setTitle('Post ' . rand());
		$post->setBody('Body');
		$post->setTags(array('Test', 'Blogging'));

		if (!$post->save()) {
			$this->fail('Failed to save post');
		}

		$this->createdPosts[] = $post;

		return $post;
	}

	private function assertPostsMatchExpectedIds($posts, $ids)
	{
		$postCount = 0;
		foreach ($posts as $post) {
			$postCount++;
			$this->assertContains($post->getId(), $ids);
		}

		$this->assertEquals(count($ids), $postCount);
	}

	public function testFindByIds()
	{
		$this->createPost();
		$this->createPost();

		$posts = array(
			$this->createPost(),
			$this->createPost(),
			$this->createPost(),
		);

		$ids = array();
		foreach ($posts as $post) {
			$ids[] = $post->getId();
		}

		$foundPosts = Posts::findByIds($ids);

		$this->assertPostsMatchExpectedIds($foundPosts, $ids);
	}

	public function testFindAll()
	{
		Post::find()->deleteAll();

		$posts = array(
			$this->createPost(),
			$this->createPost(),
			$this->createPost(),
			$this->createPost(),
		);

		$ids = array();
		foreach ($posts as $post) {
			$ids[] = $post->getId();
		}

		$foundPosts = Posts::findAll();

		$this->assertPostsMatchExpectedIds($foundPosts, $ids);
	}

	/**
	 * @depends testFindById
	 */
	public function testConnectedDefaultBlogPage($id)
	{
		$page = $this->pages->findByLayoutFile("blog.html")->first();

		$this->assertEquals("Blog", $page->getTitle());
		$this->assertEquals("/blog/", $page->getCustomUrl()->getUrl());
	}

	public function testCreateDraftFromObject()
	{
		$object = new \stdClass;
		$object->title = "My Draft Post";
		$object->body = "<p>I love deadlines.</p>";
		$object->custom_url = "/blog/my-draft-post";

		$post = Post::createFromObject($object);

		$this->assertEquals("My Draft Post", $post->getTitle());
		$this->assertInstanceOf("Store_CustomUrl", $post->getCustomUrl());
		$this->assertFalse($post->isPublished());
	}

	public function testUniqueGeneration()
	{
		$url = '/blog/test';
		$initial = Post::generateUniqueCustomUrl($url);
		sleep(2);
		$final = Post::generateUniqueCustomUrl($url);
		$this->assertNotEquals($initial, $final);
	}

	public function testFindCountAndTagsIndex()
	{
		$this->createPost();
		$this->createPost();
		$this->createPost();
		$this->createPost();

		$count = $this->posts->findCount(new \DomainModel\Query\Filter(array()));
		$this->assertEquals(4, $count);

		$tags = $this->tags->findAll();

		$this->assertCount(2, $tags);

		$this->assertEquals('Blogging', $tags[0]['tag']);
		$this->assertEquals('Test', $tags[1]['tag']);
	}
}
