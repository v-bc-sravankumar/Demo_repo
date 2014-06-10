<?php

require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Attribute extends ModelLike_TestCase
{
	protected $_attribute;

	public function setUp ()
	{
		parent::setUp();

		$this->_attribute = new Store_Attribute;
		$this->_attribute
			->setName('attribute_value_parent_test')
			->setDisplayName('attribute_value_parent_test display name')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($this->_attribute->save(), "failed to create attribute_value_parent_test");
	}

	public function tearDown ()
	{
		$this->_attribute->delete();
	}

	protected function _getCrudSmokeGetMethod ()
	{
		return 'getStaticValue';
	}

	protected function _getCrudSmokeSetMethod ()
	{
		return 'setStaticValue';
	}

	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Attribute;
		$model->setStaticValue($this->_getCrudSmokeValue1())
			->setProductId(28)
			->setAttributeId($this->_attribute->getId());

		return $model;
	}

	protected function _getFindSmokeColumn ()
	{
		return 'static_value';
	}

	public function testGetSetProductTypeAttributeId ()
	{
		$model = $this->_getCrudSmokeInstance();

		$this->assertSame($model, $model->setProductTypeAttributeId('2'));
		$this->assertSame(2, $model->getProductTypeAttributeId());
	}

	public function testSettingProductTypeAttributeIdToNullWritesNullToMysql ()
	{
		$model = $this->_getCrudSmokeInstance();

		// need to create a product_type_attribute to satisfy fk
		$attribute = new Store_Attribute;
		$attribute
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save attribute");

		$productType = new Store_Product_Type;
		$productType
			->setName('');
		$this->assertTrue($productType->save(), "failed to save product type: " . $model->getDb()->getErrorMsg());

		$productTypeAttribute = new Store_Product_Type_Attribute;
		$productTypeAttribute
			->setAttributeId($attribute->getId())
			->setProductTypeId($productType->getId());
		$this->assertTrue($productTypeAttribute->save(), "failed to save product type attribute: " . $model->getDb()->getErrorMsg());

		$this->assertSame($model, $model->setProductTypeAttributeId($productTypeAttribute->getId()), "set...() return value mismatch");
		$this->assertTrue($model->save(), "failed to insert model: " . $model->getDb()->getErrorMsg());

		$this->assertSame($model, $model->setProductTypeAttributeId(null));
		$this->assertNull($model->getProductTypeAttributeId());
		$this->assertTrue($model->save(), "failed to update model: " . $model->getDb()->getErrorMsg());

		$id = $model->getId();

		$model = new Store_Product_Attribute;
		$this->assertTrue($model->load($id), "failed to load model");
		$this->assertNull($model->getProductTypeAttributeId(), "product type attribute id is not null");
	}

	public function testGetSetProductId ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setProductId('2');
		$this->assertEquals(2, $model->getProductId());
	}

	public function testGetSetAttributeId ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setAttributeId('2');
		$this->assertEquals(2, $model->getAttributeId());
	}

	public function testGetSetSortOrder ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setSortOrder('2');
		$this->assertEquals(2, $model->getSortOrder());
	}

	public function testGetSetRequired ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setRequired('2');
		$this->assertEquals(true, $model->getRequired());
	}

	public function testGetAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$attribute = $model->getAttribute();
		$this->assertInstanceOf('Store_Attribute', $attribute);
		$this->assertEquals($this->_attribute->getId(), $attribute->getId());
	}

	public function testGetAttributeFailsForDeletedAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->_attribute->delete();
		$this->assertFalse($model->getAttribute());
	}

	public function testGetJson ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->setRequired(true)->setDisplayName('foo bar');

		$this->assertTrue($model->save(), "failed to save model");
		$content = $model->getJson($GLOBALS['ISC_CLASS_TEMPLATE']);
		$this->assertInternalType('array', $content, "invalid return value");
		$this->assertFalse(empty($content), "return array is empty");

		// at best these are basic smoke tests, better than nothing
		$this->assertSame($content['id'], $model->getUniqueId(), "unique id mismatch");
		$this->assertSame($content['displayName'], $model->getDisplayName(), "display name mismatch");
		$this->assertSame($content['required'], $model->getRequired(), 'required mismatch');
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
