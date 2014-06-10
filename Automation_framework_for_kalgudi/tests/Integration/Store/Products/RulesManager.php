<?php
class Unit_Products_Rules extends Interspire_IntegrationTest
{

	public function testSaveRuleActionId()
	{
		$data = array(
			'productHash' => '9998',
		);
		$resp = $this->saveRule($data);
		$this->assertEquals($resp['success'], 1);

		$result = Store_Product_Attribute_Rule::find((int)$resp['rule'])->first();
		$this->assertEquals($result->getProductId(), '9998');

		$this->assertTrue($this->deleteRule($resp['rule']));
	}

	public function testSaveRuleActionHash()
	{
		$data = array(
			'productHash' => 'ABCD12345',
			'loadFromProductId' => '0',
			'price_adjustment_direction' => '1',
			'rule-change-price-adjuster-value' => '25.21',
			'price_adjustment' => '25.2100',
			'price_adjuster' =>'Relative',
			'weight_adjustment_direction' => '1',
			'rule-change-weight-adjuster-value' => '1.05',
			'weight_adjustment' => '1.0500',
		);
		$resp = $this->saveRule($data);
		$this->assertEquals($resp['success'], 1);

		$result = Store_Product_Attribute_Rule::find((int)$resp['rule'])->first();
		$this->assertEquals($result->getProductHash(), 'ABCD12345');

		$this->assertTrue($this->deleteRule($resp['rule']));
	}

	public function testSaveRuleActionCloneBasic()
	{
		$data = array(
				'productHash' => 'ABCD12345',
				'loadFromProductId' => '0',
		);
		$resp1 = $this->saveRule($data);
		$this->assertEquals($resp1['success'], 1);

		$data = array(
				'productHash' => 'ABCD12345',
				'loadFromProductId' => '0',
				'cloneOf' => $resp1['rule'],
		);
		$resp2 = $this->saveRule($data);
		$this->assertEquals($resp2['success'], 1);

		$result1 = Store_Product_Attribute_Rule::find((int)$resp1['rule'])->first();
		$result2 = Store_Product_Attribute_Rule::find((int)$resp2['rule'])->first();

		$this->assertNotEquals($result1->getId(), $result2->getId());
		$this->assertNotEquals($result1->getSortOrder(), $result2->getSortOrder());
		$this->assertEquals($result2->getProductHash(), 'ABCD12345');

		$this->assertTrue($this->deleteRule($resp1['rule']));
		$this->assertTrue($this->deleteRule($resp2['rule']));
	}

	public function testSaveRuleActionCloneAdjusters()
	{
		$data = array(
			'productHash' => 'ABCD12345',
			'loadFromProductId' => '0',
			'adjust_price' => 1,
			'price_adjustment_direction' => '1',
			'rule-change-price-adjuster-value' => '25.21',
			'price_adjustment' => '25.2100',
			'price_adjuster' =>'Relative',
			'adjust_weight' => '1',
			'weight_adjustment_direction' => '1',
			'rule-change-weight-adjuster-value' => '1.05',
			'weight_adjustment' => '1.0500',
		);
		$resp1 = $this->saveRule($data);
		$this->assertEquals($resp1['success'], 1);

		//make sure adjusters were saved!
		$result1 = Store_Product_Attribute_Rule::find((int)$resp1['rule'])->first();
		$price1 = $result1->getPriceAdjuster();
		$weight1 = $result1->getWeightAdjuster();
		$this->assertNotNull($price1);
		$this->assertNotNull($weight1);
		$this->assertEquals($result1->getAdjustedPrice(), '25.21');
		$this->assertEquals($result1->getAdjustedWeight(), '1.05');

		$data = array(
			'productHash' => 'ABCD12345',
			'loadFromProductId' => '0',
			'cloneOf' => $resp1['rule'],
		);
		$resp2 = $this->saveRule($data);
		$this->assertEquals($resp2['success'], 1);

		//make sure adjusters were cloned!
		$result2 = Store_Product_Attribute_Rule::find((int)$resp2['rule'])->first();
		$price2 = $result2->getPriceAdjuster();
		$weight2 = $result2->getWeightAdjuster();
		$this->assertNotNull($price2);
		$this->assertNotNull($weight2);
		$this->assertEquals($result2->getAdjustedPrice(), '25.21');
		$this->assertEquals($result2->getAdjustedWeight(), '1.05');

		$this->assertNotEquals($result1->getId(), $result2->getId());
		$this->assertNotEquals($result1->getSortOrder(), $result2->getSortOrder());
		$this->assertEquals($result2->getProductHash(), 'ABCD12345');

		$this->assertTrue($this->deleteRule($resp1['rule']));
		$this->assertTrue($this->deleteRule($resp2['rule']));
	}

	private function saveRule($data)
	{
		$base = $this->getBaseData();
		$data = array_merge($base, $data);

		$request = new Interspire_Request(null, $data);

		$ruleManager = new ISC_ADMIN_REMOTE_PRODUCT_RULESMANAGER();
		return $ruleManager->saveRuleAction($request);
	}

	private function getBaseData()
	{
		return array(
			'ajaxSubmit' => '1',
			'productHash' => '',
			'loadFromProductId' => '1',
			'product_type' => '',
			'enabled' => 1,
			'cloneOf' => '',
			'attribute_values' => array(array(111=>1), array(111 =>2)),
			'price_adjustment_direction' => '1',
			'rule-change-price-adjuster-value' => '0.00',
			'price_adjustment' => '0.0000',
			'price_adjuster' =>'Relative',
			'weight_adjustment_direction' => '1',
			'rule-change-weight-adjuster-value' => '0.00',
			'weight_adjustment' => '0.0000',
			'purchasing_disabled' => '0',
			'purchasingDisabledAction' => 'message',
			'purchasing_disabled_message' => '',
		);
	}

	private function deleteRule($ruleId)
	{
		$rule = new Store_Product_Attribute_Rule;
		$rule->load($ruleId);
		return $rule->delete();
	}
}
