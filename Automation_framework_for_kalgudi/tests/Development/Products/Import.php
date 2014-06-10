<?php

/**
 * ====================================================================================================
 * Note, these test cases are not intended to be used as part of CI, they simply serve as a mechanism
 * to trigger individual components of the import process without interacting with the front end.
 * ====================================================================================================
 */

require_once(APP_ROOT."/includes/classes/class.batch.importer.php");
require_once(APP_ROOT."/includes/importer/products.php");

class ProductImporterMock extends ISC_BATCH_IMPORTER_PRODUCTS
{
	public function __construct()
	{
		$GLOBALS['ISC_CLASS_ADMIN_ENGINE']->LoadLangFile('batch.importer');

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

		$this->db = $GLOBALS['db'];

		return true;
	}
}

class Import extends PHPUnit_Framework_TestCase
{

	public static $testSqlFiles = array();
	public static $productImporter;
	public static $productImageUploadsDir;
	public static $productImageAttributeImagesDir;


	protected static function setupTestSqlData($sqlFiles)
	{

		// Connect to the database and run the SQL
		$db = new mysqli(TEST_DB_SERVER, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME);

		foreach($sqlFiles as $dataFile) {

			// This specific set of test data is already installed, simply skip
			if(isset(self::$testSqlFiles[$dataFile])) {
				continue;
			}

			$dataPath = TEST_DATA_ROOT.'/'.basename($dataFile).'.sql';

			if(!file_exists($dataPath)) {
				return false;
			}

			$dataContents = file_get_contents($dataPath);
			$dataContents = str_replace('[|PREFIX|]', '', $dataContents);

			if(!$db->multi_query($dataContents)) {
				continue;
			}

			self::$testSqlFiles[$dataFile] = true;
		}

		unset($db);
		return true;

	}

	public static function setUpBeforeClass()
	{

		// self::setupTestSqlData(array('option-related-tables'));

		self::$productImporter = new ProductImporterMock;

		self::$productImageUploadsDir = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/uploaded_images/');
		self::$productImageAttributeImagesDir = Store_Asset::generatePath(Store_Config::get('ImageDirectory') . '/attribute_value_images/');

		/**
		 * Copy the upload file into the correct place
		 */
		$file = new Interspire_File(dirname(__FILE__)."/Images/texture.jpg");
		$file->copy(self::$productImageUploadsDir.'test.jpg', true);

	}

	public static function tearDownAfterClass()
	{
		self::$productImporter = null;
	}

	public function testInstantiation()
	{
		$this->assertTrue((self::$productImporter instanceof ISC_BATCH_IMPORTER_PRODUCTS), 'Cant even instantiate the correct product importer.');
	}

	public function testGetOptions()
	{
		$known = 'iPod Classic';
		$productTypeAttribute = self::$productImporter->getOptionSet($known);
		$this->assertEquals(5, $productTypeAttribute->getId());
	}

	public function testCreateGenericAttributeValue()
	{

		$csvData = "11000 GB";
		$hddSizes = new Store_Attribute();
		$hddSizes->load(6); // Type: Store_Attribute_Type_Configurable_PickList_Set)

		// var_export($hddSizes);

		$value = $hddSizes->createValueFromCsvData($csvData);

		$value->save();
		$this->assertEquals($value->getId(), 68, 'CSV data not added correctly "'.$csvData.'"');


	}

	public function testCreateColourAttributes()
	{
		$csvData[11] = "Green";
		$csvData[69] = "Grouse:Red";
		$csvData[70] = "Retro:Red|Green";
		$csvData[71] = "Pastelle:Red|Green|Blue";
		$csvData[71] = "NewAndImproved:#e841c1|#123456";

		$colours = new Store_Attribute();
		$colours->load(3); // Type: Store_Attribute_Type_Configurable_PickList_Swatch

		foreach($csvData as $id => $d)
		{
			$value = $colours->createValueFromCsvData($d);
			$value->save();
			$this->assertTrue($value instanceof Store_Attribute_Value, 'CSV data not added correctly "'.$d.'"');
		}
	}

	public function testCreateProductPicklistAttributes()
	{
		$csvData = "[Sample Product] MacBook Pro";
		$software = new Store_Attribute();
		$software->load(12); // Type: Store_Attribute_Type_Configurable_PickList_Product
		$value = $software->createValueFromCsvData($csvData);
		$value->save();
		$this->assertEquals(72, $value->getId(), 'CSV data not added correctly "'.$csvData.'"');
		$this->assertEquals(6, $value->getValueData()->getProductId(), 'Product not correctly loaded by name');


		$csvData = "[Sample Product] Crumpler Considerable Embarrassment Bag";
		$software = new Store_Attribute();
		$software->load(12); // Type: Store_Attribute_Type_Configurable_PickList_Product
		$value = $software->createValueFromCsvData($csvData);
		$value->save();
		$this->assertEquals(73, $value->getId(), 'CSV data not added correctly "'.$csvData.'"');
		$this->assertEquals(18, $value->getValueData()->getProductId(), 'Product not correctly loaded by name');
	}

