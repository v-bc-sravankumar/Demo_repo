<?php

class Unit_Lib_Store_String extends PhpUnit_Framework_TestCase
{

	private static $shopUrls = array('http://127.0.0.1/trunk', 'http://127.0.0.1/trunk/');

	private static $unReplaced;

	private static $replaced;

	public function __construct()
	{
		self::$unReplaced = <<<UNREPLACED
<p style="margin-top: 12px;"><strong>%%GLOBAL_StoreName%%</strong> <br /> <a href="%%GLOBAL_ShopPathNormal%%">%%GLOBAL_ShopPathNormal%%</a></p>
<p style="margin-top: 12px;"><a href="http://example.com/absolute.html">I'm absolute</a></p>
<p style="margin-top: 12px;"><a href="http://127.0.0.1/absolute.html">I'm also absolute</a></p>
<p style="margin-top: 12px;"><a href="http://127.0.0.1/trunk/relative.html">I'm relative</a></p>
<p style="margin-top: 12px;"><img class="__mce_add_custom__" title="eyes.jpg" src="http://127.0.0.1/trunk/product_images/uploaded_images/eyes.jpg" alt="eyes.jpg" width="200" height="300" /></p>
UNREPLACED;

		self::$replaced = <<<REPLACED
<p style="margin-top: 12px;"><strong>%%GLOBAL_StoreName%%</strong> <br /> <a href="%%GLOBAL_ShopPathNormal%%">%%GLOBAL_ShopPathNormal%%</a></p>
<p style="margin-top: 12px;"><a href="http://example.com/absolute.html">I'm absolute</a></p>
<p style="margin-top: 12px;"><a href="%%GLOBAL_ShopDomain%%/absolute.html">I'm also absolute</a></p>
<p style="margin-top: 12px;"><a href="%%GLOBAL_ShopDomain%%%%GLOBAL_ShopSubFolder%%/relative.html">I'm relative</a></p>
<p style="margin-top: 12px;"><img class="__mce_add_custom__" title="eyes.jpg" src="%%GLOBAL_ShopDomain%%%%GLOBAL_ShopSubFolder%%/product_images/uploaded_images/eyes.jpg" alt="eyes.jpg" width="200" height="300" /></p>
REPLACED;

	}

	/**
	 * Note: in order to use this test, you MUST change the value in config/config.php as follows:
	 * "ShopPath" = 'https://store-64b37.dev-bigcommerce.interspire'; // for testing ISC-2674
	 *
	 * It seems that the GLOBALS is populated somewhere along the line and you cant just set the value in the testcase.
	 */
	public function _testISC2674()
	{

		// This does not actually work as GLOBALS['shopPath'] is reset to whatevers in config at runtime.
		$GLOBALS['shopPath'] = 'https://store-64b37.dev-bigcommerce.interspire';

		$unReplaced = <<<UNREPLACED
<p style="margin-top: 12px;"><strong>%%GLOBAL_StoreName%%</strong> <br /> <a href="%%GLOBAL_ShopPathNormal%%">%%GLOBAL_ShopPathNormal%%</a></p>
<p style="margin-top: 12px;"><a href="http://example.com/absolute.html">I'm absolute</a></p>
<p style="margin-top: 12px;"><a href="https://store-64b37.dev-bigcommerce.interspire/absolute.html">I'm also absolute</a></p>
<p style="margin-top: 12px;"><a href="https://store-64b37.dev-bigcommerce.interspire/relative.html">I'm relative</a></p>
<p style="margin-top: 12px;"><img class="__mce_add_custom__" title="eyes.jpg" src="https://store-64b37.dev-bigcommerce.interspire/product_images/uploaded_images/eyes.jpg" alt="eyes.jpg" width="200" height="300" /></p>
UNREPLACED;

		$replaced = <<<REPLACED
<p style="margin-top: 12px;"><strong>%%GLOBAL_StoreName%%</strong> <br /> <a href="%%GLOBAL_ShopPathNormal%%">%%GLOBAL_ShopPathNormal%%</a></p>
<p style="margin-top: 12px;"><a href="http://example.com/absolute.html">I'm absolute</a></p>
<p style="margin-top: 12px;"><a href="%%GLOBAL_ShopDomain%%/absolute.html">I'm also absolute</a></p>
<p style="margin-top: 12px;"><a href="%%GLOBAL_ShopDomain%%/relative.html">I'm relative</a></p>
<p style="margin-top: 12px;"><img class="__mce_add_custom__" title="eyes.jpg" src="%%GLOBAL_ShopDomain%%/product_images/uploaded_images/eyes.jpg" alt="eyes.jpg" width="200" height="300" /></p>
REPLACED;


		$html = $unReplaced;
		Store_String::addShopPathPlaceholder($html);
		$this->assertSame($replaced, $html);

	}

	public function testAddShopPathPlaceholder()
	{
		foreach(self::$shopUrls as $u)
		{
			$GLOBALS['ShopPath'] = $u;
			$html = self::$unReplaced;
			Store_String::addShopPathPlaceholder($html);
			$this->assertSame(self::$replaced, $html);
		}
	}

	public function testRemoveShopPathPlaceholder()
	{
		foreach(self::$shopUrls as $u)
		{
			$GLOBALS['ShopPath'] = $u;
			$html = self::$replaced;
			Store_String::removeShopPathPlaceholder($html);
			$this->assertSame(self::$unReplaced, $html);
		}
	}

//	function testReplaceEmailSnippets()
//	{
//
//		$emailTemplate = new TEMPLATE("ISC_LANG");
//		$emailTemplate->SetTemplateBase(ISC_BASE_PATH."/templates/__emails/");
//		$emailTemplate->panelPHPDir = ISC_BASE_PATH.'/includes/Panels/';
//		$emailTemplate->templateExt = 'html';
//		$emailTemplate->Assign('EmailFooter', $emailTemplate->GetSnippet('EmailFooter'));
//
//		echo "\n".'$emailTemplate->GetSnippet("EmailFooter") = '."\n";
//		var_export($emailTemplate->GetSnippet("EmailFooter"));
//	}

//	function testReplaceEmailPanels()
//	{
//
//		$emailTemplate = new TEMPLATE("ISC_LANG");
//		$emailTemplate->SetTemplateBase(ISC_BASE_PATH."/templates/__emails/");
//		$emailTemplate->panelPHPDir = ISC_BASE_PATH.'/includes/Panels/';
//		$emailTemplate->templateExt = 'html';
//
//		$emailTemplate->SetTemplate('createaccount_email');
//
//		echo "Parsed:\n";
//		var_export($emailTemplate->ParseTemplate(true));
//	}


}