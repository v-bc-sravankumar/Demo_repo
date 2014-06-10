<?php

class Integration_Store_Api_Version_2_Resource_Products_Reviews extends Interspire_IntegrationTest
{
    public function setUp()
    {
        $this->resource = new Store_Api_Version_2_Resource_Products_Reviews();
    }

    public function tearDown()
    {
        $this->resource = null;
    }

    public function getReviewDataSetForPut()
    {
        return array(
            array(
                75,
                array(
                        'author' => 'Author 1',
                        'rating' => 5,
                        'review' => 'Review 1',
                        'title' => 'Title 1',
                        'status' => 1,
                     ),
                array(
                        'author' => 'Updated Author',
                        'rating' => 4,
                        'review' => 'Updated Review',
                        'title' => 'Updated Title',
                        'status' => 0,
                )
            ),
               );
    }

    public function getReviewDataSet()
    {
        return array(
            array(
                75,
                array(
                        'author' => 'Author 1',
                        'rating' => 5,
                        'review' => 'Review 1',
                        'title' => 'Title 1',
                        'status' => 1,
                      )
            ),
            array(
                35,
                array(
                        'author' => 'Author 2',
                        'rating' => 5,
                        'review' => 'Review 2',
                        'title' => 'Title 2',
                        'status' => 1,
                )
            )
              );
    }

    public function dataSetForRequiredField()
    {
        return array(
            array(
                70,
                array(
                        'review' => 'Review',
                        'title' => 'Title',
                )
            ),
            array(
                40,
                array(
                        'rating' => 5,
                        'title' => 'Title',
                )
            ),
            array(
                50,
                array(
                        'rating' => 5,
                        'review' => 'Review',
                )
            )
            );
    }

    public function dataSetForRestrictedField()
    {
        return array(
            array(
                75,
                array(
                        'id' => 1,
                        'rating' => 5,
                        'review' => 'Review',
                        'title' => 'Title',
                )
            ),
            array(
                76,
                array(
                        'product_id' => 75,
                        'rating' => 5,
                        'review' => 'Review',
                        'title' => 'Title',
                )
            ),
            );
    }

    public function dataSetForUnsupportedField()
    {
        return array(
            array(
                65,
                array(
                    'date' => 'Fri, 11 Apr 2014 06:18:19 +0000',
                    'rating' => 5,
                    'review' => 'Review',
                    'title' => 'Title',
                )
            )
        );
    }

    /**
     * Accept an array having keys similar to API fields and returns a Store_Review object containing corresponding values.
     *
     * @param int $productId
     * @param Array $data
     * @return Store_Review
     */
    protected function createReview($productId, $data)
    {
        $review = new Store_Review();


        $review->setTitle($data['title']);
        $review->setText($data['review']);
        $review->setRating($data['rating']);
        $review->setStatus($data['status']);
        $review->setFromName($data['author']);
        $review->setDate(time());
        $review->setProductId($productId);

        if(!$review->save()) {
            throw new Store_Api_Exception_Resource_SaveError();
        }

        return $review;
    }

    /**
     * Accept an integer as product id for review and returns a Store_Review object having same product id.
     *
     * @param int $productId
     * @return Store_Review
     */
    protected function createReviewForPut($productId)
    {
        $review = new Store_Review();

        $review->setTitle('Review Title');
        $review->setText('Review for product');
        $review->setRating(5);
        $review->setStatus(1);
        $review->setFromName('author');
        $review->setDate(time());
        $review->setProductId($productId);

        if(!$review->save()) {
            throw new Store_Api_Exception_Resource_SaveError();
        }

        return $review;
    }

    /**
     * Accept an integer values as identity and return an object of \Store_Review class with matching identity..
     *
     * @param int $id
     * @return Store_Review
     */
    protected function findReview($id)
    {
        $reviews = new Repository\Reviews();
        $result = $reviews->findById($id);

        return $result;
    }

    /**
     * Send a GET request to the Products_Reviews resource and test for successful GET request for one particular review associated with one product.
     *
     * @param int $productId
     * @param Array $data

     * @dataProvider getReviewDataSet
     */
    public function testGetActionSuccessForOneReview($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();
        $request->setUserParam('products', $productId);
        $request->setUserParam('reviews', $reviewId);

        $data = $this->resource->getAction($request)->getData();
        $this->assertArrayIsNotEmpty($data);
    }

    /**
     * Send a GET request to the Products_Reviews resource and test for successful GET request all reviews associated with one product.
     *
     * @param int $productId
     * @param Array $data
     * @dataProvider getReviewDataSet
     */
    public function testGetActionSuccessForAllReviewsOfOneProduct($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();
        $request->setUserParam('products', $productId);

        $data = $this->resource->getAction($request)->getData();
        $this->assertArrayIsNotEmpty($data);
    }

    /**
     * Send a GET request to the Products_Reviews resource and test for successful GET request all reviews.
     *
     * @param int $productId
     * @param Array $data
     * @dataProvider getReviewDataSet
     */
    public function testGetActionSuccessForAllReviews($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();

        $data = $this->resource->getAction($request)->getData();
        $this->assertArrayIsNotEmpty($data);
    }

    /**
     * Send a GET request to the Products_Reviews resource and test for failed GET request.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     * @expectedException Store_Api_Exception_Resource_ResourceNotFound
     * @expectedExceptionCode 404
     */
    public function testGetActionFail($productId, $data)
    {
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get', 'CONTENT_TYPE' => 'application/json'));
        $request->setUserParam('products',$productId);
        $request->setUserParam('reviews',99999);
        $this->resource->getAction($request);
    }

