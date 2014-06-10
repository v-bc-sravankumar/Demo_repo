<?php

// @todo ISC-2841 expand this into a proper ModelLike_TestCase class

class Unit_Lib_Store_CustomUrl extends Interspire_IntegrationTest
{
	public $deleteOnTearDown = array();

	public function tearDown()
	{
		while ($id = array_pop($this->deleteOnTearDown)) {
			Store_CustomUrl::find($id)->deleteAll();
		}

		parent::tearDown();
	}

	public function testGenerateUrlSuffixes ()
	{
		// create some known existing urls

		$random = mt_rand(1000000, 9999999);

		$model = new Store_CustomUrl;
		$model
			->setUrl('/products/' . $random . '.html')
			->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
			->setTargetId(1);
		$this->assertTrue($model->save(), 'failed to save model (1)');
		$this->deleteOnTearDown[] = $model->getId();

		$model = new Store_CustomUrl;
		$model
			->setUrl('/products/' . $random . '-1.html')
			->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
			->setTargetId(1);
		$this->assertTrue($model->save(), 'failed to save model (2)');
		$this->deleteOnTearDown[] = $model->getId();

		$model = new Store_CustomUrl;
		$model
			->setUrl('/products/' . $random . '-2.html')
			->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
			->setTargetId(1);
		$this->assertTrue($model->save(), 'failed to save model (3)');
		$this->deleteOnTearDown[] = $model->getId();

		$model = new Store_CustomUrl;
		$model
			->setUrl('/products/' . $random . '-4.html')
			->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
			->setTargetId(1);
		$this->assertTrue($model->save(), 'failed to save model (4)');
		$this->deleteOnTearDown[] = $model->getId();

		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/products/%productname%.html',
			'replacements' => array(
				'productname' => $random,
			),
		);

		// BIG-1018 with the optimization of using binary search to find the next suffix
		// this will return produce -5 instead of -3.
		$expected = '/products/' . $random . '-5.html';
		$this->assertSame($expected, Store_CustomUrl::generateUrl($options));
	}

	public function testSavingLongUrls ()
	{
		$urls = array(
			// column length test
			'/foo/bar/baz/abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuv/',

			// index length test 1/2
			'/products/Kerah-Shmittah-----%D7%A7%D7%A8%D7%90-%D7%A9%D7%9E%D7%99%D7%98%D7%94%252d%D7%A9%D7%99%D7%97%D7%95%D7%AA-%D7%94%D7%92%D7%95%D7%AA-%D7%A2%D7%9C-%D7%A9%D7%A0%D7%AA-%D7%94%D7%A9%D7%9E%D7%99%D7%98%D7%94%252d%D7%AA%D7%95%D7%9C%D7%93%D7%95%D7%AA%D7%99%D7%94%252d%D7%9E%D7%A6%D7%95%D7%95%D7%AA%D7%99%D7%94-%D7%95%D7%94%D7%9C%D7%9B%D7%95%D7%AA%D7%99%D7%94.html',

			// index length test 2/2
			'/products/Kerah-Shmittah-----%D7%A7%D7%A8%D7%90-%D7%A9%D7%9E%D7%99%D7%98%D7%94%252d%D7%A9%D7%99%D7%97%D7%95%D7%AA-%D7%94%D7%92%D7%95%D7%AA-%D7%A2%D7%9C-%D7%A9%D7%A0%D7%AA-%D7%94%D7%A9%D7%9E%D7%99%D7%98%D7%94%252d%D7%AA%D7%95%D7%9C%D7%93%D7%95%D7%AA%D7%99%D7%94%252d%D7%9E%D7%A6%D7%95%D7%95%D7%AA%D7%99%D7%94-%D7%95%D7%94%D7%9C%D7%9B%D7%95%D7%AA%D7%99%D7%94.htm',
		);

		// ensure they don't exist yet
		foreach ($urls as $url) {
			Store_CustomUrl::find("url = '" . $this->db->Quote($url) . "'")->deleteAll();
		}

		// check each one inserts OK
		foreach ($urls as $url) {
			$model = new Store_CustomUrl;
			$model
				->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
				->setTargetId(0)
				->setUrl($url);
			$this->assertTrue($model->save(), "failed to save custom URL '" . $url . "': " . $model->getDb()->GetErrorMsg());
			$this->deleteOnTearDown[] = $model->getId();
			$this->assertTrue($model->load(), "failed to save custom URL '" . $url . "' after saving");
		}
	}

	public function urlSavingDataProvider ()
	{
		$data = array();

		// --------------------------------------
		// obviously duplicate url should not be saved

		$urls = array();

		$urls[] = array(
			'url' => '/duplicate/urls/foo/bar',
			'expected' => true,
		);

		$urls[] = array(
			'url' => '/duplicate/urls/foo/bar',
			'expected' => false,
		);

		$data[] = array($urls);

		// --------------------------------------
		// this long url may not work well with a unique index with a partial, it should be saved OK

		$urls = array();

		$urls[] = array(
			'url' => '/foo/bar/baz/abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu-1',
			'expected' => true,
		);

		$urls[] = array(
			'url' => '/foo/bar/baz/abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu-2',
			'expected' => true,
		);

		$data[] = array($urls);

		// --------------------------------------

		return $data;
	}

	/**
	* @dataProvider urlSavingDataProvider
	*/
	public function testUrlSaving ($urls)
	{
		// ensure the URLs in this set don't exist yet
		foreach ($urls as $url) {
			Store_CustomUrl::find("url = '" . $this->db->Quote($url['url']) . "'")->deleteAll();
		}

		// check each one's insert against it's expected result
		foreach ($urls as $url) {
			$model = new Store_CustomUrl;
			$model
				->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
				->setTargetId(0)
				->setUrl($url['url']);

			try {
				$this->assertSame($url['expected'], $model->save(), "custom URL '" . $url['url'] . "' failed test: " . $model->getDb()->GetErrorMsg());
				// Should not throw an exception when save is expected to succeed.
				$this->assertTrue($url['expected']);
			} catch (Exception $ex) {
				// Should throw an exception when save is expected to fail.
				$this->assertFalse($url['expected']);
			}

			$this->deleteOnTearDown[] = $model->getId();

			if ($url['expected']) {
				// ensure a load works after save just incase
				$this->assertTrue($model->load(), "failed to load custom URL '" . $url['url'] . "' after saving");
			}
		}
	}
}
