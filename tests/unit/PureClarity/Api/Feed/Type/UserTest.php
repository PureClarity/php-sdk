<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Feed\Type;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Feed\Feed;
use PureClarity\Api\Feed\Transfer;
use PureClarity\Api\Feed\Type\User;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class UserTest
 *
 * Unit Test for \PureClarity\Api\Delta\Type\User
 *
 * @see \PureClarity\Api\Delta\Type\User
 */
class UserTest extends MockeryTestCase
{
    /** @var string $accessKey */
    const ACCESS_KEY = 'ABCDEFGHI';

    /** @var string $secretKey */
    const SECRET_KEY = 'ZYXWVUTQR';

    /** @var string $region */
    const REGION = 1;

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $this->assertInstanceOf(User::class, $subject);
        $this->assertInstanceOf(Feed::class, $subject);
    }

    /**
     * Tests calling create sends the correct type-specific feed start
     */
    public function testCreate()
    {
        $this->mockTransfer('create', '{"Version": 2, "Users":[');

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->start();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests attempting to append bad data returns the correct type-specific errors
     */
    public function testAppendInvalid()
    {
        $this->mockTransfer(false);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append([]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Missing UserId',
            $error
        );
    }

    /**
     * Tests attempting to append bad data returns the correct type-specific errors
     */
    public function testAppendEmpty()
    {
        $this->mockTransfer(false);

        $data = [
            '_index' => '',
            'UserId' => ''
        ];

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append($data);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Missing data for UserId',
            $error
        );
    }

    /**
     * Tests that a single append does not send data (as default page size is 50)
     */
    public function testAppendValidNotSent()
    {
        $data = [
            '_index' => 1,
            'UserId' => 1
        ];

        $this->mockTransfer(false);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append($data);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when enough appends are done, the append method is called on the transfer object
     */
    public function testAppendValidDefaultPageSize()
    {
        $data = $this->generateData();

        $this->mockTransfer('append', $data['string']);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            foreach ($data['array'] as $row) {
                $subject->append($row);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when setting the page size to a different value, the append method is called on the transfer object
     * at the correct point
     */
    public function testAppendValidNonDefaultPageSize()
    {
        $data = $this->generateData(10);

        $this->mockTransfer('append', $data['string']);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $subject->setPageSize(10);

        $error = '';
        try {
            foreach ($data['array'] as $row) {
                $subject->append($row);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when setting the page size to a different value, the append method is called on the transfer object
     * at the correct points if more than one page is sent
     */
    public function testAppendValidNonDefaultPageSizeMultiPage()
    {
        $page1Data = $this->generateData(10);

        $transfer = $this->mockTransfer('append', $page1Data['string']);

        $page2Data = $this->generateData(10, 2);
        $transfer->shouldReceive('append')
            ->with($page2Data['string'])
            ->times(1);

        $page3Data = $this->generateData(10, 3);
        $transfer->shouldReceive('append')
            ->with($page3Data['string'])
            ->times(1);

        $page4Data = $this->generateData(10, 4);
        $transfer->shouldReceive('append')
            ->with($page4Data['string'])
            ->times(1);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $subject->setPageSize(10);

        $error = '';
        try {
            foreach ($page1Data['array'] as $row) {
                $subject->append($row);
            }
            foreach ($page2Data['array'] as $row) {
                $subject->append($row);
            }
            foreach ($page3Data['array'] as $row) {
                $subject->append($row);
            }
            foreach ($page4Data['array'] as $row) {
                $subject->append($row);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests calling end sends the correct type-specific feed end in the close function
     */
    public function testEnd()
    {
        $this->mockTransfer('close', ']}');

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->end();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests calling end with unset data sends an append and
     * then the correct type-specific feed end in the close function
     */
    public function testEndWithAppend()
    {
        $transfer = $this->mockTransfer('close', ']}');

        $pageData = $this->generateData(1);

        $transfer->shouldReceive('append')
            ->with($pageData['string'])
            ->times(1);

        $subject = new User(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            foreach ($pageData['array'] as $row) {
                $subject->append($row);
            }
            $subject->end();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Mocks the Transfer class
     *
     * @see \PureClarity\Api\Feed\Transfer
     * @param string $method - method to mock (will vary depending on test)
     * @param string $data - data that the method should expect to see
     * @param string $exception - optional exception to throw
     * @return MockInterface
     */
    private function mockTransfer($method, $data = '', $exception = '')
    {
        $transfer = m::mock('overload:' . Transfer::class);

        if ($method) {
            if (empty($exception)) {
                $transfer->shouldReceive($method)
                    ->with($data)
                    ->times(1);
            } else {
                $transfer->shouldReceive($method)
                    ->with($data)
                    ->times(1)
                    ->andThrow(new Exception($exception));
            }
        }

        return $transfer;
    }

    /**
     * Generates dummy data to use with the append function
     *
     * @param int $pageSize - page size (i.e. num of rows to generate)
     * @param int $pageNum - page number (so indexing uses right value)
     * @return mixed[]
     */
    private function generateData($pageSize = 50, $pageNum = 1)
    {
        $baseData = [
            '_index' => 1,
            'UserId' => 1
        ];

        $data = [];
        $dataString = '';

        $start = 1;
        if ($pageNum > 1) {
            $start = ($pageSize * ($pageNum - 1) + 1);
        }

        $end = $pageSize;
        if ($pageNum > 1) {
            $end = $pageSize * $pageNum;
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i >= 2) {
                $dataString .= ',';
            }
            $baseData['_index'] = $i;
            $baseData['UserId'] = $i;
            $dataString .= json_encode($baseData);
            $data[] = $baseData;
        }

        return [
            'array' => $data,
            'string' => $dataString
        ];
    }
}
