<?php

namespace Integration\Job\Search\Index;

use Job\Search\Index\IndexDocumentsJob;
use Store_Brand;
use Content\Blog\Post;

/**
 * @group nosample
 */
class IndexDocumentsJobTest extends \PHPUnit_Framework_TestCase
{
    private $brands;
    private $posts;

    private function createBrand()
    {
        $brand = new Store_Brand();
        $brand->setName('Brand ' . time() . rand(100, 1000));
        $brand->save();

        $this->brands[] = $brand;

        return $brand->getId();
    }

    private function createBrands()
    {
        return array(
            $this->createBrand(),
            $this->createBrand(),
            $this->createBrand(),
        );
    }

    private function createPost()
    {
        $post = new Post();
        $post->setTitle('Post ' . time() . rand(100, 1000));
        $post->save();

        $this->posts[] = $post;

        return $post->getId();
    }

    private function createPosts()
    {
        return array(
            $this->createPost(),
            $this->createPost(),
            $this->createPost(),
        );
    }

    public function tearDown()
    {
        foreach ($this->brands as $brand) {
            $brand->delete();
        }

        foreach ($this->posts as $post) {
            $post->delete();
        }
    }

    public function testRun()
    {
        $brands = $this->createBrands();
        $posts = $this->createPosts();

        $ids = array(
            'brand' => $brands,
            'post'  => $posts,
        );

        $strategy = $this->getMock('\Bigcommerce\SearchClient\IndexStrategy\IndexStrategyInterface');
        $strategy
            ->expects($this->at(0))
            ->method('bulkIndexDocuments')
            ->with($this->callback(function ($documents) use ($brands) {
                foreach ($documents as $document) {
                    if ($document->getType() !== 'brand') {
                        return false;
                    }

                    if (!in_array($document->getId(), $brands)) {
                        return false;
                    }
                }
                return true;
            }));

        $strategy
            ->expects($this->at(1))
            ->method('bulkIndexDocuments')
            ->with($this->callback(function ($documents) use ($posts) {
                foreach ($documents as $document) {
                    if ($document->getType() !== 'post') {
                        return false;
                    }

                    if (!in_array($document->getId(), $posts)) {
                        return false;
                    }
                }
                return true;
            }));

        $backgroundStrategy = $this->getMockBuilder('\Store\Search\IndexStrategy\BackgroundIndexStrategy')
            ->disableOriginalConstructor()
            ->getMock();

        $args = array(
            'ids' => $ids,
        );

        $job = $this->getMock('\Job\Search\Index\IndexDocumentsJob', array('getIndexStrategy', 'getBackgroundStrategy'));
        $job
            ->expects($this->any())
            ->method('getIndexStrategy')
            ->will($this->returnValue($strategy));

        $job
            ->expects($this->any())
            ->method('getBackgroundStrategy')
            ->will($this->returnValue($backgroundStrategy));

        $job->run($args);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No document ids given.
     */
    public function testRunWithNoIdsThrowsException()
    {
        $job = new IndexDocumentsJob();
        $job->run(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type "foo".
     */
    public function testRunWithInvalidTypeThrowsException()
    {
        $job = new IndexDocumentsJob();
        $job->run(array(
            'ids' => array(
                'foo' => array(1,2,3),
            ),
        ));
    }
}
