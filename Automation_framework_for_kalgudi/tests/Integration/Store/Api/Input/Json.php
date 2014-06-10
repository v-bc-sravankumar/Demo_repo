<?php

require_once dirname(__FILE__) . '/../Input.php';

class Unit_Lib_Store_Api_Input_Json extends Unit_Lib_Store_Api_Input
{
	public function dataProvider()
	{
		$json = '
{
  "id":1,
  "name":"MacBook",
  "price":1999.99,
  "featured":true,
  "categories":[
	2,
	6,
	10,
	11
  ],
  "details":{
	"tax":54.21,
	"stock":5
  },
  "related":[
	4
  ],
  "images":[
	{
		"id":6,
		"file":"foo.jpg"
	},
	{
		"id":8,
		"file":"bar.png"
	}
  ],
  "videos":[
	{
		"id":10,
		"url":"http://www.youtube.com/watch?foo"
	}
  ],
  "long_number":"9102927002338349750071"
}
';

		$parser = new Store_Api_InputParser_Json();
		$input = $parser->parseInput($json);
		return array(array($input));
	}
}
