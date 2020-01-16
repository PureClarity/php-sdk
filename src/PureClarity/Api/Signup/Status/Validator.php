<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\Status;

use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\Status;

/**
 * Class Validator
 *
 * Validates data provided to the Signup Status Request
 */
class Validator
{
    /** @var string[] */
    private $errors = [];

    /**
     * Required parameters for this request
     *
     * @var string[]
     */
    private $requiredParams = [
        Status::PARAM_ID, // Request ID
        Status::PARAM_REGION // Region ID
    ];

    /**
     * Returns the errors stored by this validator
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validates that all the necessary params are present and well formatted
     *
     * @param mixed[] $params
     * @return boolean
     */
    public function isValid($params)
    {
        $this->validateRequired($params);
        $this->validateRegion($params);

        return empty($this->errors);
    }

    /**
     * Validates that all the required params are present
     *
     * @param mixed[] $params
     */
    private function validateRequired($params)
    {
        $diff = array_diff($this->requiredParams, array_keys($params));

        if ($diff) {
            $this->errors[] = 'Missing Required Parameters: ' . implode(',', $diff);
        }
    }

    /**
     * Validates the provided Region
     *
     * @param mixed[] $params
     */
    private function validateRegion($params)
    {
        if (isset($params[Status::PARAM_REGION]) && !empty($params[Status::PARAM_REGION])) {
            $regions = new Regions();
            if ($regions->isValidRegion($params[Status::PARAM_REGION]) === false) {
                $this->errors[] = 'Invalid Region';
            }
        }
    }
}
