<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Resource;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Resource\Timezones;

/**
 * Class EndpointsTest
 *
 * Unit Test for \PureClarity\Api\Resource\Endpoints
 *
 * @see \PureClarity\Api\Resource\Endpoints
 */
class TimezonesTest extends MockeryTestCase
{
    /** @var Timezones $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Resource\Endpoints class
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new Timezones();
    }

    /**
     * Tests that the class instantiates correctly
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Timezones::class, $this->subject);
    }

    /**
     * Tests that timezones list is returned
     */
    public function testGetLabels()
    {
        $zones = $this->subject->getLabels();

        $this->assertCount(
            348,
            $zones
        );
    }
}
