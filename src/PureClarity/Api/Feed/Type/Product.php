<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

/**
 * Class Product
 *
 * Product Feed class - sets parameters required for the Product Feed
 */
class Product extends Feed
{
    /** @var string $feedType */
    protected $feedType = self::FEED_TYPE_PRODUCT;

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [
        'Id',
        'Title',
        'Categories',
        'Link',
        'Image',
        'Prices'
    ];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [
        'Id',
        'Title',
        'Link'
    ];

    /** @var string $feedStart */
    protected $feedStart = '{"Version": 2, "Products":[';

    /** @var string $feedEnd */
    protected $feedEnd = ']}';
}
