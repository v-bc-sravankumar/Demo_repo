<?php

require_once(dirname(__FILE__) . '/Base.php');

class Unit_EmailIntegration_MailChimp_Field_Number extends Unit_EmailIntegration_MailChimp_Field_Base
{
	public $providerFieldClassName = 'Interspire_EmailIntegration_MailChimp_Field_Number';

	/**
	* @return array
	*/
	public function dataProviderFromSubscriptionToProvider ()
	{
		$data = array(
			array(new Interspire_EmailIntegration_Field_Address, 'Test.', 'Test.'),
			array(new Interspire_EmailIntegration_Field_Address, array('Test', 'test'), 'Test test'),
			array(new Interspire_EmailIntegration_Field_Bool, true, 1),
			array(new Interspire_EmailIntegration_Field_Bool, false, 0),
			array(new Interspire_EmailIntegration_Field_Currency, 'Test.', ''),
			array(new Interspire_EmailIntegration_Field_Currency, array('numeric' => 1, 'formatted' => '$1.00'), 1),
			array(new Interspire_EmailIntegration_Field_Date, 1273025038, 1273025038),
			array(new Interspire_EmailIntegration_Field_Date, '2010-05-05', 1273017600),
			array(new Interspire_EmailIntegration_Field_Email, 'test@example.com', 'test@example.com'),
			array(new Interspire_EmailIntegration_Field_Float, 1.2, 1.2),
			array(new Interspire_EmailIntegration_Field_Int, 2, 2),
			array(new Interspire_EmailIntegration_Field_Ip, '127.0.0.1', '127.0.0.1'),
			array(new Interspire_EmailIntegration_Field_String, 'Test', 'Test'),
		);

		return $data;
	}
}
