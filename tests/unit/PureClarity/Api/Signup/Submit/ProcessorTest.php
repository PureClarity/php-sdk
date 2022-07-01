<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Submit;

use PureClarity\Api\Signup\Submit\Processor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProcessorTest
 *
 * Unit Test for PureClarity\Api\Signup\Submit\Processor
 *
 * @see \PureClarity\Api\Signup\Submit\Processor
 */
class ProcessorTest extends MockeryTestCase
{
    /** @var Processor $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Submit\Processor class
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
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an valid signup request response is handled correctly
     */
    public function testValid()
    {
        $result = $this->subject->process([
            'status' => 200,
            'body' => '',
            'error' => ''
        ]);

        $this->assertEquals(
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
     * Tests that an signup request response with errors in the body is handled correctly
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
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an signup request with no response in the body is handled correctly
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
                'success' => false,
            ],
            $result
        );
    }

    /**
     * Tests that an signup request with a curl error is handled correctly
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
                'success' => false,
            ],
            $result
        );
    }
}
