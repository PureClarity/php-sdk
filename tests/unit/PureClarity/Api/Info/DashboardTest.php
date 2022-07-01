<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Info;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Info\Dashboard;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class DashboardTest
 *
 * Unit Test for PureClarity\Api\Info\Dashboard
 *
 * @see \PureClarity\Api\Info\Dashboard
 */
class DashboardTest extends MockeryTestCase
{
    /** @var string */
    const ENDPOINT = 'http://127.0.0.1/';

    /** @var string */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var mixed[] */
    private $defaultBody = [
        'AccessKey' => 'accessKey',
        'SecretKey' => 'secretKey'
    ];

    /** @var Dashboard $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Info\Dashboard class
     */
    protected function setup() : void
    {
        $this->subject = new Dashboard(
            'accessKey',
            'secretKey',
            1
        );
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Dashboard::class, $this->subject);
    }

    /**
     * Test that a successful send gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testSuccessfulSend()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body);

        $result = $this->subject->request();

        $this->assertEquals(
            [
                'status' => 200,
                'body' => $body
            ],
            $result
        );
    }

    /**
     * Test that a send that gets an error response gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testErrorSend()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error');

        try {
            $this->subject->request();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Error: HTTP 400 Response | Error Message: An error | Body: []',
            $error
        );
    }

    /**
     * Test that a send that gets an error response gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testErrorResponse()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body, 200, 'An error');

        try {
            $this->subject->request();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Error: An error',
            $error
        );
    }

    /**
     * Test that a send that results in an exception gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testException()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error', 'An Exception');

        $error = '';
        try {
            $this->subject->request();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'An Exception',
            $error
        );
    }

    /**
     * Mocks the Curl class
     *
     * @see \PureClarity\Api\Transfer\Curl
     *
     * @param string $body - body to respond with
     * @param int $status - http status code to respond with
     * @param string $error - error string to return
     * @param string $exception - exception message to throw
     * @return MockInterface
     */
    private function mockCurl($body = '', $status = 200, $error = '', $exception = '')
    {
        $client = m::mock('overload:\PureClarity\Api\Transfer\Curl');

        if (empty($exception)) {
            $client->shouldReceive('post')
                ->with(self::ENDPOINT, json_encode($this->defaultBody))
                ->times(1);

            $client->shouldReceive('getStatus')
                ->times(1)
                ->andReturn($status);

            $client->shouldReceive('getBody')
                ->times(1)
                ->andReturn($body);

            $client->shouldReceive('getError')
                ->times(1)
                ->andReturn($error);
        } else {
            $client->shouldReceive('post')
                ->with(self::ENDPOINT, json_encode($this->defaultBody))
                ->times(1)
                ->andThrow(new Exception($exception));
        }

        $client->shouldReceive('setDataType')
            ->times(1);

        return $client;
    }

    /**
     * Mocks the Endpoints class
     *
     * @see \PureClarity\Api\Resource\Endpoints
     * @return MockInterface
     */
    private function mockEndpoint()
    {
        $endpoints = m::mock('overload:PureClarity\Api\Resource\Endpoints');
        $endpoints->shouldReceive('getDashboardEndpoint')
            ->times(1)
            ->with('1')
            ->andReturn(self::ENDPOINT);

        return $endpoints;
    }
}
