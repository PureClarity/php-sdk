<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Feed;

use Exception;
use PureClarity\Api\Feed\Transfer;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use PureClarity\Api\Transfer\Curl;
use Mockery\MockInterface;
use PureClarity\Api\Resource\Endpoints;

/**
 * Class TransferTest
 *
 * Unit Test for \PureClarity\Api\Feed\Transfer
 *
 * @see \PureClarity\Api\Feed\Transfer
 */
class TransferTest extends MockeryTestCase
{
    /** @var string */
    const ENDPOINT = 'http://127.0.0.1/';

    /** @var string */
    const FEED_TYPE = 'product';

    /** @var string */
    const ACCESS_KEY = 'ABCDEFGHI';

    /** @var string */
    const SECRET_KEY = 'ZYXWVUTQR';

    /** @var string */
    const REGION = 1;

    /** @var string */
    const FEED_ID = 'FEED1234';

    /** @var Transfer $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Feed\Transfer class
     */
    protected function setUp()
    {
        $this->subject = new Transfer(
            self::FEED_TYPE,
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION,
            self::FEED_ID
        );
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Transfer::class, $this->subject);
    }

    /**
     * Tests the create method sends the data passed through it correctly and to correct endpoint
     */
    public function testCreate()
    {
        $this->mockEndpoint();
        $this->mockCurl('feed-create', '{"Version": 2, "Products":[');

        $error = '';
        try {
            $result = $this->subject->create('{"Version": 2, "Products":[');
            $this->assertEquals(
                [
                    'status' => 200,
                    'body' => '',
                ],
                $result
            );
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests the append method sends the data passed through it correctly and to correct endpoint
     */
    public function testAppend()
    {
        $this->mockEndpoint();
        $this->mockCurl('feed-append', '{"field": "value"}');

        $error = '';
        try {
            $result = $this->subject->append('{"field": "value"}');
            $this->assertEquals(
                [
                    'status' => 200,
                    'body' => '',
                ],
                $result
            );
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests the close method sends the data passed through it correctly and to correct endpoint
     */
    public function testClose()
    {
        $this->mockEndpoint();
        $this->mockCurl('feed-close', '}');

        $error = '';
        try {
            $result = $this->subject->close('}');
            $this->assertEquals(
                [
                    'status' => 200,
                    'body' => '',
                ],
                $result
            );
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that an exception is thrown when the Curl object throws one
     */
    public function testCurlException()
    {
        $this->mockEndpoint();
        $this->mockCurl('feed-create', '{"Version": 2, "Products":[', '', 200, '', 'An Exception');

        $error = '';
        try {
            $this->subject->create('{"Version": 2, "Products":[');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'An Exception',
            $error
        );
    }

    /**
     * Tests that an exception is thrown when the Curl returns an error
     */
    public function testCurlError()
    {
        $this->mockEndpoint();
        $this->mockCurl('feed-create', '{"Version": 2, "Products":[', '', 200, 'An Error');

        $error = '';
        try {
            $this->subject->create('{"Version": 2, "Products":[');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'An Error',
            $error
        );
    }

    /**
     * Mocks the Curl class
     *
     * @see \PureClarity\Api\Transfer\Curl
     * @param string $endPoint - endpoint for this mock
     * @param string $post - expected post body
     * @param string $body - intended response body
     * @param int $status - http status to return
     * @param string $error - curl error to return
     * @param string $exception - any exception message to throw
     * @return MockInterface
     */
    private function mockCurl($endPoint, $post = '', $body = '', $status = 200, $error = '', $exception = '')
    {
        $client = m::mock('overload:' . Curl::class);

        $url = self::ENDPOINT . $endPoint;
        $postBody = http_build_query($this->getPostBody($post));
        if (empty($exception)) {
            $client->shouldReceive('post')
                ->with($url, $postBody)
                ->times(1);

            $client->shouldReceive('getStatus')
                ->times(1)
                ->andReturn($status);

            $client->shouldReceive('getError')
                ->times(1)
                ->andReturn($error);

            if (empty($error)) {
                $client->shouldReceive('getBody')
                    ->times(1)
                    ->andReturn($body);
            }
        } else {
            $client->shouldReceive('post')
                ->with($url, $postBody)
                ->times(1)
                ->andThrow(new Exception($exception));
        }

        $client->shouldReceive('setDataType')
            ->times(1)
            ->with('application/x-www-form-urlencoded')
            ->andReturn($status);

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
        $endpoints->shouldReceive('getSftpEndpoint')
            ->times(1)
            ->with(1)
            ->andReturn(self::ENDPOINT);

        return $endpoints;
    }

    /**
     * Generates a body to use as the post for the Curl mock
     * @param string $data - payload data
     * @return mixed[]
     */
    private function getPostBody($data = '')
    {
        $parameters = array(
            'accessKey' => self::ACCESS_KEY,
            'secretKey' => self::SECRET_KEY,
            'feedName' => self::FEED_TYPE . '-' . self::FEED_ID
        );

        if (! empty($data)) {
            $parameters['payLoad'] = $data;
        }

        $parameters['php'] = phpversion();

        return $parameters;
    }
}
