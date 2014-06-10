<?php

/**
 * ====================================================================================================
 * Note, these test cases are not intended to be used as part of CI, they simply serve as a mechanism
 * to trigger individual components of the import process without interacting with the front end.
 * ====================================================================================================
 */

require_once(APP_ROOT."/admin/includes/classes/class.batch.importer.php");
require_once(APP_ROOT."/admin/includes/importer/products.php");

class ProductImporterMock extends ISC_BATCH_IMPORTER_PRODUCTS
{
	public function __construct()
	{
		// $GLOBALS['ISC_CLASS_ADMIN_ENGINE']->LoadLangFile('batch.importer');

		/**
		 * @var array Array of importable fields and their friendly names.
		 */
		$this->_ImportFields = array(
			"itemType" => GetLang('ItemType'),
			"productid" => GetLang('ProductID'),
			"prodname" => GetLang('ProductName'),
			"category" => GetLang('ImportProductsCategory'),
			"category2" => GetLang('ImportProductsCategory2'),
			"category3" => GetLang('ImportProductsCategory3'),
			"brandname" => GetLang('BrandName'),
			"optionset" => GetLang('OptionSet'),
			"optionsetalign" => GetLang('OptionSetAlign'),
			"prodcode" => GetLang('ProductCodeSKU'),
			"proddesc" => GetLang('ProductDescription'),
			"prodprice" => GetLang('Price'),
			"prodcostprice" => GetLang('CostPrice'),
			"prodsaleprice" => GetLang('SalePrice'),
			"prodretailprice" => GetLang('RetailPrice'),
			"prodfixedshippingcost" => GetLang('FixedShippingCost'),
			"prodfreeshipping" => GetLang('FreeShipping'),
			"prodallowpurchases" => GetLang('ProductAllowPurchases'),
			"prodavailability" => GetLang('Availability'),
			"prodvisible" => GetLang('ProductVisible'),
			"prodinvtrack" => GetLang('ProductTrackInventory'),
			"prodcurrentinv" => GetLang('CurrentStockLevel'),
			"prodlowinv" => GetLang('LowStockLevel'),
			"prodwarranty" => GetLang('ProductWarranty'),
			"prodweight" => GetLang('ProductWeight'),
			"prodwidth" => GetLang('ProductWidth'),
			"prodheight" => GetLang('ProductHeight'),
			"proddepth" => GetLang('ProductDepth'),
			"prodpagetitle" => GetLang('PageTitle'),
			"prodsearchkeywords" => GetLang('SearchKeywords'),
			"prodmetakeywords" => GetLang('MetaKeywords'),
			"prodmetadesc" => GetLang('MetaDescription'),
			"prodimagefile" => GetLang('ProductImage'),
			"prodimagedescription" => GetLang('ProductImageDescription'),
			"prodimageisthumb" => GetLang('ProductImageIsThumb'),
			"prodimagesort" => GetLang('ProductImageSort'),
			"prodfile" => GetLang('ProductFile'),
			"prodfiledescription" => GetLang('ProductFileDescription'),
			"prodfilemaxdownloads" => GetLang('ProductFileMaxDownloads'),
			"prodfileexpiresafter" => GetLang('ProductFileExpiresAfter'),
			"prodcondition" => GetLang('ProductCondition'),
			"prodshowcondition" => GetLang('ProductShowCondition'),
			"prodeventdaterequired" => GetLang('ProductEventDateRequired'),
			"prodeventdatefieldname" => GetLang('ProductEventDateName'),
			"prodeventdatelimited" => GetLang('ProductEventDateLimited'),
			"prodeventdatelimitedstartdate" => GetLang('ProductEventDateStartDate'),
			"prodeventdatelimitedenddate" => GetLang('ProductEventDateEndDate'),
			"prodsortorder"	=> GetLang('SortOrder'),
			'tax_class_name' => getLang('ProductTaxClass'),
			'upc'	=> GetLang('ProductUPC'),
		);

// 		$this->db = $GLOBALS['db'];

		return true;
	}
}

class Import extends PHPUnit_Framework_TestCase
{

	public static $testSqlFiles = array();
	public static $productImporter;

	public static function setUpBeforeClass()
	{
		self::$productImporter = new ProductImporterMock;
	}

	public static function tearDownAfterClass()
	{
		self::$productImporter = null;
	}

	public function dataGenders()
	{
		return array(
			array(array('male'), true),
			array(array('female'), true),
			array(array('Female'), true),
			array(array('unisex'), true),
			array(array('transgender'), false),
			array(array(''), false),
		);
	}

	/**
	 * @dataProvider dataGenders
	 */
	public function testValidateGenders($input, $expected)
	{
		$gender = $input[0];
		$result = self::$productImporter->gpsValidateGender($gender);
		$this->assertEquals($expected, $result, 'Gender '.$gender.' should not be validated as '.(bool)$result);
	}

	public function dataAgeGroups()
	{
		return array(
			array(array('adult'), true),
			array(array('kids'), true),
			array(array('cats'), false),
			array(array('AdUlT'), true),
			array(array(''), false),
		);
	}

	/**
	 * @dataProvider dataAgeGroups
	 */
	public function testValidateAgeGroups($input, $expected)
	{
		$ageGroup = $input[0];
		$result = self::$productImporter->gpsValidateAgeGroup($ageGroup);
		$this->assertEquals($expected, $result, 'Age group '.$ageGroup.' should not be validated as '.(bool)$result);
	}

	public function testImportCategoryMapping()
	{
		$categoryString = 'Shop iPhone/Accessories';
		$ids = self::$productImporter->categoryNamesToIds($categoryString);
		$this->assertEquals($ids, array(2,7), 'Incorrect mapping of '.$categoryString);
	}

	public function productCategories()
	{
		return array(
			array(array(22, 7), true),
			array(array(22, 8), true),
			array(array(22, 1), false),
		);
	}

	/**
	 * @dataProvider productCategories
	 */
	public function testValidGoogleProductCategoryForProduct($input, $expected)
	{

		$productId = $input[0];
		$categoryId = $input[1];

		$result = self::$productImporter->gpsValidCategoryForThisProduct($categoryId, $productId);

		$this->assertEquals($expected, $result, 'The category '.$categoryId.' should be suitable for the product '.$productId);
	}

}