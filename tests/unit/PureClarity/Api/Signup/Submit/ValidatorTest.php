<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Submit;

use Mockery\MockInterface;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\Submit;
use PureClarity\Api\Signup\Submit\Validator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ValidatorTest
 *
 * Unit Test for PureClarity\Api\Signup\Submit\Validator
 *
 * @see \PureClarity\Api\Signup\Submit\Validator
 */
class ValidatorTest extends MockeryTestCase
{
    /** @var Validator $subject */
    private $subject;

    /** @var mixed[] */
    private $defaultRequest = [
        Submit::PARAM_FIRSTNAME => 'firstname',
        Submit::PARAM_LASTNAME => 'lastname',
        Submit::PARAM_EMAIL => 'email@example.com',
        Submit::PARAM_COMPANY => 'company',
        Submit::PARAM_PASSWORD => 'Password123!',
        Submit::PARAM_STORE_NAME => 'store_name',
        Submit::PARAM_REGION => 1,
        Submit::PARAM_URL => 'http://www.example.com/',
        Submit::PARAM_PLATFORM => 'platform',
        Submit::PARAM_CURRENCY => 'currency',
        Submit::PARAM_TIMEZONE => 'timezone',
    ];

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Submit\Validator class
     */
    protected function setUp()
    {
        $this->subject = new Validator();
    }

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Validator::class, $this->subject);
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

        $this->assertEquals(true, $isValid);
        $this->assertEquals([], $errors);
    }

    /**
     * Tests that a fully missing set of parameters gets handled correctly
     */
    public function testMissing()
    {
        $isValid = $this->subject->isValid([]);
        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(
            [
                'Missing Required Parameters: firstname,lastname,email,company,'
                . 'password,store_name,region,url,platform,currency,timezone'
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

        $this->assertEquals(false, $isValid);
        $this->assertEquals(['Invalid Region'], $errors);
    }

    /**
     * Tests that an invalid email gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidEmail()
    {
        $request = $this->defaultRequest;
        $request[Submit::PARAM_EMAIL] = 'invalid';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(['Invalid Email Address'], $errors);
    }

    /**
     * Tests that an invalid url gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidUrl()
    {
        $request = $this->defaultRequest;
        $request[Submit::PARAM_URL] = 'not a url';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(['Invalid URL'], $errors);
    }

    /**
     * Tests that an invalid url with bad scheme gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidUrlBadScheme()
    {
        $request = $this->defaultRequest;
        $request[Submit::PARAM_URL] = 'httpr://www.example.com';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(['Invalid URL'], $errors);
    }

    /**
     * Tests that a weak password gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidPassword()
    {
        $request = $this->defaultRequest;
        $request[Submit::PARAM_PASSWORD] = 'password';
        $this->mockRegions(true);

        $isValid = $this->subject->isValid($request);

        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(
            [
                'Password not strong enough, must contain 1 lowercase letter,'
                . ' 1 uppercase letter, 1 number and be 8 characters or longer'
            ],
            $errors
        );
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
