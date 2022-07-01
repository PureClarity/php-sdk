<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Account\Validate;

use Mockery\MockInterface;
use PureClarity\Api\Account\Validate;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Account\Validate\Validator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ValidatorTest
 *
 * Unit Test for \PureClarity\Api\Account\Validate\Validator
 *
 * @see \PureClarity\Api\Account\Validate\Validator
 */
class ValidatorTest extends MockeryTestCase
{
    /** @var string - default request id for mocking */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var Validator $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Account\Validate\Validator class
     */
    protected function setUp():void
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

        $isValid = $this->subject->isValid([
            Validate::PARAM_REGION => 1,
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey'
        ]);

        $errors = $this->subject->getErrors();

        self::assertEquals(true, $isValid);
        self::assertEquals([], $errors);
    }

    /**
     * Tests that a fully missing set of parameters gets handled correctly
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMissing()
    {
        $isValid = $this->subject->isValid([]);
        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(
            ['Missing Required Parameters: access_key,secret_key,region'],
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

        $isValid = $this->subject->isValid([
            Validate::PARAM_REGION => 1,
            Validate::PARAM_ACCESS_KEY => 'AccessKey',
            Validate::PARAM_SECRET_KEY => 'SecretKey'
        ]);

        $errors = $this->subject->getErrors();

        self::assertEquals(false, $isValid);
        self::assertEquals(['Invalid Region'], $errors);
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
