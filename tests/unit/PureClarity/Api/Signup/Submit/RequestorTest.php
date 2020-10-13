<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Submit;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\Submit;
use PureClarity\Api\Signup\Submit\Requestor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class RequestorTest
 *
 * Unit Test for PureClarity\Api\Signup\Submit\Requestor
 *
 * @see \PureClarity\Api\Signup\Submit\Requestor
 */
class RequestorTest extends MockeryTestCase
{
    /** @var string */
    const ENDPOINT = 'http://127.0.0.1/';

    /** @var string */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var mixed[] */
    private $defaultRequest = [
        Submit::PARAM_FIRSTNAME => 'firstname',
        Submit::PARAM_LASTNAME => 'lastname',
        Submit::PARAM_EMAIL => 'email',
        Submit::PARAM_COMPANY => 'company',
        Submit::PARAM_PASSWORD => 'password',
        Submit::PARAM_STORE_NAME => 'store_name',
        Submit::PARAM_REGION => 1,
        Submit::PARAM_URL => 'url',
        Submit::PARAM_PLATFORM => 'platform',
        Submit::PARAM_CURRENCY => 'currency',
        Submit::PARAM_TIMEZONE => 'timezone',
    ];

    /** @var mixed[] */
    private $defaultProcessedRequest = [
        'Id' => self::REQUEST_ID,
        'Platform' => 'platform',
        'Email' => 'email',
        'FirstName' => 'firstname',
        'LastName' => 'lastname',
        'Company' => 'company',
        'Region' => 'region-name',
        'Currency' => 'currency',
        'TimeZone' => 'timezone',
        'Url' => 'url',
        'Password' => 'password',
        'StoreName' => 'store_name'
    ];

    /** @var Requestor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Submit\Requestor class
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
     * Test that a successful send gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testSuccessfulSend()
    {
        $body = json_encode([]);

        $this->mockEndpoint();
        $this->mockRegions();
        $this->mockCurl($body);

        $result = $this->subject->send(self::REQUEST_ID, $this->defaultRequest);

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
     * Test that a send that gets an error response gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testErrorSend()
    {
        $body = json_encode([]);

        $this->mockRegions();
        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error');

        $result = $this->subject->send(self::REQUEST_ID, $this->defaultRequest);

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
     * Test that a send that results in an exception gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testException()
    {
        $body = json_encode([]);

        $this->mockRegions();
        $this->mockEndpoint();
        $this->mockCurl($body, 400, 'An error', 'An Exception');

        $error = '';
        try {
            $this->subject->send(self::REQUEST_ID, $this->defaultRequest);
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
        $client = m::mock('overload:PureClarity\Api\Transfer\Curl');

        if (empty($exception)) {
            $client->shouldReceive('post')
                ->with(self::ENDPOINT, json_encode($this->defaultProcessedRequest))
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
                ->with(self::ENDPOINT, json_encode($this->defaultProcessedRequest))
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
        $endpoints = m::mock('overload:PureClarity\Api\Resource\Endpoints');
        $endpoints->shouldReceive('getSignupRequestEndpoint')
            ->times(1)
            ->with('1')
            ->andReturn(self::ENDPOINT);

        return $endpoints;
    }

    /**
     * Mocks the Regions class
     *
     * @see \PureClarity\Api\Resource\Regions
     * @return MockInterface
     */
    private function mockRegions()
    {
        $endpoints = m::mock('overload:' . Regions::class);
        $endpoints->shouldReceive('getRegionName')
            ->times(1)
            ->with(1)
            ->andReturn('region-name');

        return $endpoints;
    }
}
