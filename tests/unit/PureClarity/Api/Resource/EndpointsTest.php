<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Resource;

use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PureClarity\Api\Resource\Endpoints;
use Mockery as m;
use Mockery\MockInterface;
use PureClarity\Api\Resource\Regions;

/**
 * Class EndpointsTest
 *
 * Unit Test for \PureClarity\Api\Resource\Endpoints
 *
 * @see \PureClarity\Api\Resource\Endpoints
 */
class EndpointsTest extends MockeryTestCase
{
    /** @var string */
    const EU_ENDPOINT_URL = 'https://api-eu-w-1.pureclarity.net';

    /** @var Endpoints $subject */
    private $subject;

    /**
     * @var mixed[]
     */
    private $regionDummy = [
        'label' => 'Europe',
        'name' => 'localhost',
        'endpoints' => [
            'api' => 'https://api-eu-w-1.pureclarity.net',
            'sftp' => 'https://sftp-eu-w-1.pureclarity.net',
        ]
    ];

    /**
     * Sets up the test subject - \PureClarity\Api\Resource\Endpoints class
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subject = new Endpoints();
    }

    /**
     * Tests that the class instantiates correctly
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Endpoints::class, $this->subject);
    }

    /**
     * Tests that invalid region is handled correctly
     */
    public function testInvalidRegion()
    {
        try {
            $regions = m::mock('overload:PureClarity\Api\Resource\Regions');

            $regions->shouldReceive('getRegion')
                ->with('error')
                ->andReturn(false)
                ->once();
            
            $this->subject->getDeltaEndpoint('error');
        } catch (Exception $e) {
            $this->assertEquals(
                'Invalid Region supplied',
                $e->getMessage()
            );
        }
    }

    /**
     * Tests that the Delta endpoint is returned correctly - with env variable set to override the real value
     * @throws Exception
     */
    public function testGetDeltaEndpoint()
    {
        putenv('PURECLARITY_HOST=http://127.0.0.1');
        $this->mockRegions();
        $endpoint = $this->subject->getDeltaEndpoint(1);

        $this->assertEquals(
            getenv('PURECLARITY_HOST') . '/api/productdelta',
            $endpoint
        );
    }

    /**
     * Tests that the Delta endpoint is returned correctly - with env set to empty so it returns a real value
     * @throws Exception
     */
    public function testGetDeltaEndpointReal()
    {
        putenv('PURECLARITY_HOST=');
        $this->mockRegions();
        $endpoint = $this->subject->getDeltaEndpoint(1);

        $this->assertEquals(
            self::EU_ENDPOINT_URL . '/api/productdelta',
            $endpoint
        );
    }

    /**
     * Tests that the Signup Request endpoint is returned correctly - with env set to override the real value
     * @throws Exception
     */
    public function testGetSignupRequestEndpoint()
    {
        putenv('PURECLARITY_HOST=http://127.0.0.1');
        $this->mockRegions();
        $endpoint = $this->subject->getSignupRequestEndpoint(1);

        $this->assertEquals(
            getenv('PURECLARITY_HOST') . '/api/plugin/signuprequest',
            $endpoint
        );
    }

    /**
     * Tests that the Signup Request endpoint is returned correctly - with env set to empty so it returns a real value
     * @throws Exception
     */
    public function testGetSignupRequestEndpointReal()
    {
        putenv('PURECLARITY_HOST=');
        $this->mockRegions();
        $endpoint = $this->subject->getSignupRequestEndpoint(1);

        $this->assertEquals(
            self::EU_ENDPOINT_URL . '/api/plugin/signuprequest',
            $endpoint
        );
    }

    /**
     * Tests that the Signup Status endpoint is returned correctly - with env set to override the real value
     * @throws Exception
     */
    public function testGetSignupStatusEndpoint()
    {
        putenv('PURECLARITY_HOST=http://127.0.0.1');
        $this->mockRegions();
        $endpoint = $this->subject->getSignupStatusEndpoint(1);

        $this->assertEquals(
            getenv('PURECLARITY_HOST') . '/api/plugin/signupstatus',
            $endpoint
        );
    }

    /**
     * Tests that the Signup Status endpoint is returned correctly - with env set to empty so it returns a real value
     * @throws Exception
     */
    public function testGetSignupStatusEndpointReal()
    {
        putenv('PURECLARITY_HOST=');
        $this->mockRegions();
        $endpoint = $this->subject->getSignupStatusEndpoint(1);

        $this->assertEquals(
            self::EU_ENDPOINT_URL . '/api/plugin/signupstatus',
            $endpoint
        );
    }

    /**
     * Tests that the Client script url returned correctly - with env set to override the real value
     * @throws Exception
     */
    public function testGetClientScriptUrl()
    {
        putenv('PURECLARITY_SCRIPT_URL=https://127.0.0.1/');
        $this->mockRegions();
        $endpoint = $this->subject->getClientScriptUrl('ABCDEFG');

        $this->assertEquals(
            getenv('PURECLARITY_SCRIPT_URL') . 'ABCDEFG/cs.js',
            $endpoint
        );
    }

    /**
     * Tests that the Client script url returned correctly - with env set to empty so it returns a real value
     * @throws Exception
     */
    public function testGetClientScriptUrlReal()
    {
        putenv('PURECLARITY_SCRIPT_URL=');
        $this->mockRegions();
        $endpoint = $this->subject->getClientScriptUrl('ABCDEFG');

        $this->assertEquals(
            '//pcs.pureclarity.net/ABCDEFG/cs.js',
            $endpoint
        );
    }

    /**
     * Tests that the SFTP endpoint is returned correctly - with env set to override the real value
     * @throws Exception
     */
    public function testGetSftpEndpoint()
    {
        putenv('PURECLARITY_FEED_HOST=https://127.0.0.1/');
        putenv('PURECLARITY_FEED_PORT=1234');
        $this->mockRegions();
        $endpoint = $this->subject->getSftpEndpoint(1);

        $this->assertEquals(
            getenv('PURECLARITY_FEED_HOST') . ':' . getenv('PURECLARITY_FEED_PORT') . '/',
            $endpoint
        );
    }

    /**
     * Tests that the SFTP endpoint is returned correctly - with env set to empty so it returns a real value
     * @throws Exception
     */
    public function testGetSftpEndpointReal()
    {
        putenv('PURECLARITY_FEED_HOST=');
        putenv('PURECLARITY_FEED_PORT=');
        $this->mockRegions();
        $endpoint = $this->subject->getSftpEndpoint(1);

        $this->assertEquals(
            'https://sftp-eu-w-1.pureclarity.net/',
            $endpoint
        );
    }

    /**
     * Mocks the \PureClarity\Api\Resource\Regions hard dependency
     *
     * @see \PureClarity\Api\Resource\Regions
     * @return MockInterface
     */
    private function mockRegions()
    {
        $regions = m::mock('overload:' . Regions::class);

        $regions->shouldReceive('getRegion')
            ->with(1)
            ->andReturn($this->regionDummy)
            ->once();

        return $regions;
    }
}
