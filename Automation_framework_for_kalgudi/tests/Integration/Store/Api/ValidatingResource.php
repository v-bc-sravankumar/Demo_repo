<?php

class Unit_Lib_Store_Api_ValidatingResource extends Store_Api_Resource
{

	protected $_fields = array(
		'objects' => array(
			'type' => 'object_array',
			'fields' => array(
				'id' => array(
					'type' => 'int',
				),
			),
		),
	);

}