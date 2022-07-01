<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\AddStore;

use PureClarity\Api\Signup\AddStore\Processor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProcessorTest
 *
 * Unit Test for PureClarity\Api\Signup\AddStore\Processor
 *
 * @see \PureClarity\Api\Signup\AddStore\Processor
 */
class ProcessorTest extends MockeryTestCase
{
    /** @var Processor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\AddStore\Processor class
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
                'response' => [],
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an valid add store request response is handled correctly
     */
    public function testValid()
    {
        $result = $this->subject->process([
            'status' => 200,
            'body' => '',
            'error' => ''
        ]);

        self::assertEquals(
            [
                'status' => 200,
                'errors' => [],
                'response' => [],
                'success' => true,
            ],
            $result
        );
    }

    /**
     * Tests that an add store request response with errors in the body is handled correctly
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
                'response' => $response,
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an add store request with no response in the body is handled correctly
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
                'response' => $response,
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an add store request with a curl error is handled correctly
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
                'response' => $response,
                'success' => false,
            ],
            $result
        );
    }
}
