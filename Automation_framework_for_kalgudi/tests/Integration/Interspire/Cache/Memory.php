<?php

require_once dirname(__FILE__) . '/Interface.php';

class Unit_Lib_Interspire_Cache_Memory extends Unit_Lib_Interspire_Cache_Interface
{
	public function getCache()
	{
		return new Interspire_Cache(new Interspire_Cache_Memory);
	}
}
