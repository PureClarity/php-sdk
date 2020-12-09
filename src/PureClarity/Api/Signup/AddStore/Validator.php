<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\AddStore;

use PureClarity\Api\Signup\AddStore;
use PureClarity\Api\Resource\Regions;

/**
 * Class Validator
 *
 * Validates data provided to the Add Store Request
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
        AddStore::PARAM_ACCESS_KEY,
        AddStore::PARAM_SECRET_KEY,
        AddStore::PARAM_REGION,
        AddStore::PARAM_STORE_NAME,
        AddStore::PARAM_URL,
        AddStore::PARAM_PLATFORM,
        AddStore::PARAM_CURRENCY,
        AddStore::PARAM_TIMEZONE
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
        $this->validateUrl($params);
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
     * Validate URL is present and checks it's valid
     *
     * @param mixed[] $params
     */
    private function validateUrl($params)
    {
        if (isset($params[AddStore::PARAM_URL]) && !$this->isValidUrl($params[AddStore::PARAM_URL])) {
            $this->errors[] = 'Invalid URL';
        }
    }

    /**
     * Validate URL and check that it is http/https
     *
     * @param string $value
     * @return bool
     */
    private function isValidUrl($value)
    {
        $isValid = true;

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $isValid = false;
        } else {
            $url = parse_url($value);
            if (empty($url['scheme']) || !in_array($url['scheme'], ['http', 'https'])) {
                $isValid = false;
            }
        }
        return $isValid;
    }

    /**
     * Validates the provided Region
     *
     * @param mixed[] $params
     */
    private function validateRegion($params)
    {
        if (isset($params[AddStore::PARAM_REGION]) && !empty($params[AddStore::PARAM_REGION])) {
            $regions = new Regions();
            if ($regions->isValidRegion($params[AddStore::PARAM_REGION]) === false) {
                $this->errors[] = 'Invalid Region';
            }
        }
    }
}
