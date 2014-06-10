<?php

require_once dirname(__FILE__) . '/ModelLike_TestCase.php';

class Unit_Lib_Store_Review extends ModelLike_TestCase
{
    private $productInstance;
    private $productId;

    const ORIG_RATING = 2;
    const ORIG_COUNT = 1;
    const ORIG_AVG = 2;
    const VENDOR = 3;

    public function setUp(){
        // create a test product the review is based on
        $product = $this->_getProductInstance();
        $this->productId = $product->add(array(
            'prodname' => 'test product' . rand(),
            'prodratingtotal' => self::ORIG_RATING,
            'prodnumratings' => self::ORIG_COUNT,
            'average_rating' => self::ORIG_AVG,
            'prodvendorid' => self::VENDOR,
        ));
    }

    public function tearDown()
    {
        $this->_getProductInstance()->delete($this->productId);
        Store_Review::find(null)->deleteAll();

        parent::tearDown();
    }

    protected function _getProductInstance()
    {
        if($this->productInstance === null){
            $this->productInstance = new Store_Product_Gateway();
        }
        return $this->productInstance;
    }

    protected function _getReviewProduct()
    {
        return $this->_getProductInstance()->get($this->productId);
    }

    protected function _getFindSmokeColumn ()
    {
        return 'revfromname';
    }

    protected function _getCrudSmokeGetMethod ()
    {
        return 'getFromName';
    }

    protected function _getCrudSmokeSetMethod ()
    {
        return 'setFromName';
    }

    protected function _getCrudSmokeInstance ()
    {
        $model = new Store_Review;
        $model->setFromName($this->_getCrudSmokeValue1());
        return $model;
    }

    private function buildReview($rating)
    {
        $model = $this->_getCrudSmokeInstance();
        $model->setProductId($this->productId);
        $model->setRating($rating);
        return $model;
    }

    private function buildAutoApprovedReview($rating)
    {
        $model = $this->buildReview($rating);
        $model->setStatus(Store_Review::STATUS_APPROVED);
        $model->save();
        return $model;
    }

    private function buildNeedApprovalReview($rating)
    {
        $model = $this->buildReview($rating);
        $model->setStatus(Store_Review::STATUS_UNAPPROVED);
        $model->save();
        return $model;
    }

    private function assertOrigProductRating()
    {
        $product = $this->_getReviewProduct();
        $this->assertEquals(self::ORIG_RATING,(int)$product['prodratingtotal']);
        $this->assertEquals(self::ORIG_COUNT,(int)$product['prodnumratings']);
        $this->assertEquals(self::ORIG_AVG, $product['average_rating']);
    }

    private function assertProductRatingUpdated($rating)
    {
        $product = $this->_getReviewProduct();
        $this->assertEquals(self::ORIG_RATING + $rating, (int)$product['prodratingtotal']);
        $this->assertEquals(self::ORIG_COUNT + 1,(int)$product['prodnumratings']);
        $this->assertEquals(((self::ORIG_RATING + $rating)/(self::ORIG_COUNT + 1)), $product['average_rating']);
    }

    public function testCreateAutoApprovedReview()
    {
        $this->buildAutoApprovedReview(4);
        $this->assertProductRatingUpdated(4);
    }

    public function testCreateNeedApprovalReview()
    {
        $this->buildNeedApprovalReview(4);
        $this->assertOrigProductRating();
    }

    public function testApproveReviewOnProductNotBelongToVendor()
    {
        $model = $this->buildNeedApprovalReview(4);

        $model->setStatus(1);
        $model->setProductVendorId(self::VENDOR+1);
        $model->save();

        $this->assertOrigProductRating();
    }

    public function testApproveUnprovedReview()
    {
        $model = $this->buildNeedApprovalReview(4);

        $model->setStatus(Store_Review::STATUS_APPROVED);
        $model->save();

        $this->assertProductRatingUpdated(4);
    }

    public function testApproveAlreadyApprovedReview()
    {
        $model = $this->buildAutoApprovedReview(4);

        $model->setStatus(Store_Review::STATUS_APPROVED);
        $model->save();

        $this->assertProductRatingUpdated(4);
    }

    public function testDisproveUnprovedReview()
    {
        $model = $this->buildNeedApprovalReview(4);

        $model->setStatus(Store_Review::STATUS_DISAPPROVED);
        $model->save();

        $this->assertOrigProductRating();
    }

    public function testDisproveApprovedReview()
    {
        $model = $this->buildAutoApprovedReview(4);

        $model->setStatus(Store_Review::STATUS_DISAPPROVED);
        $model->save();

        $this->assertOrigProductRating();
    }

    public function testDeleteUnprovedReview()
    {
        $model = $this->buildNeedApprovalReview(4);
        $model->delete();
        $this->assertOrigProductRating();
    }

    public function testDeleteApprovedReview()
    {
        $model = $this->buildAutoApprovedReview(4);
        $model->delete();
        $this->assertOrigProductRating();
    }

    public function testFindApprovedReviewsByProductId()
    {
        $this->buildAutoApprovedReview(4);
        $this->buildNeedApprovalReview(1);
        $reviews = \Store_Review::findApprovedReviewByProductId($this->productId);
        $this->assertEquals(1, $reviews->count());
        $this->assertEquals(4, $reviews->first()->getRating());
    }

    public function testCloneCorrectlySubClones()
    {
        $this->markTestSkipped('This is not run by default?');
    }
}
