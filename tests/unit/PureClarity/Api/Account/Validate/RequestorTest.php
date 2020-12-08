<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Account\Validate;

use Exception;
use PureClarity\Api\Account\Validate;
use PureClarity\Api\Account\Validate\Requestor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Transfer\Curl;
use Mockery\MockInterface;
use PureClarity\Api\Resource\Endpoints;

/**
 * Class RequestorTest
 *
 * Unit Test for \PureClarity\Api\Account\Validate\Requestor
 *
 * @see \PureClarity\Api\Account\Validate\Requestor
 */
class RequestorTest extends MockeryTestCase
{
    /** @var string - default endpoint for mocking */
    const ENDPOINT = 'http://127.0.0.1/';

    /** @var string - default region id for mocking */
    const REGION_ID = 1;

    /** @var Requestor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Account\Validate\Requestor class
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
        self::assertInstanceOf(Requestor::class, $this->subject);
    }

    /**
     * Tests a successful send gets handled correctly
     * @throws Exception
     */
    public function testSuccessfulSend()
    {
        $body = json_encode(['IsValid' => false]);

        $this->mockEndpoint();
        $this->mockCurl($body);

        $result = $this->subject->send([
            Validate::PARAM_REGION => self::REGION_ID,
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey'
        ]);

        self::assertEquals(
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

        $result = $this->subject->send([
            Validate::PARAM_REGION => self::REGION_ID,
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey'
        ]);

        self::assertEquals(
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
            $this->subject->send([
                Validate::PARAM_REGION => self::REGION_ID,
                Validate::PARAM_ACCESS_KEY => 'AccessKey',
                Validate::PARAM_SECRET_KEY => 'SecretKey'
            ]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        self::assertEquals(
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
                ->with(self::ENDPOINT, json_encode([
                    Requestor::PARAM_ACCESS_KEY => 'AccessKey',
                    Requestor::PARAM_SECRET_KEY => 'SecretKey'
                ]))
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
                ->with(self::ENDPOINT, json_encode([
                    Requestor::PARAM_ACCESS_KEY => 'AccessKey',
                    Requestor::PARAM_SECRET_KEY => 'SecretKey'
                ]))
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
        $endpoints->shouldReceive('getValidateAccountEndpoint')
            ->times(1)
            ->with('1')
            ->andReturn(self::ENDPOINT);

        return $endpoints;
    }
}