	public function testAddCompletelyNewAttributeColorFromCsv()
	{

		$productId = 1; // Link to this product.
		$optionName = "Color"; // Should be new?
		$optionType = "Store_Attribute_Type_Configurable_PickList_Swatch";
		$csvData = "Ugly:olive|fuchsia|Yellow";

		/**
		 * This highlights the dependency on the ImportSession.
		 * The $currentOptionSet should be passed in as a parameter making this function much more portable.
		 */
		$currentOptionSet = new Store_Product_Type;
		$currentOptionSet->load(6);
		self::$productImporter->ImportSession['CurrentOptionSet'] = serialize($currentOptionSet);

		$productAttribute = self::$productImporter->getProductAttributeForName($optionName, $optionType, $productId, true);
		$value = $productAttribute->getAttribute()->createValueFromCsvData($csvData);

		$this->assertEquals(74, $value->getId(), 'CSV data not added correctly "'.$csvData.'"');

	}

	public function testCreateTextureAttributeFromRemoteFile()
	{
		$colours = new Store_Attribute;
		$colours->load(3); // Type: Store_Attribute_Type_Configurable_PickList_Swatch

		$csvData = "Remote File:http://12.media.tumblr.com/tumblr_kumup4K8uX1qzg58bo1_500.jpg";
		$value = $colours->createValueFromCsvData($csvData);

		$this->assertTrue(file_exists(self::$productImageAttributeImagesDir.$value->getId().'.thumbnail.jpg'));
		$this->assertTrue(file_exists(self::$productImageAttributeImagesDir.$value->getId().'.preview.jpg'));
	}

	public function _testCreateAttributeFromLocalFile()
	{
		$colours = new Store_Attribute;
		$colours->load(3); // Type: Store_Attribute_Type_Configurable_PickList_Swatch

		$csvData = "Local File:test.jpg";
		$value = $colours->createValueFromCsvData($csvData);

		$this->assertTrue(file_exists(self::$productImageAttributeImagesDir.$value->getId().'.thumbnail.jpg'));
		$this->assertTrue(file_exists(self::$productImageAttributeImagesDir.$value->getId().'.preview.jpg'));
	}


	public function testISC2752()
	{

//		$data = self::$productImporter->getOptionTypeClassAndView('RB'); // Radio
//		$this->assertEquals('Store_Attribute_Type_Configurable_PickList_Set', $data['type']);
//		$this->assertEquals('Store_Attribute_View_Radio', $data['view']);
//
//		$data = self::$productImporter->getOptionTypeClassAndView('RT'); // Rectangle
//		$this->assertEquals('Store_Attribute_Type_Configurable_PickList_Set', $data['type']);
//		$this->assertEquals('Store_Attribute_View_Rectangle', $data['view']);
//
//		$data = self::$productImporter->getOptionTypeClassAndView('S'); // Select
//		$this->assertEquals('Store_Attribute_Type_Configurable_PickList_Set', $data['type']);
//		$this->assertEquals('Store_Attribute_View_Select', $data['view']);

		/**
		 * =================
		 * Types with views
		 * =================
		 */

		// [RB] for radio
		$radio = new Store_Attribute_Type_Configurable_PickList_Set;
		$radio->setView(new Store_Attribute_View_Radio);
		$this->assertEquals('RB', $radio->getImportExportPrefix(), '[RB] code not correctly returned');

		// [RT] for rectangle
		$rectangle = new Store_Attribute_Type_Configurable_PickList_Set;
		$rectangle->setView(new Store_Attribute_View_Rectangle);
		$this->assertEquals('RT', $rectangle->getImportExportPrefix(), '[RT] code not correctly returned');

		// [S] for set (multiline)?
		$select = new Store_Attribute_Type_Configurable_PickList_Set;
		$select->setView(new Store_Attribute_View_Select);
		$this->assertEquals('S', $select->getImportExportPrefix(), '[S] code not correctly returned');

		// [P] for product list
	    $p = new Store_Attribute_Type_Configurable_PickList_Product;
	    $p->setView(new Store_Attribute_View_Product_PickList);
		$this->assertEquals('P', $p->getImportExportPrefix(), '[P] code not correctly returned');

	    // [PI] for product list with images
	    $pi = new Store_Attribute_Type_Configurable_PickList_Product;
		$pi->setView(new Store_Attribute_View_Product_PickListWithImage);
		$this->assertEquals('PI', $pi->getImportExportPrefix(), '[PI] code not correctly returned');

		/**
		 * ===================
		 * Types with no views
		 * ===================
		 */

	    // [C] for checkbox
	    $c = new Store_Attribute_Type_Configurable_Entry_Checkbox;
		$this->assertEquals('C', $c->getImportExportPrefix(), '[C] code not correctly returned');

	    // [CS] for swatch
	    $cs = new Store_Attribute_Type_Configurable_PickList_Swatch;
		$this->assertEquals('CS', $cs->getImportExportPrefix(), '[CS] code not correctly returned');

	}

}