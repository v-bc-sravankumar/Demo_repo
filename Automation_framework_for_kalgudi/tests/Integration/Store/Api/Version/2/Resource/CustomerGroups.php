<?php
class Unit_Lib_Store_Api_Version_2_Resource_CustomerGroups extends Interspire_IntegrationTest
{
    /** @var $resource Store_Api_Version_2_Resource_CustomerGroups */
    private $resource = null;

    /** @var $group Store_Customer_Group */
    private static $group = null;

    public function setUp()
    {
        $this->resource = new Store_Api_Version_2_Resource_CustomerGroups();
    }

    public function tearDown()
    {
        $this->resource = null;
    }

    public static function setUpBeforeClass()
    {
        self::$group = self::createGroup();
    }

    private static function createGroup($name = 'default')
    {
        $group = new Store_Customer_Group();
        $group->setCategoryAccessType(Store_Customer_Group::CATEGORY_ACCESS_ALL);
        $group->setIsDefault(true);
        $group->setName($name);
        $group->setStorewideDiscountMethod(Store_Customer_Group::DISCOUNT_METHOD_FIXED);
        $group->setStorewideDiscountAmount(5.00);
        $group->save();

        return $group;
    }

    public static function tearDownAfterClass()
    {
        if (self::$group != null) self::$group->delete();
    }


    public function testGetActionFail()
    {
        try {
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
            $request->setUserParam('customer_groups', 9999999);
            $this->resource->getAction($request);
        } catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
            $this->assertEquals("The requested resource was not found.", $e->getMessage());
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function testGetActionSuccess()
    {
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
        $request->setUserParam('customer_groups', self::$group->getId());

        /** @var $result Store_Api_OutputDataWrapper */
        $result = $this->resource->getAction($request);
        $data = $result->getData();
        $this->assertNotEmpty($data);

        $this->assertEquals(self::$group->getId(), $data['id']);
        $this->assertEquals(self::$group->getName(), $data['name']);
        $this->assertEquals(self::$group->getCategoryAccessType(), $data['category_access']['type']);

        $discountRule = $data['discount_rules'][0];

        $this->assertEquals(self::$group->getStorewideDiscountAmount(), $discountRule['amount']);
        $this->assertEquals(self::$group->getStorewideDiscountMethod(), $discountRule['method']);
        $this->assertEquals('all', $discountRule['type']);
    }

    private function getFaultyPayload()
    {
        return array(
            "name" => "default",
            "is_default" => 1,
            "category_access" => array("type" => "all"),
            "discount_rules" => array(
                array(
                    "type" => "all",
                    "method" => "fixed",
                    "amount" => 5.0000,
                )
            )
        );
    }

    public function testPostActionFail()
    {
        try {
            $payload = $this->getFaultyPayload();
            $json = json_encode($payload);
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
            $this->resource->postAction($request)->getData(true);
        } catch (Store_Api_Exception_Request $e) {
            $this->assertEquals("The field 'is_default' is invalid.", $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testPostActionFailOnCategoryAccess()
    {
        try {
            $payload = $this->getFaultyPayload();
            $payload["name"] = "new group";
            $payload['is_default'] = true;
            $payload["category_access"]["categories"] = array(1);
            $json = json_encode($payload);
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
            $this->resource->postAction($request)->getData(true);
        } catch (Store_Api_Exception_Request_InvalidField $e) {
            $this->assertEquals("The field 'category_access' is invalid.", $e->getMessage());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testPostActionSuccess()
    {
        $payload = array(
            "name" => "new group",
            "is_default" => false,
            "category_access" => array("type" => "all"),
            "discount_rules" => array(
                array(
                    "type" => "all",
                    "method" => "fixed",
                    "amount" => 5.0000,
                )
            )
        );
        $json = json_encode($payload);

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $group = $this->resource->postAction($request)->getData(true);
        $this->assertNotEmpty($group);

        foreach ($payload as $key => $value) {
            $this->assertTrue(array_key_exists($key, $group));
            if (is_scalar($value)) {
                $this->assertEquals($value, $group[$key]);
            } else {
                $this->assertEmpty(array_diff($value, $group[$key]));
            }
        }

        // cleanup after ourselves like good citizens
        Store_Customer_Group::find($group['id'])->first()->delete();
    }

    public function testPutActionSuccess()
    {
        $payload = array(
            "name" => "new default",
            "is_default" => true,
            "category_access" => array("type" => "all"),
            "discount_rules" => array(
                array(
                    "type" => "all",
                    "method" => "fixed",
                    "amount" => 10.0000,
                )
            )
        );
        $json = json_encode($payload);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('customer_groups', self::$group->getId());
        $group = $this->resource->putAction($request)->getData(true);

        $this->assertNotEmpty($group);

        foreach ($payload as $key => $value) {
            $this->assertTrue(array_key_exists($key, $group));
            if (is_scalar($value)) {
                $this->assertEquals($value, $group[$key]);
            } else {
                $this->assertEmpty(array_diff($value, $group[$key]));
            }
        }
    }

    public function testPutActionFail()
    {
        try {
            $json = json_encode(array());
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
            $request->setUserParam('customer_groups', 999999999);
            $this->resource->putAction($request)->getData(true);
        } catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
            $this->assertEquals("The requested resource was not found.", $e->getMessage());
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function testDeleteActionSuccess()
    {
        $group = self::createGroup('group i can delete');
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
        $request->setUserParam('customer_groups', $group->getId());
        $this->resource->deleteAction($request);

        $this->assertEquals(0, Store_Customer_Group::find($group->getId())->count());
    }

    public function testDeleteActionFail()
    {
        try {
            $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
            $request->setUserParam('customer_groups', 999999999);
            $this->resource->deleteAction($request);
        } catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
            $this->assertEquals("The requested resource was not found.", $e->getMessage());
            $this->assertEquals(404, $e->getCode());
        }
    }
}
