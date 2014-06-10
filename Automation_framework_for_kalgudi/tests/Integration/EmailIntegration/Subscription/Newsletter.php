<?php

require_once dirname(__FILE__) . '/Base.php';

class Unit_EmailIntegration_Subscription_Newsletter extends Unit_EmailIntegration_Subscription_Base
{
	const TEST_FIRST_NAME = 'Gwilym';

	public function getSubscriptionInstance ()
	{
		return new Interspire_EmailIntegration_Subscription_Newsletter(Unit_EmailIntegration_Subscription_Base::TEST_EMAIL, self::TEST_FIRST_NAME);
	}
}
