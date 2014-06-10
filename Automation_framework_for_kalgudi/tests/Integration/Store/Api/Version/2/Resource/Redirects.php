<?php
use Store\Redirect\Forward;
use Store\Redirect;

class Integration_Lib_Store_Api_Version_2_Resource_Redirects extends Interspire_IntegrationTest
{
	/** @var $resource Store_Api_Version_2_Resource_Redirects */
	private $resource = null;

	/** @var Interspire_DataFixtures */
	private static $dbFixture;

	protected function createRedirect($path, $type, $ref, $id = null)
	{
		$redirect = new Redirect();
		$redirect->setPath($path);
		$redirect->setForward(new Forward($type, $ref));
		$redirect->save();
		return $redirect;
	}

	public static function setUpBeforeClass()
	{
		self::$dbFixture = new Interspire_DataFixtures($useCache = false);
	}

	public function setUp()
	{
		self::$dbFixture->loadData('redirects');
		$this->resource = new Store_Api_Version_2_Resource_Redirects();
	}

	public function tearDown()
	{
		$this->resource = null;
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testGetActionFail()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('redirects', 999999999);
		$this->resource->getAction($request);
	}

	public function redirectProvider()
	{
		return array(
			/*   ID, TYPE (int),             TYPE NAME,  REF,        REF ERROR, PATH */
			array(1, Forward::TYPE_MANUAL,   'manual',   '/example', 'example', '/test_manual'),
			array(2, Forward::TYPE_BRAND,    'brand',    '1',        '2',       '/test_brand'),
			array(3, Forward::TYPE_CATEGORY, 'category', '1',        '2',       '/test_category'),
			array(4, Forward::TYPE_PAGE,     'page',     '1',        '2',       '/test_page'),
			array(5, Forward::TYPE_PRODUCT,  'product',  '1',        '2',       '/test_product'),
			array(6, Forward::TYPE_NEWS,     'news',     '1',        '2',       '/test_news'),
		);
	}

	protected function transformedProvider()
	{
		return array_map(function ($el) {
			$redirect = new Redirect();
			$redirect->setForward(new Forward($el[1], $el[3]));
			return array(
				'id' => $el[0],
				'path' => $el[5],
				'forward' => array(
					'type' => $el[2],
					'ref' => $el[3],
				),
				'url' => $redirect->getUrl(),
			);
		}, $this->redirectProvider());
	}

