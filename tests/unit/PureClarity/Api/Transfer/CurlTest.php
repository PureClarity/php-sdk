<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Transfer;

use Mockery\MockInterface;
use PureClarity\Api\Transfer\Curl;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Transfer\Curl\Client;

/**
 * Class CurlTest
 *
 * Unit Test for PureClarity\Api\Transfer\Curl
 *
 * @see \PureClarity\Api\Transfer\Curl
 */
class CurlTest extends MockeryTestCase
{
    const URL = 'http://127.0.0.1/';
    const PAYLOAD = '{"field":"value"}';

    /** @var Curl $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Transfer\Curl class
     *
     * @return void
     */
    protected function setup() : void
    {
        $this->subject = new Curl();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Curl::class, $this->subject);
    }

    /**
     * Tests that setDataType works as intended
     */
    public function testSetDataType()
    {
        $this->subject->setDataType('something');
        $this->assertEquals(
            'something',
            $this->subject->getDataType()
        );
    }

    /**
     * Tests that ->post with no options gets handled correctly
     */
    public function testPostNoOptions()
    {
        $this->mockClient('something');

        $this->subject->post(
            self::URL,
            self::PAYLOAD
        );

        $this->assertEquals(200, $this->subject->getStatus());
        $this->assertEquals('something', $this->subject->getBody());
        $this->assertEquals('', $this->subject->getError());
    }

    /**
     * Tests that ->post with extra options gets handled correctly
     */
    public function testPostWithOptions()
    {
        $client = $this->mockClient('something');

        $client->shouldReceive('setopt')
            ->with(CURLOPT_BUFFERSIZE, 100)
            ->times(1);

        $this->subject->post(
            self::URL,
            self::PAYLOAD,
            [
                CURLOPT_BUFFERSIZE => 100
            ]
        );

        $this->assertEquals(200, $this->subject->getStatus());
        $this->assertEquals('something', $this->subject->getBody());
        $this->assertEquals('', $this->subject->getError());
    }

    /**
     * Tests that ->post with an error response gets handled correctly
     */
    public function testErrorResponse()
    {
        $client = $this->mockClient(false);

        $client->shouldReceive('error')
            ->times(1)
            ->andReturn('an error');

        $client->shouldReceive('errno')
            ->times(1)
            ->andReturn(7);

        $this->subject->post(
            self::URL,
            self::PAYLOAD
        );

        $this->assertEquals(200, $this->subject->getStatus());
        $this->assertEquals(false, $this->subject->getBody());
        $this->assertEquals('an error', $this->subject->getError());
    }

    /**
     * Tests that a failed certificate check is retried once with the fallback CA bundle
     */
    public function testCertificateFailureRetriesWithFallbackCaBundle()
    {
        $caInfo = [];
        $attempts = 0;
        $this->mockRetryClient(60, ['response body'], $caInfo, $attempts);

        Curl::setFallbackCaBundle(__FILE__);
        $this->subject->post(self::URL, self::PAYLOAD);

        $this->assertEquals(2, $attempts);
        $this->assertEquals([__FILE__], $caInfo);
        $this->assertEquals('response body', $this->subject->getBody());
        $this->assertEquals('', $this->subject->getError());
    }

    /**
     * Tests that other errors, such as a timeout, are not retried, as the request may have been received
     */
    public function testOtherErrorIsNotRetried()
    {
        $caInfo = [];
        $attempts = 0;
        $this->mockRetryClient(28, [], $caInfo, $attempts);

        Curl::setFallbackCaBundle(__FILE__);
        $this->subject->post(self::URL, self::PAYLOAD);

        $this->assertEquals(1, $attempts);
        $this->assertEquals([], $caInfo);
        $this->assertEquals('first error', $this->subject->getError());
    }

    /**
     * Mocks the Client class for a request whose first attempt fails
     *
     * @param int $errno - curl error number of the first attempt
     * @param string[] $retryResponses - responses of later attempts
     * @param string[] $caInfo - collects the CURLOPT_CAINFO values set
     * @param int $attempts - counts the requests made
     * @return MockInterface
     */
    private function mockRetryClient($errno, $retryResponses, &$caInfo, &$attempts)
    {
        $client = m::mock('overload:' . Client::class);
        $client->shouldReceive('init', 'getinfo', 'close');
        $client->shouldReceive('setopt')->andReturnUsing(function ($key, $value) use (&$caInfo) {
            if ($key === CURLOPT_CAINFO) {
                $caInfo[] = $value;
            }
        });
        $responses = array_merge([false], $retryResponses);
        $client->shouldReceive('exec')->andReturnUsing(function () use (&$responses, &$attempts) {
            $attempts++;
            return array_shift($responses);
        });
        $client->shouldReceive('error')->andReturn('first error');
        $client->shouldReceive('errno')->andReturn($errno);

        return $client;
    }

    /**
     * Tests that ->post with a different data type gets handled correctly
     */
    public function testPostDifferentDataType()
    {
        $this->mockClient('something', 'text/html');

        $this->subject->setDataType('text/html');

        $this->subject->post(
            self::URL,
            self::PAYLOAD
        );

        $this->assertEquals(200, $this->subject->getStatus());
        $this->assertEquals('something', $this->subject->getBody());
        $this->assertEquals('', $this->subject->getError());
    }

    /**
     * Mocks the Client class
     *
     * @see \PureClarity\Api\Transfer\Curl\Client
     * @param string $response - response body to return
     * @param string $type - expected data type
     * @return MockInterface
     */
    private function mockClient($response, $type = 'application/json')
    {
        $client = m::mock('overload:' . Client::class);
        $client->shouldReceive('init')->times(1);

        $client->shouldReceive('setopt')
            ->with(CURLOPT_URL, self::URL)
            ->times(1);

        $client->shouldReceive('setopt')
            ->with(CURLOPT_CONNECTTIMEOUT_MS, 5000)
            ->times(1);

        $client->shouldReceive('setopt')
            ->with(CURLOPT_TIMEOUT_MS, 5000)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_RETURNTRANSFER, true)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_FOLLOWLOCATION, true)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_SSL_VERIFYPEER, true)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_SSL_VERIFYHOST, 2)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_FAILONERROR, false)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_POST, true)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_POSTFIELDS, self::PAYLOAD)
            ->times(1);

        $client->shouldReceive('setopt')
            ->with(
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: ' . $type,
                    'Content-Length: ' . strlen(self::PAYLOAD)
                ]
            )
            ->times(1);

        $client->shouldReceive('exec')
            ->times(1)
            ->andReturn($response);

        $client->shouldReceive('getinfo')
            ->times(1)
            ->andReturn([
                'http_code' => 200
            ]);

        $client->shouldReceive('close')
            ->times(1);

        return $client;
    }
}
