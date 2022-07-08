<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Status;

use PureClarity\Api\Signup\Status\Processor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProcessorTest
 *
 * Unit Test for \PureClarity\Api\Signup\Status\Processor
 *
 * @see \PureClarity\Api\Signup\Status\Processor
 */
class ProcessorTest extends MockeryTestCase
{
    /** @var Processor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Status\Processor class
     */
    protected function setup() : void
    {
        $this->subject = new Processor();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Processor::class, $this->subject);
    }

    /**
     * Tests that an invalid response passed to the processor is handled correctly
     */
    public function testInvalid()
    {
        $result = $this->subject->process([]);

        $this->assertEquals(
            [
                'status' => 0,
                'errors' => ['Invalid Response'],
                'response' => [],
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an incomplete signup response is handled correctly
     */
    public function testIncompleteRequest()
    {
        $result = $this->subject->process([
            'status' => 200,
            'body' => json_encode([
                'Complete' => false
            ]),
            'error' => ''
        ]);

        $this->assertEquals(
            [
                'status' => 200,
                'errors' => [],
                'response' => [
                    'Complete' => false
                ],
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests that a complete signup response is handled correctly
     */
    public function testCompleteRequest()
    {
        $response = [
            'Complete' => true,
            'AccessKey' => 'ABCDEFGHI',
            'SecretKey' => 'JKLMNOP',
        ];

        $result = $this->subject->process([
            'status' => 200,
            'body' => json_encode($response),
            'error' => ''
        ]);

        $this->assertEquals(
            [
                'status' => 200,
                'errors' => [],
                'response' => $response,
                'complete' => true,
            ],
            $result
        );
    }

    /**
     * Tests that a response with errors is handled correctly
     */
    public function testErrorResponseWithErrors()
    {
        $response = [
            'errors' => [
                'Error 1',
                'Error 2',
            ]
        ];

        $result = $this->subject->process([
            'status' => 400,
            'body' => json_encode($response),
            'error' => ''
        ]);

        $this->assertEquals(
            [
                'status' => 400,
                'errors' => [
                    'Error 1',
                    'Error 2',
                ],
                'response' => $response,
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an empty response body is handled correctly
     */
    public function testErrorResponseNoErrors()
    {
        $response = [];

        $result = $this->subject->process([
            'status' => 400,
            'body' => json_encode($response),
            'error' => ''
        ]);

        $this->assertEquals(
            [
                'status' => 400,
                'errors' => [],
                'response' => $response,
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests that a curl error response is handled correctly
     */
    public function testCurlError()
    {
        $response = [];

        $result = $this->subject->process([
            'status' => 400,
            'body' => json_encode($response),
            'error' => 'A curl Error'
        ]);

        $this->assertEquals(
            [
                'status' => 400,
                'errors' => ['A curl Error'],
                'response' => $response,
                'complete' => false,
            ],
            $result
        );
    }
}
