<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

/**
 * Class Brand
 *
 * Brand Feed class - sets parameters required for the Brand Feed
 */
class Brand extends Feed
{
    /** @var string $feedType */
    protected $feedType = self::FEED_TYPE_BRAND;

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [
        'Id',
        'DisplayName',
        'Image',
        'Description',
        'Link'
    ];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [
        'Id',
        'DisplayName'
    ];

    /** @var string $feedStart */
    protected $feedStart = '{"Version": 2, "Brands":[';

    /** @var string $feedEnd */
    protected $feedEnd = ']}';
}
