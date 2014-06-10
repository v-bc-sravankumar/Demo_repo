<?php

class Admin_ProductImagesTest extends PHPUnit_Extensions_Functional_TestCase
{
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
	}
}