	/**
	 * If a redirect has an invalid reference, it will still be included it in the result, though no URL will be set
	 * @see BIG-6705
	 */
	public function testGetRedirectsWithInvalidReferences()
	{
		self::$dbFixture->loadData('invalid_redirects');
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));

		$result = $this->resource->getAction($request);
		$data = $result->getData();
		$this->assertNotEmpty($data);
		$this->assertCount(5, $data);

		foreach ($data as $redirect) {
			$this->assertArrayNotHasKey('url', $redirect);
		}
	}

	public function testRedirectsAreRemovedWhenTheirReferenceIsRemoved()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$initialState = $this->resource->getAction($request)->getData();
		$this->assertCount(6, $initialState);

		Interspire_Event::trigger(Store_Event::EVENT_PRODUCT_DELETED, array('id' => 1));
		Interspire_Event::trigger(Store_Event::EVENT_CATEGORY_DELETED, array('id' => 1));
		Interspire_Event::trigger(Store_Event::EVENT_BRAND_DELETED, array('id' => 1));
		Interspire_Event::trigger(Store_Event::EVENT_WEBSITE_DELETED_NEWS_ITEM, array('id' => 1));
		Interspire_Event::trigger(Store_Event::EVENT_WEBSITE_DELETED_WEB_PAGE, array('id' => 1));

		$finalState = $this->resource->getAction($request)->getData();
		$this->assertCount(1, $finalState);
		$this->assertEquals('manual', $finalState[0]['forward']['type']);
	}

	/**
	 * @dataProvider redirectProvider
	 */
	public function testGetActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$request->setUserParam('redirects', $id);

		$result = $this->resource->getAction($request);
		$data = $result->getData();
		$this->assertNotEmpty($data);

		$this->assertEquals($id, $data['id']);
		$this->assertEquals($path, $data['path']);
		$forward = $data['forward'];
		$this->assertEquals($typeName, $forward['type']);
		$this->assertEquals($ref, $forward['ref']);
	}

	public function testGetAllActionSuccess()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
		$result = $this->resource->getAction($request);
		$data = $result->getData();
		$this->assertEquals($this->transformedProvider(), $data);
	}

	/**
	 * @dataProvider redirectProvider
	 * @expectedException Store_Api_Exception_Request
	 * @expectedExceptionCode 400
	 */
	public function testPostActionFail($id, $type, $typeName, $ref, $refError, $path)
	{
		$json = json_encode(array(
			"path" => $path,
			"forward" => array(
				"type" => $type,
				"ref" => $refError,
			),
		));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$this->resource->postAction($request)->getData(true);
	}

	/**
	 * @dataProvider redirectProvider
	 */
	public function testPostActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$json = json_encode(array(
			"path" => $path . "_post",
			"forward" => array(
				"type" => $type,
				"ref" => $ref,
			),
		));

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
		$redirect = $this->resource->postAction($request)->getData(true);
		$this->assertNotEmpty($redirect);

		$forward = $redirect["forward"];
		$this->assertEquals($path . "_post", $redirect["path"]);
		$this->assertEquals($typeName, $forward["type"]);
		$this->assertEquals($ref, $forward["ref"]);

		Redirect::find($redirect['id'])->first()->delete();
	}

	/**
	 * @dataProvider redirectProvider
	 */
	public function testPutActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$json = json_encode(array(
			"path" => $path . "_put",
			"forward" => array(
				"type" => "manual",
				"ref" => "http://test.bigcommerce.com",
			),
		));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('redirects', $id);
		$redirect = $this->resource->putAction($request)->getData(true);
		$forward = $redirect['forward'];
		$this->assertNotEmpty($redirect);
		$this->assertEquals($path . '_put', $redirect['path']);
		$this->assertEquals('manual', $forward['type']);
		$this->assertEquals('http://test.bigcommerce.com', $forward['ref']);

		// Restore state
		$this->createRedirect($path, $type, $ref, $id);
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testPutActionFail()
	{
		$json = json_encode(array(
			"path" => "/invalid_put",
			"forward" => array(
				"type" => "manual",
				"ref" => "/test",
			),
		));
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
		$request->setUserParam('redirects', 999999999);
		$this->resource->putAction($request)->getData(true);
	}

	/**
	 * @dataProvider redirectProvider
	 */
	public function testDeleteActionSuccess($id, $type, $typeName, $ref, $refError, $path)
	{
		$redirect = $this->createRedirect($path . '_delete', $type, $ref);
		$this->assertEquals(1, Redirect::find($redirect->getId())->count());

		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('redirects', $redirect->getId());
		$this->resource->deleteAction($request);

		$this->assertEquals(0, Redirect::find($redirect->getId())->count());
	}

	public function testDeleteAllActionSuccess()
	{
		$this->assertGreaterThan(0, Redirect::find()->count());
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$this->resource->deleteAction($request);
		$this->assertEquals(0, Redirect::find()->count());

		// restore state
		foreach ($this->redirectProvider() as $testRedirect) {
			$this->createRedirect($testRedirect[5], $testRedirect[1], $testRedirect[3], $testRedirect[0]);
		}
	}

	/**
	 * @expectedException Store_Api_Exception_Resource_ResourceNotFound
	 * @expectedExceptionCode 404
	 */
	public function testDeleteActionFail()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
		$request->setUserParam('redirects', 999999999);
		$this->resource->deleteAction($request);
	}

	public function testGetCount()
	{
		$request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get'));
		$resource = new Store_Api_Version_2_Resource_Redirects_Count();
		$result = $resource->getAction($request)->getData();
		$this->assertArrayHasKey('count', $result);
		$this->assertEquals(count($this->redirectProvider()), $result['count']);
	}
}
