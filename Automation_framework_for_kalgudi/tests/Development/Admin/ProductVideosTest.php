<?php

PHPUnit_Extensions_Functional_TestCase::useLiveHttp('https://product-images-mark-rickerby.dev3.syd1bc.bigcommerce.net');

class Admin_ProductVideosTest extends PHPUnit_Extensions_Functional_TestCase
{
	// @todo: make this work
	protected function login($username, $password)
	{
		$this->post("/admin/index.php?ToDo=processLogin", array(
			"username" => $username,
			"password" => $password,
		));
	}

	public function testVideoSearch()
	{
		$this->setHeader("Accept", "application/json");
		$this->get("/admin/videos?q=bigcommerce");
		$this->assertStatus(200);
		$this->assertContentType("application/json");
        $this->assertText("bigcommercedotcom");
	}

    public function testAssignVideoToProduct()
    {
        $this->setHeader("Accept", "application/json");
        $this->post("/admin/products/2/videos", array(
            'id' => 'jJ5l5ls0hP4',
            'name' => 'Batman Maybe',
            'description' => 'You tell me',
            'sort_order' => 1,
        ));
        $this->assertStatus(200);
        $this->dumpBody();
        $this->assertContentType("application/json");
    }
}