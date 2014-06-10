<?php

require_once dirname(__FILE__) . '/Interface.php';

class Integration_Lib_Interspire_Cache_Memcache extends Unit_Lib_Interspire_Cache_Interface
{
	public function getCache()
	{
		if (!getenv("MEMCACHE_SERVER")) {
			return false;
		}

		return new Interspire_Cache(new Interspire_Cache_Memcache);
	}
}
