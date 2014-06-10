<?php 
 
namespace Integration\Model\Products;

use Products\Video;
use Products\Videos;
use DomainModel\Query\Filter;
use DomainModel\Query\Pager;
use DomainModel\Query\Sorter;

class VideosTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->videos = new Videos();
	}	
	
	public function testSave()
	{
		$para =  array(
			'video_id' => '12345',
			'video_product_id' => 70,
			'video_sort_order' => 1,
			'video_title' => "my video",
	    );
		
		$video = new Video($para);
		$this->videos->save($video);
		
		$this->assertEquals('12345', $video->getId());
		$this->assertEquals('my video', $video->getTitle());
	}
	
	public function testDeleteVideo()
	{
		$para =  array(
			'video_id' => '12345',
			'video_product_id' => 70,
			'video_sort_order' => 1,
			'video_title' => "my video",
	    );

		$video = new Video($para);
		$result = $this->videos->deleteVideo($video);
		$this -> assertEquals(true,$result);
	}
	
	public function testFindById()
	{
		$para = array(
			'video_id' => "video12345",
			'video_product_id' => 70,
	  		'video_sort_order' => 1,
		  	'video_title' => "my video",
	  		'video_description' => "",
	  		'video_length' => "",		
		);

 		$this->videos->save(new Video($para));
        
        $video = $this->videos->findById('video12345');
		$this->assertEquals('video12345', $video->getId());
		$this->assertEquals('my video', $video->getTitle());
	}
	
	public function testFindMatching()
	{
		$myfilter = array("video_product_id:match"=>72,"video_sort_order:match"=>1);
        $filter = new Filter($myfilter); 
        $pager = new Pager();
        $sorter = new Sorter();

		$param = array();
		
		$param[0]=array(
	        "video_id" => "abc",
	        "video_product_id" => 72,
	        "video_sort_order" => 1,
	        "video_title" => "Google Nexus 1",
	        "video_description" =>'', 
	        "video_length" =>'', 
        );
			
		$param[1] = array(
	        "video_id" => "def",
	        "video_product_id" => 72,
	        "video_sort_order" => 1,
	        "video_title" => "Google Nexus 2",
	        "video_description" =>'', 
	        "video_length" =>'', 
        );
		
		$param[2] = array(
	        "video_id" => "ghi",
	        "video_product_id" => 72,
	        "video_sort_order" => 1,
	        "video_title" => "Google Nexus 3",
	        "video_description" =>'', 
	        "video_length" =>'', 
        );
		
		foreach ($param as $key => $value) {
			$video = new Video($value);
	 		$this->videos->save($video);
		}	
				           
		$result = $this->videos->findMatching($filter,$pager,$sorter);
		$collection = new \DataModel_PagedCollection($param, $pager);
		
		$this->assertEquals(3, count($collection));
	}

	public function testDeleteMatching() {
		$myfilter = array("video_product_id:match"=>72,"video_sort_order:match"=>1);
        $filter = new Filter($myfilter); 

		$param = array(
	              "video_id" => "abc",
	              "video_product_id" => 72,
	              "video_sort_order" => 1,
	              "video_title" => "Google Nexus 1",
	              "video_description" =>'', 
	              "video_length" =>'', 
                );
		
		$video = new Video($param);
 		$this->videos->save($video);
		$result = $this->videos->deleteMatching($filter);
		$this->assertEquals(true,$result);
	}
}
