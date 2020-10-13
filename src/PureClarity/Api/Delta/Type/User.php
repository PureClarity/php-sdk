<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Delta\Type;

use PureClarity\Api\Delta\Base;

class User extends Base
{
    /** @var string $addKey */
    protected $dataKey = 'Users';

    /** @var string $deleteKey */
    protected $deleteKey = 'DeleteUsers';
}
