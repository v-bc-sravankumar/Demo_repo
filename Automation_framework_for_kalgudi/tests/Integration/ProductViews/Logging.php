<?php

class Unit_ProductViews_Logging extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testViewLogs ()
	{
	    $this->markTestSkipped('This test has been skipped as it relies on new sample data');
		// todo: split to multiple tests (one large 'smoke' test easier to write for now)

		$productId = 32;
		$sessionId = session_id();

		$GLOBALS['ISC_CLASS_TEMPLATE'] = new TEMPLATE("ISC_LANG");
		$GLOBALS['ISC_CLASS_TEMPLATE']->FrontEnd();
		$GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplateBase(Theme::getRepoBasePath());
		$GLOBALS['ISC_CLASS_TEMPLATE']->panelPHPDir = ISC_BASE_PATH . "/includes/display/";
		$GLOBALS['ISC_CLASS_TEMPLATE']->templateExt = "html";
		$GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate(Store_Config::get("template"));

		$GLOBALS['ISC_CLASS_PRODUCT'] = new ISC_PRODUCT($productId);

		// view product twice
		ob_start();
		$GLOBALS['ISC_CLASS_PRODUCT']->HandlePage();
		ob_end_clean();

		// cannot seem to trigger a second HandlePage so hit it again directly to simulate a second log
		ISC_PRODUCT_VIEWS::logView($productId);

		$db = $GLOBALS['ISC_CLASS_DB'];

		$sql = "SELECT * FROM `[|PREFIX|]product_views` WHERE `session` = '" . $db->Quote($sessionId) . "' AND product = " . $productId;
		$result = $db->Query($sql);

		// check for a valid result
		$this->assertInternalType('resource', $result, "MySQL result not returned when checking for logged views: ". $db->Error());

		// check for a valid row
		$row = $db->Fetch($result);
		$this->assertInternalType('array', $row, $db->Error());
		$this->assertEquals($sessionId, $row['session'], "sessionid mismatch");
		$this->assertEquals($productId, $row['product'], "productid mismatch");
		$this->assertLessThanOrEqual(time(), $row['lastview'], "lastview mismatch");

		// check for only one row
		$row = $db->Fetch($result);
		$this->assertFalse($row, "found more than one row, expecting only one");

		// view a different product
		$productId = 33;

		// cannot seem to trigger a second HandlePage so do it directly since the above has been verified
		ISC_PRODUCT_VIEWS::logView($productId);

		$sql = "SELECT * FROM `[|PREFIX|]product_views` WHERE `session` = '" . $db->Quote($sessionId) . "' AND product = " . $productId;
		$result = $db->Query($sql);

		// check for a valid result
		$this->assertInternalType('resource', $result, "MySQL result not returned when checking for logged views on second product: " . $db->Error());

		// check for a valid row
		$row = $db->Fetch($result);
		$this->assertInternalType('array', $row, $db->Error());
		$this->assertEquals($sessionId, $row['session'], "sessionid mismatch second product");
		$this->assertEquals($productId, $row['product'], "productid mismatch second product");
		$this->assertLessThanOrEqual(time(), $row['lastview'], "lastview mismatch second product");

		// check for only one row
		$row = $db->Fetch($result);
		$this->assertFalse($row, "second product: found more than one row, expecting only one");

		// check for correct summaries
		ISC_PRODUCT_VIEWS::summariseLogs($sessionId);

		// summarising is now a background task; execute it
		while (Interspire_TaskManager_Internal::executeNextTask()) { }

		$sql = "SELECT * FROM `[|PREFIX|]product_views` WHERE `session` = '" . $db->Quote($sessionId) . "'";
		$result = $db->Query($sql);

		// check for a valid result
		$this->assertInternalType('resource', $result, "MySQL result not returned when checking for views after summary: " . $db->Error());

		// check for zero rows
		$row = $db->Fetch($result);
		$this->assertFalse($row, "post-summary: found a db row, expected none");

		// check for related products
		$related = ISC_PRODUCT_VIEWS::getRelatedProducts($productId);
		$this->assertEquals(1, count($related), "post-summary: related product count mismatch");

		// check for valid relationship
		$product = $related[0];
		//$this->assertEquals(1, $product['productid'], "post-summary: related product id mismatch");

		$sql = "SELECT * FROM `[|PREFIX|]product_related_byviews` WHERE `prodida` = " . $productId;
		$result = $db->Query($sql);

		// check for a valid result
		$this->assertInternalType('resource', $result, "MySQL result not returned when checking view relationship: " . $db->Error());

		// check for a valid row
		$row = $db->Fetch($result);
		$this->assertInternalType('array', $row, $db->Error());
		$this->assertEquals($productId, $row['prodida'], "view-relation check: prodida mismatch");
		$this->assertEquals(32, $row['prodidb'], "view-relation check: prodidb mismatch");
		$this->assertEquals(1, $row['relevance'], "view-relation check: relevance mismatch");
		$this->assertLessThanOrEqual(time(), $row['lastview'], "view-relation check: lastview mismatch");

		// check for only one row
		$row = $db->Fetch($result);
		$this->assertFalse($row, "view-relation check: expected one row, found more");
	}
}
