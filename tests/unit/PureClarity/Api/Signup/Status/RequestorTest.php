<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Status;

use Exception;
use PureClarity\Api\Signup\Status\Requestor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Transfer\Curl;
use Mockery\MockInterface;
use PureClarity\Api\Resource\Endpoints;

/**
 * Class RequestorTest
 *
 * Unit Test for \PureClarity\Api\Signup\Status\Requestor
 *
 * @see \PureClarity\Api\Signup\Status\Requestor
 */
class RequestorTest extends MockeryTestCase
{
    /** @var string - default endpoint for mocking */
    const ENDPOINT = 'http://127.0.0.1/';

    /** @var string - default request id for mocking */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var Requestor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Status\Requestor class
     */
    protected function setUp()
    {
        $this->subject = new Requestor();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Requestor::class, $this->subject);
    }

    /**
     * Tests a successful send gets handled correctly
     * @throws Exception
     */
    public function testSuccessfulSend()
    {
        $body = json_encode(['Complete' => false]);

        $this->mockEndpoint();
        $this->mockCurl($body);

        $result = $this->subject->send(self::REQUEST_ID, 1);

        $this->assertEquals(
            [
                'status' => 200,
                'body' => $body,
                'error' => ''
            ],
            $result
        );
    }

    /**
     * Tests an error response gets handled correctly
     * @throws Exception
     */
    public function testErrorSend()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error');

        $result = $this->subject->send(self::REQUEST_ID, 1);

        $this->assertEquals(
            [
                'status' => 400,
                'body' => $body,
                'error' => 'An error'
            ],
            $result
        );
    }

    /**
     * Tests an exception during sending gets handled correctly
     * @throws Exception
     */
    public function testException()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error', 'An Exception');

        $error = '';
        try {
            $this->subject->send(self::REQUEST_ID, 1);
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
        $client = m::mock('overload:' . Curl::class);

        if (empty($exception)) {
            $client->shouldReceive('post')
                ->with(self::ENDPOINT, json_encode(['Id' => self::REQUEST_ID]))
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
                ->with(self::ENDPOINT, json_encode(['Id' => self::REQUEST_ID]))
                ->times(1)
                ->andThrow(new Exception($exception));
        }

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
        $endpoints = m::mock('overload:' . Endpoints::class);
        $endpoints->shouldReceive('getSignupStatusEndpoint')
            ->times(1)
            ->with('1')
            ->andReturn(self::ENDPOINT);

        return $endpoints;
    }
}
