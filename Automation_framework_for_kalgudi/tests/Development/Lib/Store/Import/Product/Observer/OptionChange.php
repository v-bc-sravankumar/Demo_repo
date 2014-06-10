<?php

require_once(APP_ROOT . "/includes/exporter/class.exportmethod.factory.php");
require_once(APP_ROOT . "/init.php");

/**
 * Before running this query make sure you add the following:
 *
 * INSERT INTO `product_attribute_combinations` (`id`, `product_id`, `product_hash`, `sku`, `cost_price`, `upc`, `stock_level`, `low_stock_level`, `bin_picking_number`) VALUES (1, 5, '', 'MB-1', 0.0000, 'UPC1', 5, 0, 'BPN101');
 *
 * @author qamal.kosim-satyaputra
 *
 */
class Unit_Lib_Store_Import_Observer_OptionChange extends Interspire_UnitTest
{

	const PRODUCT_ID = 5;
	const PRODUCT_NAME = '[Sample Product] MacBook';

	const SKU = 'sku';
	const SKU_ID = 1;
	const SKU_DISPLAY_NAME = 'MB-1';

	const SKU_FLD_ID = 0;
	const SKU_FLD_SKU = 1;
	const SKU_FLD_UPC = 2;
	const SKU_FLD_COMBINATION = 3;
	const SKU_FLD_COST_PRICE = 4;
	const SKU_FLD_BIN_PICKING_NUMBER = 5;
	const SKU_FLD_PROD_LOW_INV = 6;

	const RULE = 'rule';
	const RULE_ID = 16;
	const RULE_DISPLAY_NAME = self::RULE_ID;

	const RULE_IMG_PRODUCT_ID = 2;
	const RULE_IMG_PRODUCT_NAME = '[Sample Product] iPod Nano';
	const RULE_IMG_ID = 3;
	const RULE_IMG_DISPLAY_NAME = self::RULE_IMG_ID;

	const RULE_FLD_ID = 0;
	const RULE_FLD_COMBINATION = 1;
	const RULE_FLD_PROD_CODE = 2;
	const RULE_FLD_PROD_PRICE = 3;
	const RULE_FLD_PROD_WEIGHT = 4;
	const RULE_FLD_PROD_IMG_ID = 5;
	const RULE_FLD_PROD_IMG_PATH = 6;
	const RULE_FLD_PROD_IMG_DESCRIPTION = 7;
	const RULE_FLD_PROD_IMG_IS_THUMB = 8;
	const RULE_FLD_PROD_IMG_SORT = 9;

	const FLD_INDEX_IMPORT = 0;
	const FLD_INDEX_ORIGINAL = 1;
	const FLD_INDEX_LANG = 2;

	protected static $exporter = null;

	/**
	 * Get/create the exporter
	 */
	protected static function getExporter()
	{
		if (self::$exporter == null) {
			self::$exporter = new ISC_ADMIN_EXPORTFILETYPE_PRODUCTS();
			// initialize with the bulk edit template so we get complete fields
			self::$exporter->Init(ISC_ADMIN_EXPORTMETHOD_FACTORY::GetExportMethod('CSV'),
				Store_Import_Product_Observer_OptionChange::EXPORT_TEMPLATE_BULK_EDIT, '', '');
		}
		return self::$exporter;
	}

	/**
	 * Create the settings object
	 * @param array $importSession
	 */
	public function createSettings($importSession = array())
	{

		$defaultImportSession = array(
					'OverrideDuplicates' => true,
					'IsBulkEdit' => true,
					'DeleteImages' => true,
					'DeleteDownloads' => true,
					'IgnoreBlankFields' => true,
					'AutoCategory' => true,
					'CategoryId' => 0

		);
		// merge in the importSession to the default
		foreach ($importSession as $key => $value) {
			$defaultImportSession[$key] = $value;
		}

		return new Store_Import_Product_Settings($defaultImportSession);

	}

	protected $skuRecord = null;
	protected $ruleRecord = null;
	protected $ruleImgRecord = null;

