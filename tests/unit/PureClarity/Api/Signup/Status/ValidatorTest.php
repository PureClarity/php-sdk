<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Signup\Status;

use Mockery\MockInterface;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\Status\Validator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Signup\Status;

/**
 * Class ValidatorTest
 *
 * Unit Test for \PureClarity\Api\Signup\Status\Validator
 *
 * @see \PureClarity\Api\Signup\Status\Validator
 */
class ValidatorTest extends MockeryTestCase
{
    /** @var string - default request id for mocking */
    const REQUEST_ID = 'ABCDEFGHI';

    /** @var Validator $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Signup\Status\Validator class
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

        $isValid = $this->subject->isValid([
            Status::PARAM_ID => self::REQUEST_ID,
            Status::PARAM_REGION => '1'
        ]);

        $errors = $this->subject->getErrors();

        $this->assertEquals(true, $isValid);
        $this->assertEquals([], $errors);
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

        $this->assertEquals(false, $isValid);
        $this->assertEquals(
            ['Missing Required Parameters: id,region'],
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
            Status::PARAM_ID => self::REQUEST_ID,
            Status::PARAM_REGION => 'error'
        ]);

        $errors = $this->subject->getErrors();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(['Invalid Region'], $errors);
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
