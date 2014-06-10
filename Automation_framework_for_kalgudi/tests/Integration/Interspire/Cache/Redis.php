<?php

require_once dirname(__FILE__) . '/Interface.php';

class Unit_Lib_Interspire_Cache_Redis extends Unit_Lib_Interspire_Cache_Interface
{
	public function getCache()
	{
		return new Interspire_Cache($GLOBALS["app"]["caching.backends.redis"]);
	}
}
