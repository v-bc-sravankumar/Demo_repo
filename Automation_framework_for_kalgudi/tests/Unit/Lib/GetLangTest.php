<?php

namespace Unit\Lib;
use \Store_Config;
use GetText;
require_once __DIR__ . '/../../../lib/general.php';

class GetLangTest extends \PHPUnit_Framework_TestCase
{
	/**
	*Sets up the environment for GetText and GetLang calls
	*@param $lang Language to be set
	*/
	public function getTextSetUp($lang)
	{
		if (!defined('ISC_BASE_PATH')) {
			define('ISC_BASE_PATH', realpath(__DIR__ . "/../../.."));
		}
		$translations_path = realpath(dirname(__FILE__) . "/../../../language/locale");
		Store_Config::override('template', 'Blueprint');
		$GLOBALS['TPL_CFG']['International'] = true;
		Store_Config::override('Store_Language', $lang);
		
		$GLOBALS['ISC_LANG'] = array();
		$storeFrontDomains = array (
			'front_language',
			'common',
			'module_language',
			'settings'
		);
		foreach ($storeFrontDomains as $iniName) {
			$iniPath = realpath(dirname(__FILE__) . "/../../../language/en/$iniName.ini");
			$this->parseAndLoadLangFile($iniPath);

		}
	}

	/**
	 * Parses INI files and merges it into $GLOBALS['ISC_LANG']
	 * @param $file INI File name to parse and merge
	*/
	public function parseAndLoadLangFile($file)
	{
		$vars = parse_ini_file($file);
		if (! is_array($vars)) {
			$logger = \Logging\Logger::getInstance();
			$logger->error("The language file {file} couldn't be loaded.", array("file" => $file));
			return;
		}
		// If $GLOBALS['ISC_LANG']) already exists, Merge $vars into $GLOBALS['ISC_LANG']).
		if (isset($GLOBALS['ISC_LANG'])) {
			$GLOBALS['ISC_LANG'] = array_merge($GLOBALS['ISC_LANG'], $vars);
		} else {
			$GLOBALS['ISC_LANG'] = $vars;
		}
	}

	public function predefinedPluralsData()
	{
		return array(
			array(
				array(
					'en_US' => array(
								'OneItem'                    => '1 item',
								'XItems'                     => '1 items',
								'1ItemInWishListMessage'     => 'Your wish list contains 1 item and is shown below.',
								'XItemsInWishListMessage'    => 'Your wish list contains 2 items and is shown below.'
								),
					'es_ES' => array(
								'OneItem'                    => '1 artículo',
								'XItems'                     => '2 artículos',
								'1ItemInWishListMessage'     => 'Su lista de deseos contiene 1 elemento y se muestra a continuación.',
								'XItemsInWishListMessage'    => 'Su lista de deseos contiene 2 elementos y se muestra a continuación.'
								),
					'fr_FR' => array(
								'OneItem'                    => '1 article',
								'XItems'                     => '2 articles',
								'1ItemInWishListMessage'     => 'Votre liste de souhaits contient 1 élément et est illustré ci-dessous.',
								'XItemsInWishListMessage'    => 'Votre liste de souhaits contient 2 articles et est illustré ci-dessous.'
								),								
					'ja_JP' => array(
								'OneItem'                    => '1 枚アイテム',
								'XItems'                     => '2 枚アイテム',
								'1ItemInWishListMessage'     => 'お客様欲しい物のリストには、一枚項目が含まれており、以下のようになります。',
								'XItemsInWishListMessage'    => 'お客様欲しい物のリストには、2 項目が含まれており、以下のようになります。'
								)
				),
			),
		); 
	}

	/**
	 * Tests if native gettext is supported or not
	*/
	public function testGetTextExists()
	{
		$getTextFlag = function_exists('gettext');
		$this->assertEquals(true, $getTextFlag);
	}

	/**
	 * Tests isEnabled for the Global variable  "International"
	*/
	public function testIsEnabled()
	{
		$GLOBALS['TPL_CFG']['International'] = true;
		$themeInternationalFlag = GetText::getInstance()->isEnabled();
		$this->assertEquals(true, $themeInternationalFlag);
	}

	/**
	 * Check for a valid locale path for GetText PO files
	*/
	public function testLocalePath()
	{
		// Define path where our PO files exists.
		$localePath = realpath(dirname(__FILE__) . "/../../../language/locale");
		if ($localePath) {
			$localePathFlag = TRUE;
		} else {
			$localePathFlag = FALSE;
		}
		$this->assertEquals(TRUE,$localePathFlag);
	}

	/**
	 * Tests whether English locale is available or not in the environment
	*/
	public function testEnglishLocale()
	{
		$lang = "en_US";
		$locale = "$lang.UTF-8";

		$setLocaleFlag = false;
		if (setlocale(LC_ALL, $locale)) {
			$setLocaleFlag = true;
		}

		$this->assertEquals(true, $setLocaleFlag);
	}

