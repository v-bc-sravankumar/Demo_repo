<?php

class Unit_ProductImages_Iterator extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testProductImageIteratorFindsCorrectData ()
	{
		// should be split up to multiple tests but this is quicker for now

		$result = $this->fixtures->Query("SELECT COUNT(*) AS `count` FROM [|PREFIX|]product_images");
		$row = $this->fixtures->Fetch($result);
		$row['count'] = (int)$row['count'];

		$this->assertTrue($row['count'] >= 1, 'There are no product images in the test data to test ISC_PRODUCT_IMAGE_ITERATOR against.');

		$iterator = new ISC_PRODUCT_IMAGE_ITERATOR();

		$counter = 0;
		foreach ($iterator as $image) {
			$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'Iterator returned a non-ISC_PRODUCT_IMAGE value at index ' . $counter . '.');
			$this->assertNotEquals(0, $iterator->key(), 'Iterator returned a zero value for key() at index ' . $counter . '.');
			$counter++;
		}
		$this->assertEquals($row['count'], $counter, 'Iterator looped over ' . $counter . ' images but there were ' . $row['count'] . ' images in the database.');

		$counter = 0;
		foreach ($iterator as $image) {
			$this->assertInstanceOf('ISC_PRODUCT_IMAGE', $image, 'On second round, Iterator returned a non-ISC_PRODUCT_IMAGE value at index ' . $counter . '.');
			$this->assertNotEquals(0, $iterator->key(), 'On second round, Iterator returned a zero value for key() at index ' . $counter . '.');
			$counter++;
		}
		$this->assertEquals($row['count'], $counter, 'On second round, Iterator looped over ' . $counter . ' images but there were ' . $row['count'] . ' images in the database - possible rewind() method issue?');

		$this->assertFalse(false);
	}
}