	/**
	 * Get the original sku record
	 * @return array
	 */
	protected function getSKURecord()
	{
		if ($this->skuRecord == null) {
			$record = self::getExporter()->handleAttributeCombination(Store_Product_Attribute_Combination::find(self::SKU_ID)->first(), true);
			// make all key lowercase
			$this->skuRecord = array();
			$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();
			foreach ($fields as $field) {
				if (isset($record[$field[self::FLD_INDEX_ORIGINAL]])) {
					$this->skuRecord[$field[self::FLD_INDEX_IMPORT]] = $record[$field[self::FLD_INDEX_ORIGINAL]];
				}
			}
			$this->skuRecord['itemType'] = 'SKU';
		}
		return $this->skuRecord;
	}

	/**
	 * Get the original rule record
	 * return array
	 */
	protected function getRuleRecord()
	{
		if ($this->ruleRecord == null) {
			$record = self::getExporter()->handleAttributeRule(Store_Product_Attribute_Rule::find(self::RULE_ID)->first(), true);
			// flatten product images
			if (isset($record['productImages']) && is_array($record['productImages'])) {
				$value = $record['productImages'];
				for ($i = 0; $i < count($value); ++$i) {
					foreach ($value[$i] as $irKey => $irValue) {
						$record[$irKey . ($i + 1)] = $irValue;
					}
				}
			}
			// normalize
			$this->ruleRecord = array();
			$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();
			foreach ($fields as $field) {
				if (isset($record[$field[self::FLD_INDEX_ORIGINAL]])) {
					$this->ruleRecord[$field[self::FLD_INDEX_IMPORT]] = $record[$field[self::FLD_INDEX_ORIGINAL]];
				}
			}
			$this->ruleRecord['itemType'] = 'Rule';
		}

		return $this->ruleRecord;
	}

	/**
	* Get the original rule image record
	* return array
	*/
	protected function getRuleImgRecord()
	{
		if ($this->ruleImgRecord == null) {
			$record = self::getExporter()->handleAttributeRule(Store_Product_Attribute_Rule::find(self::RULE_IMG_ID)->first(), true);
			// flatten product images
			if (isset($record['productImages']) && is_array($record['productImages'])) {
				$value = $record['productImages'];
				for ($i = 0; $i < count($value); ++$i) {
					foreach ($value[$i] as $irKey => $irValue) {
						$record[$irKey . ($i + 1)] = $irValue;
					}
				}
			}
			// normalize
			$this->ruleImgRecord = array();
			$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();
			foreach ($fields as $field) {
				if (isset($record[$field[self::FLD_INDEX_ORIGINAL]])) {
					$this->ruleImgRecord[$field[self::FLD_INDEX_IMPORT]] = $record[$field[self::FLD_INDEX_ORIGINAL]];
				}
			}
			$this->ruleImgRecord['itemType'] = 'Rule';

		}
		return $this->ruleImgRecord;
	}

	protected function createImportRow($row, $type, $productId = self::PRODUCT_ID)
	{
		$type = isc_strtolower($type);
		if ($type == 'sku') {
			return new Store_Import_Product_SKURow($row, $productId);
		} else {
			return new Store_Import_Product_RuleRow($row, $productId);
		}
	}


	// --------------- BEGIN SKU TESTCASES ---------------


