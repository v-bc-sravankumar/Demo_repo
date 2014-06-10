<?php

class LegacyTableModel extends DataModel_Record
{
	protected $_tableName = 'model_like_legacy_primary_key_test';
	protected $_data = array(
		'legacyid' => null,
		'data' => null,
	);

	protected $_primaryKey = 'legacyid';

	public static function findByData($data)
	{
		$model = new self;
		return self::find("`data` = '" . $model->getDb()->Quote($data) . "'");
	}

	public function getData()
	{
		return $this->_getData('data');
	}

	public function setData($data)
	{
		return $this->_setData('data', $data);
	}
}
