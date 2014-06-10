<?php

class Integration_Lib_Store_Api_Version_2_Resource_Coupons_Count extends Interspire_IntegrationTest
{
    public function setUp()
    {
        $this->resource = new Store_Api_Version_2_Resource_Coupons_Count();
    }

    public function tearDown()
    {
        $this->resource = null;
    }

    public function getCouponDataSet()
    {
        return array(
            array(
                array(
                    array(
                        'Name' => 'test coupon 1',
                        'Code' => uniqid(),
                        'Type' => 'per_item_discount',
                        'Amount' => '10.00',
                        'AppliesTo' => array(
                            'entity' => 'categories',
                            'ids' => array(array('value' => 1), array('value' => 2)),
                        ),
                    ),
                    array(
                        'Name' => 'test coupon 2',
                        'Code' => uniqid(),
                        'Type' => 'per_item_discount',
                        'Amount' => '20.00',
                        'AppliesTo' => array(
                            'entity' => 'categories',
                            'ids' => array(array('value' => 1), array('value' => 2)),
                        ),
                    ),
                    array(
                        'Name' => 'test coupon 3',
                        'Code' => uniqid(),
                        'Type' => 'per_item_discount',
                        'Amount' => '30.00',
                        'AppliesTo' => array(
                            'entity' => 'categories',
                            'ids' => array(array('value' => 1), array('value' => 2)),
                        ),
                    )
                )
            )
               );
    }

    /**
     * Deletes all the existing coupons in the database.
     *
     * @return boolean
     */
    private function _deleteAllCoupon()
    {
        $data = Store_Coupon::find();
        return $data->deleteAll();;
    }

    /**
     * Insert coupons into database.
     *
     * @param array $data
     * @throws Exception
     * @return Store_Coupon
     */
    private function _createDummyCoupon($data = null)
    {
        $coupon = new Store_Coupon();
        foreach ($data as $k => $v) {
            $method = 'set'.$k;
            $coupon->$method($v);
        }
        if (!$coupon->save()) {
            throw new Exception("Unable to create coupon.");
        }
        return $coupon;
    }

    /**
     * Send a GET request to Coupons_Count resource and test for correct count for existing coupons.
     *
     * @dataProvider getCouponDataSet
     * @param array $content
     */
    public function testCountGetAction($content)
    {
        $this->_deleteAllCoupon();

        foreach ($content as $coupons => $coupon) {
            $this->_createDummyCoupon($coupon);
        }

        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'), $json);
        $OutputDataWrapper = $this->resource->getAction($request);
        $data = $OutputDataWrapper->getData();
        $count = $data[0]['count'];
        $this->assertEquals(count($content),$count);
    }

}