    /**
     * Send a DELETE request to the Products_Reviews resource and test for successful one review deletion associated with one product.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     */
    public function testDeleteActionSuccessForOneReview($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();
        $request->setUserParam('products', $productId);
        $request->setUserParam('reviews', $reviewId);

        $this->resource->deleteAction($request);
        $this->assertEquals(false,$this->findReview($reviewId));
    }

    /**
     * Send a DELETE request to the Products_Reviews resource and test for successful all reviews deletion associated with one product.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     */
    public function testDeleteActionSuccessForAllReviewsOfOneProduct($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();
        $request->setUserParam('products', $productId);

        $this->resource->deleteAction($request);
        $this->assertEquals(false, $this->findReview($reviewId));
    }

    /**
     * Send a DELETE request to the Products_Reviews resource and test for successful all reviews deletion associated with any product.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     */
    public function testDeleteActionSuccessForAllReviews($productId, $data)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $request = new Interspire_Request();

        $this->resource->deleteAction($request);
        $this->assertEquals(false, $this->findReview($reviewId));
    }

    /**
     * Send a DELETE request to the Products_Reviews resource and test for failed review deletion.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     * @expectedException Store_Api_Exception_Resource_ResourceNotFound
     * @expectedExceptionCode 404
     */
    public function testDeleteActionFail($productId, $data)
    {
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'delete'));
        $request->setUserParam('products',$productId);
        $request->setUserParam('reviews',99999);
        $this->resource->deleteAction($request);
    }

    /**
     * Send a POST request to the Products_Reviews resource and test for successful review posting.
     *
     * @param int $productId
     * @param Array $data
     *
     * @dataProvider getReviewDataSet
     */
    public function testPostActionSuccess($productId, $data)
    {
        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);

        $outputDataWrapper = $this->resource->postAction($request);
        $data = $outputDataWrapper->getData();
        $reviewId = $data['id'];
        $review = $this->findReview($reviewId);

        $this->assertNotEmpty($review->getId());
    }

    /**
     *  @dataProvider dataSetForRequiredField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
     * @expectedExceptionCode 400
     */
    public function testPostActionRequiredFieldNotSuppliedFail($productId, $data)
    {
        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);

        $this->resource->postAction($request);
    }

    /**
     *  @dataProvider dataSetForRestrictedField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_FieldNotWritable
     * @expectedExceptionCode 400
     */
    public function testPostActionRestrictedFieldSuppliedFail($productId, $data)
    {
        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);

        $this->resource->postAction($request);
    }

    /**
     *  @dataProvider dataSetForUnsupportedField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_UnsupportedField
     * @expectedExceptionCode 400
     */
    public function testPostActionUnsupportedFieldSuppliedFail($productId, $data)
    {
        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);

        $this->resource->postAction($request);
    }

    /**
     * Send a PUT request to the Products_Reviews resource and test for successful review update.
     *
     * @param int $productId
     * @param Array $data
     * @param Array $updatedData
     *
     * @dataProvider getReviewDataSetForPut
     */
    public function testPutActionSuccess($productId, $data, $updatedData)
    {
        $review = $this->createReview($productId, $data);
        $reviewId = $review->getId();

        $json = json_encode($updatedData);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('reviews', $reviewId);
        $request->setUserParam('products', $productId);

        $this->resource->putAction($request);
        $review = $this->findReview($reviewId);
        $dbData = $review->getData();
        $dbData = $dbData['reviews'];

        unset($dbData['reviewid']);
        unset($dbData['revproductid']);
        unset($dbData['revdate']);

        $iterator = new MultipleIterator;
        $iterator->attachIterator(new ArrayIterator($dbData));
        $iterator->attachIterator(new ArrayIterator($updatedData));

        foreach ($iterator as $values) {
             $this->assertEquals($values[0],$values[1]);
        }
    }

    /**
     * @dataProvider getReviewDataSet
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Resource_MethodNotFound
     * @expectedExceptionCode 405
     */
    public function testPutActionReviewIdNotPassed($productId, $data)
    {
        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);

        $this->resource->putAction($request);
    }

    /**
     *  @dataProvider dataSetForRequiredField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_RequiredFieldNotSupplied
     * @expectedExceptionCode 400
     */
    public function testPutActionRequiredFieldNotSuppliedFail($productId, $data)
    {
        $review = $this->createReviewForPut($productId);
        $reviewId = $review->getId();

        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);
        $request->setUserParam('reviews', $reviewId);

        $this->resource->putAction($request);
    }

    /**
     *  @dataProvider dataSetForRestrictedField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_FieldNotWritable
     * @expectedExceptionCode 400
     */
    public function testPutActionRestrictedFieldSuppliedFail($productId, $data)
    {
        $review = $this->createReviewForPut($productId);
        $reviewId = $review->getId();

        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);
        $request->setUserParam('reviews', $reviewId);

        $this->resource->putAction($request);
    }

    /**
     *  @dataProvider dataSetForUnsupportedField
     *
     * @param int $productId
     * @param Array $data
     *
     * @expectedException Store_Api_Exception_Request_UnsupportedField
     * @expectedExceptionCode 400
     */
    public function testPutActionUnsupportedFieldSuppliedFail($productId, $data)
    {
        $review = $this->createReviewForPut($productId);
        $reviewId = $review->getId();

        $json = json_encode($data);
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'), $json);
        $request->setUserParam('products', $productId);
        $request->setUserParam('reviews', $reviewId);

        $this->resource->putAction($request);
    }
}
