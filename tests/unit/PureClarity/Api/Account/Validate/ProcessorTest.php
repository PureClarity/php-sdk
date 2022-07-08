<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Account\Validate;

use PureClarity\Api\Account\Validate\Processor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProcessorTest
 *
 * Unit Test for \PureClarity\Api\Account\Validate\Processor
 *
 * @see \PureClarity\Api\Account\Validate\Processor
 */
class ProcessorTest extends MockeryTestCase
{
    /** @var Processor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Account\Validate\Processor class
     */
    protected function setUp() :void
    {
        $this->subject = new Processor();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        self::assertInstanceOf(Processor::class, $this->subject);
    }

    /**
     * Tests that an invalid response passed to the processor is handled correctly
     */
    public function testInvalid()
    {
        $result = $this->subject->process([]);

        self::assertEquals(
            [
                'status' => 0,
                'errors' => ['Invalid Response'],
                'response' => []
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

        self::assertEquals(
            [
                'status' => 400,
                'errors' => [
                    'Error 1',
                    'Error 2',
                ],
                'response' => $response
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

        self::assertEquals(
            [
                'status' => 400,
                'errors' => [],
                'response' => $response
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

        self::assertEquals(
            [
                'status' => 400,
                'errors' => ['A curl Error'],
                'response' => $response
            ],
            $result
        );
    }
}
