<?php

require_once(dirname(__FILE__) . '/Base.php');

class Unit_KeyStore_Mysql extends Unit_KeyStore_Base
{
	public function instance ()
	{
		return Interspire_KeyStore_Mysql::instance();
	}
}
