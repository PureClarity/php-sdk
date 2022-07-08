<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Signup\AddStore;
use PureClarity\Api\Signup\AddStore\Validator;
use PureClarity\Api\Signup\AddStore\Requestor;
use PureClarity\Api\Signup\AddStore\Processor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AddStoreTest
 *
 * Unit Test for PureClarity\Api\Signup\AddStore
 *
 * @see \PureClarity\Api\Signup\AddStore
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AddStoreTest extends MockeryTestCase
{
    /** @var string */
    const REQUEST_ID = '1234567890';

    /** @var mixed[] */
    private $defaultRequest = [
        AddStore::PARAM_ACCESS_KEY => 'firstname',
        AddStore::PARAM_SECRET_KEY => 'lastname',
        AddStore::PARAM_REGION => 'region',
        AddStore::PARAM_STORE_NAME => 'store_name',
        AddStore::PARAM_URL => 'url',
        AddStore::PARAM_PLATFORM => 'platform',
        AddStore::PARAM_CURRENCY => 'currency',
        AddStore::PARAM_TIMEZONE => 'timezone',
    ];


    /** @var AddStore $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\AddStore class
     *
     * @return void
     */
    protected function setup() : void
    {
        $this->subject = new AddStore();

        $this->subject->setRequestId(self::REQUEST_ID);
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        self::assertInstanceOf(AddStore::class, $this->subject);
    }

    /**
     * Tests that generating a unique ID works correctly
     */
    public function testUniqueId()
    {
        $this->subject->setRequestId(null);
        $id = $this->subject->getRequestId();

        self::assertNotEquals(self::REQUEST_ID, $id);
        self::assertNotEquals(null, $id);
    }

    /**
     * Tests that invalid parameters are handled correctly
     * @preserveGlobalState disabled
     */
    public function testInvalid()
    {
        $this->mockValidator(['Missing Region']);
        $this->mockRequestor($this->defaultRequest);
        $this->mockProcessor();

        $result = $this->subject->request($this->defaultRequest);

        self::assertEquals(
            [
                'status' => 0,
                'errors' => ['Missing Region'],
                'response' => [],
                'success' => false,
                'request_id' => '',
            ],
            $result
        );
    }

    /**
     * Tests that a valid request returns the correct data
     */
    public function testValid()
    {
        $this->mockValidator();
        $this->mockRequestor($this->defaultRequest);
        $this->mockProcessor();

        $result = $this->subject->request($this->defaultRequest);

        self::assertEquals(
            [
                'status' => 200,
                'errors' => [],
                'response' => [],
                'success' => true,
                'request_id' => self::REQUEST_ID,
            ],
            $result
        );
    }

    /**
     * Tests that if an exception happens, request returns the correct data
     */
    public function testException()
    {
        $response = [];
        
        $this->mockValidator();
        $this->mockRequestor($this->defaultRequest, 'An Exception');
        $this->mockProcessor();

        $result = $this->subject->request($this->defaultRequest);

        self::assertEquals(
            [
                'status' => 0,
                'errors' => ['Exception: An Exception'],
                'response' => $response,
                'success' => false,
                'request_id' => '',
            ],
            $result
        );
    }

    /**
     * Mocks the Validator class
     *
     * @see \PureClarity\Api\Signup\AddStore\Validator
     * @param array $errors - any errors to be returned by getErrors
     * @return MockInterface
     */
    private function mockValidator($errors = [])
    {
        $mock = m::mock('overload:'  . Validator::class);

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
     * @see \PureClarity\Api\Signup\AddStore\Requestor
     * @param string $request - expected request ID
     * @param string $exception - any exception message to throw
     * @return MockInterface
     */
    private function mockRequestor($request, $exception = '')
    {
        $mock = m::mock('overload:' . Requestor::class);
        if ($exception) {
            $mock->shouldReceive('send')
                ->times(1)
                ->with(self::REQUEST_ID, $request)
                ->andThrow(new Exception($exception));
        } else {
            $mock->shouldReceive('send')
                ->times(1)
                ->with(self::REQUEST_ID, $request)
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
     * @see \PureClarity\Api\Signup\AddStore\Processor
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
                'errors' => [],
                'response' => [],
                'success' => true,
            ]);

        return $mock;
    }
}
