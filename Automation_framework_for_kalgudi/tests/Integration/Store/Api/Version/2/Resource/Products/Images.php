<?php

/**
 * @TODO: Product images API needs much more test coverage (i.e. testing basic CRUD functionality)
 */
class Integration_Store_Api_Version_2_Resource_Products_Images extends Interspire_IntegrationTest {
    private $productId;

    /** @var Testable_Store_Api_Version_2_Resource_Products_Images */
    private $resource = null;

    public function setUp()
    {
        $this->resource = new Testable_Store_Api_Version_2_Resource_Products_Images();
        $row = $this->fixtures->db->FetchOne('SELECT productid FROM products');
        $this->productId = $row['productid'];
    }

    /**
     * Send a POST request to the images resource.
     *
     * @param $image_src
     * @return Store_Api_OutputDataWrapper
     */
    protected function createImageResource($image_src)
    {
        $json = json_encode(array('image_file' => $image_src));

        $request = new Interspire_Request(
            null,
            null,
            null,
            array('REQUEST_METHOD' => 'post', 'CONTENT_TYPE' => 'application/json'),
            $json);

        $request->setUserParam('products', $this->productId);
        return $this->resource->postAction($request);
    }

    /**
     * Send a PUT request to the images resource.
     *
     * @param $id
     * @param $description
     * @return Store_Api_OutputDataWrapper
     */
    protected function modifyImageResource($id, $description)
    {
        $json = json_encode(array('description' => $description));

        $request = new Interspire_Request(
            null,
            null,
            null,
            array('REQUEST_METHOD' => 'put', 'CONTENT_TYPE' => 'application/json'),
            $json);

        $request->setUserParam('products', $this->productId);
        $request->setUserParam('images', $id);
        return $this->resource->putAction($request);
    }

    /**
     * Send a GET request to the images resource.
     *
     * @return Store_Api_OutputDataWrapper
     */
    protected function getImageResource()
    {
        $request = new Interspire_Request(null, null, null, array('REQUEST_METHOD' => 'get'));
        $request->setUserParam('products', $this->productId);
        return $this->resource->getAction($request);
    }

    public function testSampleImageFile()
    {
        $images = $this->getImageResource();
        $images = $images->getData();
        $image  = $images[0];

        $this->assertStringStartsWith('../app/assets/img/sample_images', $image['image_file']);
    }

    /**
     * @expectedException Store_Api_Exception
     * @expectedExceptionMessage Cannot edit a sample image
     */
    public function testModifySampleImageResource()
    {
        $images = $this->getImageResource();
        $data = $images->getData();

        foreach ($data as $datum) {
            if ($datum['is_sample']) {
                $this->modifyImageResource($datum['id'], 'different');
                break;
            }
        }
    }

    public function testModifyImageResource()
    {
        $image = $this->createImageResource('http://www.google.com/intl/en_ALL/images/logo.gif');
        $data = $image->getData(true);
        $id = $data['id'];

        $image = $this->modifyImageResource($id, 'different');
        $data = $image->getData(true);

        $this->assertEquals('different', $data['description']);
    }

    public function testEnsureTmpFileRemovedForSuccessfulHttpImage()
    {
        $this->createImageResource('http://www.google.com/intl/en_ALL/images/logo.gif');

        // a successful import will delete the temp file, we only need to check that it doesn't exist.

        $tempFile = $this->resource->tempFile;
        $this->assertFileNotExists($tempFile);
    }

    public function testEnsureTmpFileRemovedForUnsuccessfulHttpImage()
    {
        // Remove any existing tmp files handled by DestructDelete
        Interspire_File_DestructDelete::flush();

        try {
            $this->createImageResource('http://abcdefg/nonexistent.gif');
            $this->fail('Expected Store_Api_Exception_Request_InvalidField exception');
        } catch (Store_Api_Exception_Request_InvalidField $e) {
            $this->assertEquals('image_file', $e->getField());
        }

        $files = Interspire_File_DestructDelete::getDeleteOnShutdownFiles();
        // we shouldn't create a temp file if we can't download the image
        $this->assertEmpty($files);
    }
}

class Testable_Store_Api_Version_2_Resource_Products_Images extends Store_Api_Version_2_Resource_Products_Images
{
    public $tempFile;

    /**
     * @param Store_Api_Input $input
     * @param $currentValues
     * @return null|string
     */
    protected function parseInputImagePath(Store_Api_Input $input, $currentValues)
    {
        $this->tempFile = parent::parseInputImagePath($input, $currentValues);
        return $this->tempFile;
    }
}
