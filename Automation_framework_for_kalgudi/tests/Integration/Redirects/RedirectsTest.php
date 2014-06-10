<?php
namespace Redirects;

use Store\Redirect;
use Store\Redirect\Forward;

class RedirectsTest extends \PHPUnit_Extensions_Functional_TestCase {

	protected function createManualRedirect($from, $to)
	{
		$redirect = new Redirect();
		$redirect->setPath($from);
		$redirect->setForward(new Forward(Forward::TYPE_MANUAL, $to));
		$redirect->save();
		return $redirect;
	}

	public function indexPageProvider()
	{
		return array(
			// index.php with no query params should not redirect
			array("/index.php", "/index.php", 200),

			// GET /index.php should not match redirect containing query params
			array("/index.php?foo=bar", "/index.php", 200),

			// correct query parameter order is required to match
			array("/index.php?foo=bar&baz=qux", "/index.php?baz=qux&foo=bar", 200),

			// exact index.php path with query params should redirect
			array("/index.php?foo=bar&baz=qux", "/index.php?foo=bar&baz=qux", 301),
		);
	}

	/**
	 * @dataProvider indexPageProvider
	 */
	public function testIndexWithoutParams($redirectPath, $getPath, $status)
	{
		$redirect = $this->createManualRedirect($redirectPath, "http://www.bigcommerce.com");

		$this->get($getPath);
		$this->assertStatus($status);

		$redirect->delete();
	}

}