	/**
	 * Tests whether Spanish locale is available or not in the environment
	*/
	public function testSpanishLocale()
	{
		$this->markTestSkipped('Skipping Test For Spanish Locale');
		$lang = "es_ES";
		$locale = "$lang.UTF-8";

		$setLocaleFlag = false;
		if (setlocale(LC_ALL, $locale)) {
			$setLocaleFlag = true;
		} 
				
		$this->assertEquals(true, $setLocaleFlag);
	}

	/**
	 * Tests whether French locale is available or not in the environment
	 */
	public function testFrenchLocale()
	{
		$this->markTestSkipped('Skipping Test For French Locale');
		$lang = "fr_FR";
		$locale = "$lang.UTF-8";

		$setLocaleFlag = false;
		if (setlocale(LC_ALL, $locale)) {
			$setLocaleFlag = true;
		}

		$this->assertEquals(true, $setLocaleFlag);
	}

	/**
	 * Tests whether Japanese locale is available or not in the environment
	 */	
	public function testJapaneseLocale()
	{
		$this->markTestSkipped('Skipping Test For Japanese Locale');
		$lang = "ja_JP";
		$locale = "$lang.UTF-8"; 

		$setLocaleFlag = false;
		if (setlocale(LC_ALL, $locale)) {
			$setLocaleFlag = true;
		}

		$this->assertEquals(true, $setLocaleFlag);
	}

	/**
	 * Test for native gettext with English
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetTextNativeEnglish()
	{
		$key = 'AdvancedSearch';
		$actValue = 'Advanced Search';
		$lang = 'en_US';
		$domain = 'storefront';
		$localePath = realpath(__DIR__ . "/../../../language/locale");
		$locale = "$lang.UTF-8";
		putenv("LANGUAGE=" . $lang);
		setlocale(LC_ALL, $locale);
		bindtextdomain($domain, $localePath);
		textdomain($domain);
		$expValue=gettext($key);
		$this->assertEquals($actValue, $expValue);
	}
	
	/**
	 * Test for GetLang with English
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 * 
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetLangEnglish()
	{
		$this->getTextSetUp('en_US');

		$poString = GetLang('AddToCartAlt');
		$this->assertEquals('Click here to add this product to your cart', $poString);

		$poString = GetLang('HeadRSSNewProducts');
		$this->assertEquals('New Products', $poString);
	}

	/**
	 * Test for GetLang with English & replacements array
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 * 
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetLangEnglishReplacements()
	{
		$this->getTextSetUp('en_US');

		$numItems = 3;
		$actualResultValue = GetLang('XItemsAdded', array('count' => $numItems));
		$expectedValue = 'OK, 3 items were added to your cart. What next?';
		$this->assertEquals($expectedValue,$actualResultValue);
	}
	
	/**
	 * Test for GetLang with Spanish
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 * 
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetLangSpanish()
	{
		$this->markTestSkipped('Skipping Test For Spanish GetLang');
		$this->getTextSetUp('es_ES');

		$poString = GetLang('AddToCartAlt');
		$this->assertEquals('Haga clic aquí para añadir este producto a su cesta', $poString);

		$poString = GetLang('HeadRSSNewProducts');
		$this->assertEquals('Nuevos Productos', $poString);
	}
	
	/**
	 * Test for GetLang with French
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetLangFrench()
	{
		$this->markTestSkipped('Skipping Test For French GetLang');
		$this->getTextSetUp('fr_FR');

		$poString = GetLang('AddToCartAlt');
		$this->assertEquals('Cliquez ici pour ajouter ce produit à votre panier', $poString);
		
		$poString = GetLang('HeadRSSNewProducts');
		$this->assertEquals('Nouveaux produits', $poString);
	}


	/**
	 * Test for GetLang with Japanse
	 * Running this Unit Test in a different process as setLocale() affects getText configuration
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testGetLangJapanese()
	{
		$this->markTestSkipped('Skipping Test For Japanese GetLang');
		$this->getTextSetUp('ja_JP');

		$poString = GetLang('AddToCartAlt');
		$this->assertEquals('カートにこの製品を追加するにはここをクリックしてください', $poString);
	}
	
	/**
	 * @dataProvider predefinedPluralsData
	 *	 
	 */
	public function testPlurals($pluralData)
	{
		foreach ($pluralData as $key => $value) {
			$this->getTextSetUp($key);
			$singularString = GetText::getInstance()->getTextPlural('OneItem', 'XItems' , 1);
			$pluralString = GetText::getInstance()->getTextPlural('1ItemInWishListMessage', 'XItemsInWishListMessage' , 2);
			$this->assertEquals($value['OneItem'], $singularString);
			$this->assertEquals($value['XItemsInWishListMessage'], $pluralString);
		}
	}

}