<?php

require_once dirname(__FILE__) . '/../ModelLike_TestCase.php';

class Unit_Lib_Store_Product_Type extends ModelLike_TestCase
{
	protected function _getCrudSmokeInstance ()
	{
		$model = new Store_Product_Type;
		$model->setName($this->_getCrudSmokeValue1());
		return $model;
	}

	/**
	 * @covers Store_Product_Type::addAttribute
	 */
	public function testAddAttribute ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type");

		$attribute = new Store_Attribute;
		$attribute->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$this->assertInstanceOf('Store_Product_Type_Attribute', $model->addAttribute($attribute));
	}

	/**
	 * @covers Store_Product_Type::addAttribute
	 */
	public function testAddAttributeBeforeProductTypeSaveFails ()
	{
		$model = $this->_getCrudSmokeInstance();

		$attribute = new Store_Attribute;
		$attribute->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$this->assertFalse($model->addAttribute($attribute), "addAttribute worked but should have failed");
	}

	/**
	 * @covers Store_Product_Type::addAttribute
	 */
	public function testAddAttributeBeforeAttributeSaveFails ()
	{
		$this->markTestSkipped(); // skipping this for BC only due to missing foreign keys

		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type");

		$attribute = new Store_Attribute;
		$attribute->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);

		$productTypeAttribute = $model->addAttribute($attribute);
		$this->assertInstanceOf('Store_Product_Type_Attribute', $productTypeAttribute);
		$this->assertFalse($productTypeAttribute->save(), "saving Store_Product_Type_Attribute produced by addAttribute worked but should have failed");
	}

	/**
	 * @covers Store_Product_Type::_beforeDelete
	 */
	public function testDeleteCascadesToProductTypeAttributes ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type");

		$attribute = new Store_Attribute;
		$attribute->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$typeAttribute = $model->addAttribute($attribute);
		$this->assertInstanceOf('Store_Product_Type_Attribute', $typeAttribute, "addAttribute failed");
		$this->assertTrue($typeAttribute->save(), "failed to save Store_Product_Type_Attribute");
		$this->assertTrue($typeAttribute->load(), "failed to save-load Store_Product_Type_Attribute");
		$this->assertTrue($model->delete(), "failed to delete Store_Product_Type");
		$this->assertFalse($typeAttribute->load(), "loading Store_Product_Type_Attribute worked but should have failed");
	}

	/**
	 * @covers Store_Product_Type::_beforeDelete
	 */
	public function testDeleteClearsProductsAssignedToProductType ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type");

		$db = $model->getDb();

		$entity = new Store_Product_Gateway;
		$productId = $entity->add(array(
			'prodname' => 'Unit_Lib_Store_Product_Test',
			'product_type_id' => $model->getId(),
		));
		$this->assertNotEquals(false, $productId, "failed to add test product");

		$this->assertTrue($model->delete(), "failed to delete Store_Product_Type");

		$product = $entity->get($productId);
		$this->assertNotEquals(false, $product);
		$this->assertNull($product['product_type_id']);
	}

	/**
	 * @covers Store_Product_Type::getProductTypeAttributes
	 */
	public function testClonedProductTypeInstanceReturnsAttributesFromOriginalInstance ()
	{
		$model = $this->_getCrudSmokeInstance();
		$this->assertTrue($model->save(), "failed to save Store_Product_Type");

		$attribute = new Store_Attribute;
		$attribute->setName('foo')
			->setType(new Store_Attribute_Type_Configurable_Entry_Text);
		$this->assertTrue($attribute->save(), "failed to save Store_Attribute");

		$typeAttribute = $model->addAttribute($attribute);
		$this->assertInstanceOf('Store_Product_Type_Attribute', $typeAttribute, "addAttribute failed");
		$this->assertTrue($typeAttribute->save(), "failed to save Store_Product_Type_Attribute");

		$clone = $model->copy();
		$this->assertNull($clone->getId(), "cloned product_type id mismatch");
		$subClone = $clone->getProductTypeAttributes()->first();
		$this->assertInstanceOf('Store_Product_Type_Attribute', $subClone);
		$this->assertEquals(null, $subClone->getId(), "product_type_attribute id mismatch");
		$this->assertEquals($subClone->getAttributeId(), $attribute->getId(), "attribute id mismatch");
	}

	/**
	 * @covers Store_Product_Type::getAssignedProductsCount
	 */
	public function testGetAssignedProductsCount ()
	{
		$model = $this->_getCrudSmokeInstance();
		$model->save();

		$this->assertSame(0, $model->getAssignedProductsCount(), "failed count before assigning products");

		$entity = new Store_Product_Gateway;
		$productId = $entity->add(array(
			'prodname' => md5(uniqid('', true)),
			'product_type_id' => $model->getId(),
		));
		$this->assertNotEquals(false, $productId, "failed to add test product");

		$this->assertSame(1, $model->getAssignedProductsCount(), "failed count after assigning one product");

		$entity = new Store_Product_Gateway;
		$productId = $entity->add(array(
			'prodname' => md5(uniqid('', true)),
			'product_type_id' => $model->getId(),
		));
		$this->assertNotEquals(false, $productId, "failed to add test product(2)");

		$this->assertSame(2, $model->getAssignedProductsCount(), "failed count after assigning two products");
	}

	public function testCloneCorrectlySubClones()
	{
		$this->markTestSkipped('This is not run by default?');
	}
}
