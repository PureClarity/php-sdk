<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Resource;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Resource\Regions;

/**
 * Class RegionsTest
 *
 * Unit Test for \PureClarity\Api\Resource\Regions
 *
 * @see \PureClarity\Api\Resource\Regions
 */
class RegionsTest extends MockeryTestCase
{
    /** @var Regions $subject */
    private $subject;

    /**
     * Sets up the test subject - \PureClarity\Api\Resource\Regions class
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new Regions();
    }

    /**
     * Tests that the class instantiates correctly
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Regions::class, $this->subject);
    }

    /**
     * Tests that getRegionLabels returns expected information
     */
    public function testGetRegionLabels()
    {
        $labels = $this->subject->getRegionLabels();
        $this->assertEquals(3, count($labels));
        $this->assertEquals(
            [
                1 => [
                    'value' => 1,
                    'label' => 'Europe'
                ],
                4 => [
                    'value' => 4,
                    'label' => 'USA'
                ],
                99 => [
                    'value' => 99,
                    'label' => 'UK'
                ]
            ],
            $labels
        );
    }

    /**
     * Tests that getRegionName returns expected information when env variable is set
     */
    public function testGetRegionNameLocal()
    {
        putenv('PURECLARITY_REGION=localhost');
        $this->assertEquals(
            getenv('PURECLARITY_REGION'),
            $this->subject->getRegionName(1)
        );
    }

    /**
     * Tests that getRegionName returns expected information when env variable is empty and US id is passed
     */
    public function testGetRegionNameUS()
    {
        putenv('PURECLARITY_REGION=');
        $this->assertEquals(
            'us-east-1',
            $this->subject->getRegionName(4)
        );
    }

    /**
     * Tests that getRegionName returns expected information when env variable is empty and UK id is passed
     */
    public function testGetRegionNameUK()
    {
        putenv('PURECLARITY_REGION=');
        $this->assertEquals(
            'uk',
            $this->subject->getRegionName(99)
        );
    }

    /**
     * Tests that getRegionName returns expected information when env variable is empty and EU id is passed
     */
    public function testGetRegionNameEU()
    {
        putenv('PURECLARITY_REGION=');
        $this->assertEquals(
            'eu-west-1',
            $this->subject->getRegionName(1)
        );
    }

    /**
     * Tests that getRegionName returns null when env variable is empty and invalid id is passed
     */
    public function testGetRegionNameInvalid()
    {
        putenv('PURECLARITY_REGION=');
        $this->assertEquals(
            null,
            $this->subject->getRegionName(9999)
        );
    }

    /**
     * Tests that isValidRegion returns true when valid region passed to it
     */
    public function testIsValidRegionTrue()
    {
        $this->assertEquals(
            true,
            $this->subject->isValidRegion(1)
        );
    }

    /**
     * Tests that isValidRegion returns false when invalid region passed to it
     */
    public function testIsValidRegionFalse()
    {
        $this->assertEquals(
            false,
            $this->subject->isValidRegion(123)
        );
    }

    /**
     * Tests that getRegion returns correct info when EU region passed to it
     */
    public function testGetRegion()
    {
        $this->assertEquals(
            [
                'label' => 'Europe',
                'name' => 'eu-west-1',
                'endpoints' => [
                    'api' => 'https://api-eu-w-1.pureclarity.net',
                    'sftp' => 'https://sftp-eu-w-1.pureclarity.net',
                ]
            ],
            $this->subject->getRegion(1)
        );
    }

    /**
     * Tests that getRegion returns null info when invalid region passed to it
     */
    public function testGetRegionInvalid()
    {
        $this->assertEquals(
            null,
            $this->subject->getRegion(9999)
        );
    }
}
