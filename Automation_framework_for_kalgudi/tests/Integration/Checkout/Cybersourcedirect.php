<?php

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Cybersourcedirect extends Unit_Checkout_Online
{
	public $moduleName = 'cybersourcedirect';

	public $vars = array(
		'merchantid' => 'interspire',
		'securitykey' => '7pw8jmzCwgWWv9CEX/XyUjUJPwzkv3eqS9/AcS8NSwCV+GCZCP21UYPwoFSOFAir49ag658dFJOqAC+6qeZ51Nr9D1jEJInV8qpuHfkmDUjNRntc2V9e7wXiHYvgmA+rjkOUVqkT8YMlQ8Ggo6HrEeO3Oq0wlbjBiOB9zYhjvt/0bJJU6Po9ptArEYRf9fJSNQk/DOS/d6pL38BxLw1LAJX4YJkI/bVRg/CgVI4UCKvj1qDrnx0Uk6oAL7qp5nnU2v0PWMQkidXyqm4d+SYNSM1Ge1zZX17vBeIdi+CYD6uOQ5RWqRPxgyVDwaCjoesR47c6rTCVuMGI4H3NiGO+3w==',
		'transactiontype' => 'SALE',
		'cardcode' => 'YES',
		'testmode' => 'YES',
	);
}