	public function testSKUNoChange()
	{
		$record = $this->getSKURecord();

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testSKUNoChangeIgnoreBlankFieldsOff()
	{
		$record = $this->getSKURecord();

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports(array('IgnoreBlankFields' => false));

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testSKUChangeSKU()
	{

		$record = $this->getSKURecord();
		$record['prodcode'] = 'MB-123';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_SKU][self::FLD_INDEX_LANG] => array(
					'before' => self::SKU_DISPLAY_NAME,
					'after' => 'MB-123'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testSKUChangeUPC()
	{

		$record = $this->getSKURecord();
		$record['upc'] = 'UPC5';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_UPC][self::FLD_INDEX_LANG] => array(
					'before' => 'UPC1',
					'after' => 'UPC5'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testSKUChangeProductName()
	{

		$record = $this->getSKURecord();
		$record['prodname'] = '[RB]Clock Speeds (CPU)=2.0 Ghz,[RB]HDD Sizes=250 GB';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_COMBINATION][self::FLD_INDEX_LANG] => array(
					'before' => '[RB]Clock Speeds (CPU)=2.0 Ghz,[RB]HDD Sizes=160 GB',
					'after' => '[RB]Clock Speeds (CPU)=2.0 Ghz,[RB]HDD Sizes=250 GB'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testSKUChangeProductCostPrice()
	{

		$record = $this->getSKURecord();
		$record['prodcostprice'] = '5.99';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_COST_PRICE][self::FLD_INDEX_LANG] => array(
					'before' => '0.00',
					'after' => '5.99'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testSKUChangeProductLowInventory()
	{
		$record = $this->getSKURecord();
		$record['prodlowinv'] = '10';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_PROD_LOW_INV][self::FLD_INDEX_LANG] => array(
					'before' => '0',
					'after' => '10'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testSKUChangeCurrentInventory()
	{

		$record = $this->getSKURecord();
		$record['prodcurrentinv'] = '10';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as we dont monitor inventory here');

	}

	public function testSKUMultipleChange1()
	{
		$record = $this->getSKURecord();
		$record['prodlowinv'] = '10';
		$record['prodcostprice'] = '5.99';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_PROD_LOW_INV][self::FLD_INDEX_LANG] => array(
									'before' => '0',
									'after' => '10'
				),
				$fields[self::SKU_FLD_COST_PRICE][self::FLD_INDEX_LANG] => array(
									'before' => '0.00',
									'after' => '5.99'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testSKUMultipleChange2()
	{
		$record = $this->getSKURecord();
		$record['prodlowinv'] = '10';
		$record['prodcostprice'] = '5.99';
		$record['prodcurrentinv'] = '10';
		$record['binPickingNumber'] = 'BPN102';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_COST_PRICE][self::FLD_INDEX_LANG] => array(
					'before' => '0.00',
					'after' => '5.99'
				),
				$fields[self::SKU_FLD_BIN_PICKING_NUMBER][self::FLD_INDEX_LANG] => array(
					'before' => 'BPN101',
					'after' => 'BPN102'
				),
				$fields[self::SKU_FLD_PROD_LOW_INV][self::FLD_INDEX_LANG] => array(
					'before' => '0',
					'after' => '10'
				),
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testSKUDeleteIgnoreBlankFieldsOn()
	{
		$record = $this->getSKURecord();
		$record['binPickingNumber'] = '';

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');
	}

	public function testSKUMultiDeleteIgnoreBlankFieldsOn()
	{
		$record = $this->getSKURecord();
		$record['upc'] = '';
		$record['binPickingNumber'] = '';

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');
	}

	public function testSKUDeleteIgnoreBlankFieldsOff()
	{
		$record = $this->getSKURecord();
		$record['binPickingNumber'] = '';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_BIN_PICKING_NUMBER][self::FLD_INDEX_LANG] => array(
					'before' => 'BPN101',
					'after' => ''
				),
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testSKUMultiDeleteIgnoreBlankFieldsOff()
	{
		$record = $this->getSKURecord();
		$record['upc'] = '';
		$record['binPickingNumber'] = '';

		$fields = Store_Import_Product_Observer_OptionChange::getSKUCommonFields();

		$expected = array(
			'id' => self::SKU_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::SKU,
			'displayName' => self::SKU_DISPLAY_NAME,
			'changes' => array(
				$fields[self::SKU_FLD_UPC][self::FLD_INDEX_LANG] => array(
					'before' => 'UPC1',
					'after' => ''
				),
				$fields[self::SKU_FLD_BIN_PICKING_NUMBER][self::FLD_INDEX_LANG] => array(
					'before' => 'BPN101',
					'after' => ''
				),
			)
		);

		$importProductRow = $this->createImportRow($record, 'sku');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	// --------------- BEGIN RULE TESTCASES ---------------

	public function testRuleNoChange()
	{
		$record = $this->getRuleRecord();

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testRuleNoChangeIgnoreBlankFieldsOff()
	{
		$record = $this->getRuleRecord();

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports(array('IgnoreBlankFields' => false));

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testRuleChangeProductName()
	{

		$record = $this->getRuleRecord();
		$record['prodname'] = '[RB]Clock Speeds (CPU)=2.0 Ghz,[RB]HDD Sizes=160 GB';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_COMBINATION][self::FLD_INDEX_LANG] => array(
					'before' => '[RB]Clock Speeds (CPU)=2.4 Ghz,[RB]HDD Sizes=250 GB',
					'after' => '[RB]Clock Speeds (CPU)=2.0 Ghz,[RB]HDD Sizes=160 GB'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testRuleChangeProductCode()
	{
		$record = $this->getRuleRecord();
		$record['prodcode'] = 'MB-1';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_CODE][self::FLD_INDEX_LANG] => array(
					'before' => '',
					'after' => 'MB-1'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testRuleChangeProductPrice()
	{
		$record = $this->getRuleRecord();
		$record['prodprice'] = '[ADD]9.99';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_PRICE][self::FLD_INDEX_LANG] => array(
					'before' => '[ADD]450',
					'after' => '[ADD]9.99'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testRuleChangeProductWeight()
	{
		$record = $this->getRuleRecord();
		$record['prodweight'] = '[ADD]10';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_WEIGHT][self::FLD_INDEX_LANG] => array(
									'before' => '',
									'after' => '[ADD]10'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);

	}

	public function testRuleDeleteIgnoreBlankFieldsOn()
	{
		$record = $this->getRuleRecord();
		$record['prodprice'] = '';

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');
	}

	public function testRuleDeleteIgnoreBlankFieldsOff()
	{
		$record = $this->getRuleRecord();
		$record['prodprice'] = '';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_ID,
			'productId' => self::PRODUCT_ID,
			'productName' => self::PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_PRICE][self::FLD_INDEX_LANG] => array(
					'before' => '[ADD]450',
					'after' => ''
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule');
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	// --------------- BEGIN RULE WITH IMAGES TESTCASES ---------------

	public function testRuleImgChangeMulti1()
	{
		$record = $this->getRuleImgRecord();
		$record['prodimageid1'] = '5';
		$record['prodimagefile1'] = 'some_image.jpg';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_IMG_ID,
			'productId' => self::RULE_IMG_PRODUCT_ID,
			'productName' => self::RULE_IMG_PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_IMG_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_IMG_ID][self::FLD_INDEX_LANG] => array(
					'before' => '',
					'after' => '5'
				),
				$fields[self::RULE_FLD_PROD_IMG_PATH][self::FLD_INDEX_LANG] => array(
					'before' => '3_source.jpg',
					'after' => 'some_image.jpg'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule', self::RULE_IMG_PRODUCT_ID);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testRuleImgChangeMulti2()
	{
		$record = $this->getRuleImgRecord();
		$record['prodimagedescription1'] = 'Image for the pink ipod nano';
		$record['prodimageisthumb1'] = 'Y';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_IMG_ID,
			'productId' => self::RULE_IMG_PRODUCT_ID,
			'productName' => self::RULE_IMG_PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_IMG_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_IMG_DESCRIPTION][self::FLD_INDEX_LANG] => array(
					'before' => '',
					'after' => 'Image for the pink ipod nano'
				),
				$fields[self::RULE_FLD_PROD_IMG_IS_THUMB][self::FLD_INDEX_LANG] => array(
					'before' => 'N',
					'after' => 'Y'
				)
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule', self::RULE_IMG_PRODUCT_ID);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

	public function testRuleImgDeleteIgnoreBlankFieldsOn()
	{
		$record = $this->getRuleImgRecord();
		$record['prodimagefile1'] = '';

		$importProductRow = $this->createImportRow($record, 'rule', self::RULE_IMG_PRODUCT_ID);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, "There should be no reports as there are no changes.");
	}

	public function testRuleImgDeleteIgnoreBlankFieldsOff()
	{
		$record = $this->getRuleImgRecord();
		$record['prodimagefile1'] = '';

		$fields = Store_Import_Product_Observer_OptionChange::getRuleCommonFields();

		$expected = array(
			'id' => self::RULE_IMG_ID,
			'productId' => self::RULE_IMG_PRODUCT_ID,
			'productName' => self::RULE_IMG_PRODUCT_NAME,
			'type' => self::RULE,
			'displayName' => self::RULE_IMG_DISPLAY_NAME,
			'changes' => array(
				$fields[self::RULE_FLD_PROD_IMG_PATH][self::FLD_INDEX_LANG] => array(
					'before' => '3_source.jpg',
					'after' => ''
				),
			)
		);

		$importProductRow = $this->createImportRow($record, 'rule', self::RULE_IMG_PRODUCT_ID);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_OptionChange, Store_Import_Product_Observer_OptionChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();
		$actual = null;
		if (isset($reports[Store_Import_Product_Observer_OptionChange::$reportLabel])) {
			$actual = $reports[Store_Import_Product_Observer_OptionChange::$reportLabel]->getMessage();
		}

		$this->assertEquals($expected, $actual);
	}

}
