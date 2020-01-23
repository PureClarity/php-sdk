<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Delta\Type;

use Exception;
use PureClarity\Api\Delta\Type\Product;
use PureClarity\Api\Delta\Base;
use PureClarity\Api\Transfer\Curl;
use PureClarity\Api\Resource\Endpoints;
use Mockery as m;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProductTest
 *
 * Unit Test for \PureClarity\Api\Delta\Type\Product
 *
 * @see \PureClarity\Api\Delta\Type\Product
 */
class ProductTest extends MockeryTestCase
{
    /** @var Product $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Delta\Type\Product class
     */
    protected function setUp()
    {
        $this->subject = new Product(
            'ACCESS_KEY',
            'SECRET_KEY',
            1
        );
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Product::class, $this->subject);
        $this->assertInstanceOf(Base::class, $this->subject);
    }

    /**
     * Tests that deletes send correctly
     *
     * @throws Exception
     */
    public function testSendDeletesOnly()
    {
        $expectedBody = $this->getExpectedBody([], ['test123']);
        $this->mockEndpoint();
        $this->mockCurl($expectedBody);

        $this->subject->addDelete('test123');
        $responses = $this->subject->send();

        $this->assertEquals(1, count($responses));

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[0]
        );
    }

    /**
     * Tests that Product data deltas send correctly
     *
     * @throws Exception
     */
    public function testSendDataOnly()
    {
        $expectedBody = $this->getExpectedBody(
            [
                ['SKU' => 'test123']
            ],
            []
        );

        $this->mockEndpoint();
        $this->mockCurl($expectedBody);

        $this->subject->addData(['SKU' => 'test123']);
        $responses = $this->subject->send();

        $this->assertEquals(1, count($responses));

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[0]
        );
    }

    /**
     * Tests that sending both data & deletes at the same time works as expected
     *
     * @throws Exception
     */
    public function testSendBoth()
    {
        $expectedBody = $this->getExpectedBody(
            [],
            ['test123']
        );

        $this->mockEndpoint();
        $curl = $this->mockCurl($expectedBody, 200, '', '', 2);

        $expectedBody2 = $this->getExpectedBody(
            [
                ['SKU' => 'test123']
            ],
            []
        );

        $curl->shouldReceive('post')
            ->with('http://127.0.0.1/delta/', $expectedBody2)
            ->times(1);

        $this->subject->addDelete('test123');
        $this->subject->addData(['SKU' => 'test123']);

        $responses = $this->subject->send();

        $this->assertEquals(2, count($responses));

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[0]
        );

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[1]
        );
    }

    /**
     * Tests that data is paged correctly
     *
     * @throws Exception
     */
    public function testSendMultiPage()
    {
        $request1 = $this->getExpectedBody(
            [
                ['SKU' => 'test1'],
                ['SKU' => 'test2'],
                ['SKU' => 'test3'],
                ['SKU' => 'test4'],
                ['SKU' => 'test5'],
                ['SKU' => 'test6'],
                ['SKU' => 'test7'],
                ['SKU' => 'test8'],
                ['SKU' => 'test9'],
                ['SKU' => 'test10']
            ],
            []
        );


        $curl = $this->mockCurl($request1, 200, '', '', 2);

        $request2 = $this->getExpectedBody(
            [
                ['SKU' => 'test11'],
                ['SKU' => 'test12']
            ],
            []
        );

        $curl->shouldReceive('post')
            ->once()
            ->with('http://127.0.0.1/delta/', $request2);

        $this->mockEndpoint();

        $this->subject->addData(['SKU' => 'test1']);
        $this->subject->addData(['SKU' => 'test2']);
        $this->subject->addData(['SKU' => 'test3']);
        $this->subject->addData(['SKU' => 'test4']);
        $this->subject->addData(['SKU' => 'test5']);
        $this->subject->addData(['SKU' => 'test6']);
        $this->subject->addData(['SKU' => 'test7']);
        $this->subject->addData(['SKU' => 'test8']);
        $this->subject->addData(['SKU' => 'test9']);
        $this->subject->addData(['SKU' => 'test10']);
        $this->subject->addData(['SKU' => 'test11']);
        $this->subject->addData(['SKU' => 'test12']);

        $responses = $this->subject->send();

        $this->assertEquals(2, count($responses));

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[0]
        );

        $this->assertEquals(
            [
                'status' => 200,
                'body' => ''
            ],
            $responses[1]
        );
    }

    /**
     * Tests that invalid region is handled correctly
     *
     * @throws Exception
     */
    public function testInvalidRegion()
    {
        try {
            $this->mockEndpoint('Invalid Region supplied');
            $test = new Product(
                'ACCESS_KEY',
                'SECRET_KEY',
                1
            );
            $test->addData(['SKU' => 'test123']);
            $test->send();
        } catch (Exception $e) {
            $this->assertEquals(
                'Invalid Region supplied',
                $e->getMessage()
            );
        }
    }

    /**
     * Tests that errors are handled correctly
     *
     * @throws Exception
     */
    public function testCurlError()
    {
        $expectedBody = $this->getExpectedBody(
            [
                ['SKU' => 'test123']
            ],
            []
        );

        $this->mockEndpoint();
        $this->mockCurl($expectedBody, 500, '', 'Some error');

        try {
            $this->subject->addData(['SKU' => 'test123']);
            $this->subject->send();
        } catch (Exception $e) {
            $this->assertEquals(
                'Error: HTTP 500 Response | Error Message: Some error | Body: ',
                $e->getMessage()
            );
        }
    }

    /**
     * Tests that error status codes are handled correctly
     *
     * @throws Exception
     */
    public function testSoftError()
    {
        $expectedBody = $this->getExpectedBody(
            [
                ['SKU' => 'test123']
            ],
            []
        );

        $this->mockEndpoint();
        $this->mockCurl($expectedBody, 200, 'An error response');

        try {
            $this->subject->addData(['SKU' => 'test123']);
            $this->subject->send();
        } catch (Exception $e) {
            $this->assertEquals(
                'Error: HTTP 500 Response | Error Message: Some error | Body: ',
                $e->getMessage()
            );
        }
    }

    /**
     * Generates an expected request body function
     *
     * @param mixed[] $data - any product data to send
     * @param mixed[] $deletes - product deletions to send
     * @return string
     */
    private function getExpectedBody($data, $deletes)
    {
        $body = [
            'AppKey'         => 'ACCESS_KEY',
            'Secret'         => 'SECRET_KEY',
            'Products'       => $data,
            'DeleteProducts' => $deletes,
            'Format'         => 'magentoplugin1.0.0'
        ];

        return json_encode($body);
    }

    /**
     * Mocks the \PureClarity\Api\Resource\Endpoints hard dependency
     *
     * @param string $exception - exception message to show
     *
     * @return MockInterface
     */
    private function mockEndpoint($exception = '')
    {
        $endpoints = m::mock('overload:' . Endpoints::class);
        if (!$exception) {
            $endpoints->shouldReceive('getDeltaEndpoint')
                ->with(1)
                ->andReturn('http://127.0.0.1/delta/')
                ->once();
        } else {
            $endpoints->shouldReceive('getDeltaEndpoint')
                ->with(1)
                ->andReturn('http://127.0.0.1/delta/')
                ->once()
                ->andThrow(new Exception($exception));
        }


        return $endpoints;
    }

    /**
     * Mocks the \PureClarity\Api\Transfer\Curl hard dependency
     *
     * @param string $requestBody - expected request body
     * @param integer $statusCode - expected status code response
     * @param string $response - expected response body
     * @param string $error - expected error response
     * @param integer $pages - expected number of pages that will be sent
     * @return MockInterface
     */
    private function mockCurl($requestBody, $statusCode = 200, $response = '', $error = '', $pages = 1)
    {
        $curl = m::mock('overload:' . Curl::class);

        $curl->shouldReceive('setDataType')
            ->once()
            ->with('application/json');

        $curl->shouldReceive('post')
            ->with('http://127.0.0.1/delta/', $requestBody)
            ->times(1);

        $curl->shouldReceive('getStatus')
            ->times($pages)
            ->andReturn($statusCode);

        $curl->shouldReceive('getError')
            ->times($pages)
            ->andReturn($error);

        $curl->shouldReceive('getBody')
            ->times($pages)
            ->andReturn($response);



        return $curl;
    }
}
