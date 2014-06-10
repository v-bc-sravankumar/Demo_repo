<?php

abstract class Integration_Store_Attribute_Type_Configurable extends Interspire_IntegrationTest
{
	abstract protected function _getInstance ();

	public function testGetIconPathSmokeTest ()
	{
		$type = $this->_getInstance();
		$result = $type->getIconPath();
		$this->assertInternalType('string', $result);
		$this->assertNotEmpty($result);
		$this->assertNotSame(false, strpos($result, '.'));
	}

	public function testGetJsonSmokeTest ()
	{
		$productAttribute = new Store_Product_Attribute;
		$type = $this->_getInstance();
		$result = $type->getJson($productAttribute);
		$this->assertInternalType('array', $result);
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey('id', $result);
		$this->assertArrayHasKey('displayName', $result);
		$this->assertArrayHasKey('required', $result);
		$this->assertArrayHasKey('condition', $result);
		$this->assertArrayHasKey('validation', $result);
	}

	public function testGetJqueryPluginNameSmokeTest ()
	{
		$type = $this->_getInstance();
		$result = $type->getJqueryPluginName();
		$this->assertInternalType('string', $result);
		$this->assertNotEmpty($result);
		$this->assertStringStartsWith('productOption', $result);
		$this->assertGreaterThan(strlen('productOption'), strlen($result));
	}

	public function testGetFormJavaScriptSmokeTest ()
	{
		$productAttribute = new Store_Product_Attribute;
		$type = $this->_getInstance();
		$result = $type->getFormJavaScript($productAttribute);
		$this->assertInternalType('string', $result);
		$this->assertNotEmpty($result);
		$this->assertStringStartsWith('$(function(){', $result);
		$this->assertStringEndsWith("});\n", $result);
		$this->assertNotSame(false, strpos($result, $type->getJqueryPluginName()));
	}

	public function testGetValidationMessagesSmokeTest ()
	{
		$type = $this->_getInstance();
		$result = $type->getValidationMessages('name', 'value');
		$this->assertInternalType('array', $result);
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey('required', $result);
	}

	public function testGetValidationMessageReplacementsSmokeTest ()
	{
		$type = $this->_getInstance();
		$result = $type->getValidationMessageReplacements('name?');
		$this->assertInternalType('array', $result);
		$this->assertNotEmpty($result);
		$this->assertArrayHasKey('displayName', $result);
		$this->assertStringEndsNotWith('?', $result['displayName']);
	}
}
