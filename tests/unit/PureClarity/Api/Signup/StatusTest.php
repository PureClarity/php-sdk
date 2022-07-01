<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Signup\Status;
use PureClarity\Api\Signup\Status\Validator;
use PureClarity\Api\Signup\Status\Requestor;
use PureClarity\Api\Signup\Status\Processor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class StatusTest
 *
 * Unit Test for PureClarity\Api\Signup\Status
 *
 * @see \PureClarity\Api\Signup\Status
 */
class StatusTest extends MockeryTestCase
{
    /** @var string */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var Status $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Status class
     *
     * @return void
     */
    protected function setup() : void
    {
        $this->subject = new Status();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Status::class, $this->subject);
    }

    /**
     * Tests an invalid request, missing region, gets handled correctly
     */
    public function testInvalid()
    {
        $this->mockValidator(['Missing Region']);
        $this->mockRequestor();
        $this->mockProcessor();

        $result = $this->subject->request([
            'id' => self::REQUEST_ID
        ]);

        $this->assertEquals(
            [
                'status' => 0,
                'errors' => ['Missing Region'],
                'response' => [],
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests a valid request returns the correct data
     */
    public function testValid()
    {
        $this->mockValidator();
        $this->mockRequestor();
        $this->mockProcessor();

        $result = $this->subject->request([
            'id' => self::REQUEST_ID,
            'region' => 1
        ]);

        $this->assertEquals(
            [
                'status' => 200,
                'errors' => '',
                'response' => [],
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Tests an Exception is handled correctly
     */
    public function testException()
    {
        $response = [];
        
        $this->mockValidator();
        $this->mockRequestor('An Exception');
        $this->mockProcessor();

        $result = $this->subject->request([
            'id' => self::REQUEST_ID,
            'region' => '1'
        ]);

        $this->assertEquals(
            [
                'status' => 0,
                'errors' => ['Exception: An Exception'],
                'response' => $response,
                'complete' => false,
            ],
            $result
        );
    }

    /**
     * Mocks the Validator class
     *
     * @see \PureClarity\Api\Signup\Status\Validator
     * @param array $errors - any errors to be returned by getErrors
     * @return MockInterface
     */
    private function mockValidator($errors = [])
    {
        $mock = m::mock('overload:' . Validator::class);

        $mock->shouldReceive('isValid')
            ->times(1)
            ->andReturn(empty($errors));

        if ($errors) {
            $mock->shouldReceive('getErrors')
                ->times(1)
                ->andReturn($errors);
        }

        return $mock;
    }

    /**
     * Mocks the Requestor class
     *
     * @see \PureClarity\Api\Signup\Status\Requestor
     * @param string $exception - any exception message to throw
     * @return MockInterface
     */
    private function mockRequestor($exception = '')
    {
        $mock = m::mock('overload:' . Requestor::class);
        if ($exception) {
            $mock->shouldReceive('send')
                ->times(1)
                ->with(self::REQUEST_ID, 1)
                ->andThrow(new Exception($exception));
        } else {
            $mock->shouldReceive('send')
                ->times(1)
                ->with(self::REQUEST_ID, 1)
                ->andReturn([
                    'status' => 200,
                    'body' => '',
                    'error' => ''
                ]);
        }

        return $mock;
    }

    /**
     * Mocks the Processor class
     *
     * @see \PureClarity\Api\Signup\Status\Processor
     * @return MockInterface
     */
    private function mockProcessor()
    {
        $mock = m::mock('overload:' . Processor::class);
        $mock->shouldReceive('process')
            ->times(1)
            ->with([
                'status' => 200,
                'body' => '',
                'error' => ''
            ])
            ->andReturn([
                'status' => 200,
                'errors' => '',
                'response' => [],
                'complete' => false,
            ]);

        return $mock;
    }
}
