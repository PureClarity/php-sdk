<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Account;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Account\Validate;
use PureClarity\Api\Account\Validate\Validator;
use PureClarity\Api\Account\Validate\Requestor;
use PureClarity\Api\Account\Validate\Processor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ValidateTest
 *
 * Unit Test for PureClarity\Api\Account\Validate
 *
 * @see \PureClarity\Api\Account\Validate
 */
class ValidateTest extends MockeryTestCase
{
    /** @var string */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var Validate $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Validate class
     *
     * @return void
     */
    protected function setUp():void
    {
        $this->subject = new Validate();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        self::assertInstanceOf(Validate::class, $this->subject);
    }

    /**
     * Tests an invalid request, missing region, gets handled correctly
     */
    public function testInvalid()
    {
        $params = [
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey'
        ];

        $this->mockValidator(['Missing Region']);
        $this->mockRequestor($params);
        $this->mockProcessor();

        $result = $this->subject->request($params);

        self::assertEquals(
            [
                'status' => 0,
                'errors' => ['Missing Region'],
                'response' => []
            ],
            $result
        );
    }

    /**
     * Tests a valid request returns the correct data
     */
    public function testValid()
    {
        $params = [
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey',
            Validate::PARAM_REGION => 1
        ];
        $this->mockValidator();
        $this->mockRequestor($params);
        $this->mockProcessor();

        $result = $this->subject->request($params);

        self::assertEquals(
            [
                'status' => 200,
                'errors' => '',
                'response' => []
            ],
            $result
        );
    }

    /**
     * Tests an Exception is handled correctly
     */
    public function testException()
    {
        $params = [
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey',
            Validate::PARAM_REGION => 1
        ];
        $response = [];
        
        $this->mockValidator();
        $this->mockRequestor($params, 'An Exception');
        $this->mockProcessor();

        $result = $this->subject->request($params);

        self::assertEquals(
            [
                'status' => 0,
                'errors' => ['Exception: An Exception'],
                'response' => $response
            ],
            $result
        );
    }

    /**
     * Mocks the Validator class
     *
     * @see \PureClarity\Api\Account\Validate\Validator
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
     * @see \PureClarity\Api\Account\Validate\Requestor
     * @param array $params - params for the call
     * @param string $exception - any exception message to throw
     * @return MockInterface
     */
    private function mockRequestor($params, $exception = '')
    {
        $mock = m::mock('overload:' . Requestor::class);
        if ($exception) {
            $mock->shouldReceive('send')
                ->times(1)
                ->with($params)
                ->andThrow(new Exception($exception));
        } else {
            $mock->shouldReceive('send')
                ->times(1)
                ->with($params)
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
     * @see \PureClarity\Api\Account\Validate\Processor
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
                'response' => []
            ]);

        return $mock;
    }
}
