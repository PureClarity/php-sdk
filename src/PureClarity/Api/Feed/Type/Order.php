<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed\Type;

use PureClarity\Api\Feed\Feed;

/**
 * Class Order
 *
 * Order Feed class - sets parameters required for the Order Feed
 */
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
     * Override of processData, so that we can accept multiple lines per order
     *
     * @param mixed[] $orderData
     * @return string
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
     * Override of validate, so that we can validate multiple lines per order
     *
     * @param mixed[] $orderData
     * @return string[]
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
