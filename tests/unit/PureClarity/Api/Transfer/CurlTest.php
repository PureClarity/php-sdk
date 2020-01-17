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
    protected function setUp()
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

        $this->subject->post(
            self::URL,
            self::PAYLOAD
        );

        $this->assertEquals(200, $this->subject->getStatus());
        $this->assertEquals(false, $this->subject->getBody());
        $this->assertEquals('an error', $this->subject->getError());
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
            ->with(CURLOPT_SSL_VERIFYPEER, false)
            ->times(1);
        $client->shouldReceive('setopt')
            ->with(CURLOPT_SSL_VERIFYHOST, 0)
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
