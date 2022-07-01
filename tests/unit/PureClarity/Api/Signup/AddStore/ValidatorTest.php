<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\AddStore;

use Mockery\MockInterface;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\AddStore;
use PureClarity\Api\Signup\AddStore\Validator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ValidatorTest
 *
 * Unit Test for PureClarity\Api\Signup\AddStore\Validator
 *
 * @see \PureClarity\Api\Signup\AddStore\Validator
 */
class ValidatorTest extends MockeryTestCase
{
    /** @var Validator $subject */
    private $subject;

    /** @var mixed[] */
    private $defaultRequest = [
        AddStore::PARAM_ACCESS_KEY => 'firstname',
        AddStore::PARAM_SECRET_KEY => 'lastname',
        AddStore::PARAM_REGION => 'region',
        AddStore::PARAM_STORE_NAME => 'store_name',
        AddStore::PARAM_URL => 'https://www.test.com/',
        AddStore::PARAM_PLATFORM => 'platform',
        AddStore::PARAM_CURRENCY => 'currency',
        AddStore::PARAM_TIMEZONE => 'timezone',
    ];

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\AddStore\Validator class
     */
    protected function setup() : void
    {
        $this->subject = new Validator();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        self::assertInstanceOf(Validator::class, $this->subject);
    }

    /**
     * Tests that a fully valid set of parameters gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testValid()
    {
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($this->defaultRequest);

        $errors = $this->subject->getErrors();

        self::assertEquals(true, $isValid);
        self::assertEquals([], $errors);
    }

    /**
     * Tests that a fully missing set of parameters gets handled correctly
     */
    public function testMissing()
    {
        $isValid = $this->subject->isValid([]);
        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(
            [
                'Missing Required Parameters: access_key,secret_key,region,store_name,url,platform,currency,timezone'
            ],
            $errors
        );
    }

    /**
     * Tests that an invalid region gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidRegion()
    {
        $this->mockRegions(false);

        $isValid = $this->subject->isValid($this->defaultRequest);

        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(['Invalid Region'], $errors);
    }

    /**
     * Tests that an invalid url gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidUrl()
    {
        $request = $this->defaultRequest;
        $request[AddStore::PARAM_URL] = 'not a url';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(['Invalid URL'], $errors);
    }

    /**
     * Tests that an invalid url with bad scheme gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidUrlBadScheme()
    {
        $request = $this->defaultRequest;
        $request[AddStore::PARAM_URL] = 'httpr://www.example.com';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(['Invalid URL'], $errors);
    }

    /**
     * Mocks the Regions class
     *
     * @see \PureClarity\Api\Resource\Regions
     * @param mixed $response - response for the mocked function
     * @return MockInterface
     */
    private function mockRegions($response)
    {
        $regions = m::mock('overload:' . Regions::class);
        $regions->shouldReceive('isValidRegion')
            ->times(1)
            ->andReturn($response);

        return $regions;
    }
}
