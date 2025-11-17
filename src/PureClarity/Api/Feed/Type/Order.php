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

    /** @var string $itemSeparator - No separator for CSV format */
    protected $itemSeparator = '';

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
        'OrderID',
        'ProdCode',
        'Quantity',
        'UnitPrice',
        'LinePrice'
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
        $lines = [];
        foreach ($orderData as $orderLine) {
            $lines[] = PHP_EOL . $orderLine['OrderID'] . ',' .
                      $orderLine['UserId'] . ',' .
                      $orderLine['Email'] . ',' .
                      $orderLine['DateTime'] . ',' .
                      $orderLine['ProdCode'] . ',' .
                      $orderLine['Quantity'] . ',' .
                      $orderLine['UnitPrice'] . ',' .
                      $orderLine['LinePrice'];
        }

        return implode('', $lines);
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
            foreach (parent::validate($orderLine) as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
