<?php

class Api_Products_ImagesTest extends Interspire_FunctionalTest
{

	public function testInvalidIdReturnsNotFound()
	{
		$this->authenticate()->get($this->makeUrl('/api/v2/products/234234/images'));
		$this->assertStatus(404);
	}
}