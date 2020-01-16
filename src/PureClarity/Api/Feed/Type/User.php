<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

/**
 * Class User
 *
 * User Feed class - sets parameters required for the User Feed
 */
class User extends Feed
{
    /** @var string $feedType */
    protected $feedType = self::FEED_TYPE_USER;

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [
        'UserId'
    ];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [
        'UserId'
    ];

    /** @var string $feedStart */
    protected $feedStart = '{"Version": 2, "Users":[';

    /** @var string $feedEnd */
    protected $feedEnd = ']}';
}
