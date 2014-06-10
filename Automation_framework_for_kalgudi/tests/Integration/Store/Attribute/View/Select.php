<?php

require_once dirname(__FILE__) . '/../View.php';

class Unit_Lib_Store_Attribute_View_Select extends Unit_Lib_Store_Attribute_View
{
	public function getTestInstance ()
	{
		return new Store_Attribute_View_Select;
	}
}
