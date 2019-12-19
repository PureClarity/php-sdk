<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

class Order extends Feed
{
    /** @var string $feedType */
    protected $feedType = self::FEED_TYPE_ORDER;

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [
        'OrderID',
        'UserId',
        'Email',
        'DateTime',
        'ProdCode',
        'Quantity',
        'UnitPrice',
        'LinePrice'
    ];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [
        'OrderID'
    ];

    /** @var string $feedStart */
    protected $feedStart = 'OrderId,UserId,Email,DateTimeStamp,ProdCode,Quantity,UnityPrice,LinePrice';

    /** @var string $feedEnd */
    protected $feedEnd = '';

    /**
     * @param $orderData
     * @return false|string
     */
    public function processData($orderData)
    {
        $data = '';
        foreach ($orderData as $orderLine) {
            $data .= $orderLine['OrderID'] . ',' .
                     $orderLine['UserId'] . ',' .
                     $orderLine['Email'] . ',' .
                     $orderLine['DateTime'] . ',' .
                     $orderLine['ProdCode'] . ',' .
                     $orderLine['Quantity'] . ',' .
                     $orderLine['UnitPrice'] . ',' .
                     $orderLine['LinePrice'] . PHP_EOL;
        }

        return $data;
    }

    /**
     * @param mixed[] $orderData
     * @return array
     */
    protected function validate($orderData)
    {
        $errors = [];
        foreach ($orderData as $orderLine) {
            $errors = array_merge(
                $errors,
                parent::validate($orderLine)
            );
        }

        return $errors;
    }
}
