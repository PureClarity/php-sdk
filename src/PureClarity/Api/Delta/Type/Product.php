<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Delta\Type;

use PureClarity\Api\Delta\Base;

class Product extends Base
{
    /** @var string $addKey */
    protected $dataKey = 'Products';

    /** @var string $deleteKey */
    protected $deleteKey = 'DeleteProducts';
}
