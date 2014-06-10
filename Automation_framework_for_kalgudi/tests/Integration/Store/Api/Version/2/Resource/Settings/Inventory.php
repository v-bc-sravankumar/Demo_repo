<?php

use Store\Settings\InventorySettings;

class Integration_Store_Api_Version_2_Resource_Settings_InventoryTest extends Interspire_IntegrationTest
{
  private $createdUsers = array();

  private function getApiRequest($method, $user = null, $data = null)
  {
    $server = array(
      'REQUEST_URI' => '/api/v2/settings/inventory',
      'REQUEST_METHOD' => $method,
      'HTTPS' => 'on',
    );

    if ($user !== null) {
		$server['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($user->getUsername() . ':' . $user->getUserToken());
	}

    $body = null;
    if ($data !== null) {
      $body = json_encode($data);
      $server['CONTENT_TYPE'] = 'application/json';
    }

    return new Interspire_Request(null, null, null, $server, $body);
  }

  private function getTestApiUser($settingsPermission = true)
  {
    $username = uniqid('api_user_');

    $user = new Store_User();
    $user
      ->setUsername(uniqid('api_user_'))
      ->setUserStatus(1)
      ->setUserApi(1)
      ->setUserToken('token')
      ->setUserEmail($username . '@example.com');

    if (!$user->save()) {
      $this->fail('Failed to save API test user: ' . $username);
    }

    if ($settingsPermission) {
      // add the manage settings permission for our user
      $permission = new Store_Permission();
      $permission
        ->setUserId($user->getId())
        ->setPermissionId(AUTH_Manage_Settings);

      if (!$permission->save()) {
        $this->fail('Failed to save manage settings permission for API test user');
      }
    }

    $this->createdUsers[] = $user;

    return $user;
  }

  private function executeRequestForMethod($user, $method)
  {
    $request = $this->getApiRequest($method, $user);
    return $this->executeRequest($request);
  }

  private function executeRequest($request)
  {
    $api = new Store_Api();
    $api->authenticate($request);
    $api->executeRequest($request, new Store_Api_Version_2_Resource_Settings_Inventory(), 'json');
    return $request->getResponse();
  }

  private $originalAppPath;

  public function setUp()
  {
    $this->originalAppPath = Store_Config::get('AppPath');
    Store_Config::override('AppPath', '');
  }

  public function tearDown()
  {
    foreach ($this->createdUsers as $user) {
      $user->delete();
    }

    Store_Config::override('AppPath', $this->originalAppPath);
  }

  public function testGetInventorySettingsForAllowedUserSucceeds()
  {
    $expectedKeys = array(
      'product_out_of_stock_behavior',
      'option_out_of_stock_behavior',
      'update_stock_behavior',
      'edit_order_stock_adjustment',
      'delete_order_stock_adjustment',
      'refund_order_stock_adjustment',
      'stock_level_display',
      'show_pre_order_stock_levels',
      'default_out_of_stock_message',
      'show_out_of_stock_message',
      'low_stock_notification_email_address',
      'out_of_stock_notification_email_address',
    );

    $response = $this->executeRequestForMethod($this->getTestApiUser(), 'GET');
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($expectedKeys, array_keys($result));
  }

  /**
   * @expectedException Store_Api_Exception_Resource_NoPermission
   */
  public function testGetInventorySettingsAsOtherUserFails()
  {
    $this->executeRequestForMethod($this->getTestApiUser(false), 'GET');
  }

  /**
   * @expectedException Store_Api_Exception_Resource_ResourceNotFound
   */
  public function testGetInventorySettingsWithResourceIdFails()
  {
    $request = $this->getApiRequest('GET', $this->getTestApiUser());
    $request->setUserParam('inventory', 1);
    $this->executeRequest($request);
  }

  public function testUpdateInventorySettingsForAllowedUserSucceeds()
  {
    $data = array(
      'product_out_of_stock_behavior' => 'hide_product_and_accessible',
      'option_out_of_stock_behavior' => 'hide_option',
      'update_stock_behavior' => 'order_placed',
      'edit_order_stock_adjustment' => true,
      'delete_order_stock_adjustment' => true,
      'refund_order_stock_adjustment' => true,
      'stock_level_display' => 'show_when_low',
      'show_pre_order_stock_levels' => false,
      'default_out_of_stock_message' => 'Product out of stock',
      'show_out_of_stock_message' => true,
      'low_stock_notification_email_address' => 'foo@example.com',
      'out_of_stock_notification_email_address' => 'bar@example.com',
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($data, $result);
  }

  /**
   * @expectedException Store_Api_Exception_Resource_NoPermission
   */
  public function testUpdateInventorySettingsAsOtherUserFails()
  {
    $this->executeRequestForMethod($this->getTestApiUser(false), 'PUT');
  }

  /**
   * @expectedException Store_Api_Exception_Resource_ResourceNotFound
   */
  public function testUpdateInventorySettingsWithResourceIdFails()
  {
    $request = $this->getApiRequest('PUT', $this->getTestApiUser());
    $request->setUserParam('inventory', 1);
    $this->executeRequest($request);
  }

  /**
   * @expectedException Store_Api_Exception_Resource_MethodNotFound
   */
  public function testCreateInventorySettingsFails()
  {
    Store_RequestRouter_StoreApi::getApiRoute($this->getApiRequest('POST'));
  }

  /**
   * @expectedException Store_Api_Exception_Resource_MethodNotFound
   */
  public function testDeleteInventorySettingsFails()
  {
    Store_RequestRouter_StoreApi::getApiRoute($this->getApiRequest('DELETE'));
  }

  /**
   * @dataProvider productOutOfStockBehaviors
   */
  public function testProductOutOfStockBehavior($behavior)
  {
    $data = array(
      'product_out_of_stock_behavior' => $behavior,
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($result['product_out_of_stock_behavior'], $behavior);
  }

  public function productOutOfStockBehaviors()
  {
    return array(
      array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE_AND_ACCESSIBLE),
      array(InventorySettings::PRODUCT_OUT_OF_STOCK_HIDE),
      array(InventorySettings::PRODUCT_OUT_OF_STOCK_REDIRECT_TO_CATEGORY),
      array(InventorySettings::PRODUCT_OUT_OF_STOCK_DO_NOTHING),
    );
  }

  /**
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidProductOutOfStockBehavior()
  {
    $data = array(
      'product_out_of_stock_behavior' => 'foo',
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $this->executeRequest($request);
  }

  /**
   * @dataProvider optionOutOfStockBehaviors
   */
  public function testOptionOutOfStockBehavior($behavior)
  {
    $data = array(
      'option_out_of_stock_behavior' => $behavior,
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($result['option_out_of_stock_behavior'], $behavior);
  }

  public function optionOutOfStockBehaviors()
  {
    return array(
      array(InventorySettings::OPTION_OUT_OF_STOCK_HIDE),
      array(InventorySettings::OPTION_OUT_OF_STOCK_LABEL),
      array(InventorySettings::OPTION_OUT_OF_STOCK_DO_NOTHING),
    );
  }

  /**
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidOptionOutOfStockBehavior()
  {
    $data = array(
      'option_out_of_stock_behavior' => 'foo',
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $this->executeRequest($request);
  }

  /**
   * @dataProvider updateStockBehaviors
   */
  public function testUpdateStockBehavior($behavior)
  {
    $data = array(
      'update_stock_behavior' => $behavior,
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($result['update_stock_behavior'], $behavior);
  }

  public function updateStockBehaviors()
  {
    return array(
      array(InventorySettings::UPDATE_STOCK_ORDER_PLACED),
      array(InventorySettings::UPDATE_STOCK_ORDER_COMPLETED_OR_SHIPPED),
    );
  }

  /**
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidUpdateStockBehavior()
  {
    $data = array(
      'update_stock_behavior' => 'foo',
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
  }

  /**
   * @dataProvider boolFields
   */
  public function testBoolFields($field)
  {
    $data = array($field => true);
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());
    $this->assertTrue($result[$field]);

    $data = array($field => false);
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());
    $this->assertFalse($result[$field]);
  }

  /**
   * @dataProvider boolFields
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidBoolFields($field)
  {
    $data = array($field => 'foo');
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $this->executeRequest($request);
  }

  public function boolFields()
  {
      return array(
        array('show_pre_order_stock_levels'),
        array('edit_order_stock_adjustment'),
        array('delete_order_stock_adjustment'),
        array('refund_order_stock_adjustment'),
        array('show_out_of_stock_message'),
      );
  }

  /**
   * @dataProvider stockLevelDisplays
   */
  public function testStockLevelDisplay($display)
  {
    $data = array(
      'stock_level_display' => $display,
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());

    $this->assertEquals($result['stock_level_display'], $display);
  }

  public function stockLevelDisplays()
  {
    return array(
      array(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW),
      array(InventorySettings::STOCK_LEVEL_DISPLAY_SHOW_WHEN_LOW),
      array(InventorySettings::STOCK_LEVEL_DISPLAY_DONT_SHOW),
    );
  }

  /**
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidStockLevelDisplay()
  {
    $data = array(
      'stock_level_display' => 'foo',
    );

    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
  }

  /**
   * @dataProvider emailFields
   */
  public function testEmailFields($field)
  {
    $data = array($field => 'foo@example.com');
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());
    $this->assertEquals($result[$field], 'foo@example.com');
  }

  /**
   * @dataProvider emailFields
   */
  public function testEmailFieldsAllowNull($field)
  {
    $data = array($field => null);
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $response = $this->executeRequest($request);
    $result = Store_Json::decode($response->getBody());
    $this->assertNull($result[$field]);
  }

  /**
   * @dataProvider emailFields
   * @expectedException Store_Api_Exception_Request_InvalidField
   */
  public function testInvalidEmailField($field)
  {
    $data = array($field => 'foo');
    $request = $this->getApiRequest('PUT', $this->getTestApiUser(), $data);
    $this->executeRequest($request);
  }

  public function emailFields()
  {
    return array(
      array('low_stock_notification_email_address'),
      array('out_of_stock_notification_email_address'),
    );
  }
}
