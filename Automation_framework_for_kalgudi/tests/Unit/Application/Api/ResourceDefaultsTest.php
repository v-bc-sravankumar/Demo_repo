<?php

class Store_Api_Version_2_Resource_Thing extends Store_Api_Version_2_Resource
{
	protected $_name = "things";

	protected $_fields = array(
		'number_field' => array(
			'type' => 'int',
		),
		'text_field' => array(
			'type' => 'string',
		),
	);

	protected $_requirements = array(
		'post' => array(
			'text_field',
		),
	);

	protected $_readonlyFields = array(
		'put' => array(
			'number_field',
		),
		'post' => array(
			'number_field',
		),
	);

	protected $_searchFields = array(
		'number_field' => array('type' => 'int'),
		'text_field',
	);
}

class Store_Api_ResourceDefaultsTest extends PHPUnit_Framework_TestCase
{
	public function testResourceNameInflections()
	{
		$thing = new Store_Api_Version_2_Resource_Thing();

		$this->assertEquals("things", $thing->getPluralName());
		$this->assertEquals("thing", $thing->getSingularName());
	}

	public function testResourceOptionsSchema()
	{
		$thing = new Store_Api_Version_2_Resource_Thing();

		$schema = $thing->optionsAction(new Interspire_Request())->getData(true);

		$this->assertArrayHasKey('fields', $schema);
		$this->assertArrayHasKey('filters', $schema);

		$this->assertCount(2, $schema['fields']->getData(true));
		$this->assertCount(2, $schema['filters']->getData(true));

		$filters = $schema['filters']->getData(true);
		$this->assertEquals($filters[0]['name'], 'number_field');
		$this->assertEquals($filters[0]['type'], 'int');
		$this->assertEquals($filters[1]['name'], 'text_field');
		$this->assertEquals($filters[1]['type'], 'string');
	}
}