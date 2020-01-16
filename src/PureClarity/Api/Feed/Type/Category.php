<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

/**
 * Class Category
 *
 * Category Feed class - sets parameters required for the Category Feed
 */
class Category extends Feed
{
    /** @var string $feedType */
    protected $feedType = self::FEED_TYPE_CATEGORY;

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [
        'Id',
        'DisplayName',
        'Image',
        'Link',
        'ParentIds',
        'Description'
    ];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [
        'Id',
        'DisplayName',
        'Link'
    ];

    /** @var string $feedStart */
    protected $feedStart = '{"Version": 2, "Categories":[';

    /** @var string $feedEnd */
    protected $feedEnd = ']}';
}
