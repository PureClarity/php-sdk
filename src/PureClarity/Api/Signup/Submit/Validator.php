<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\Submit;

use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\Submit;

/**
 * Class Validator
 *
 * Validates data provided to the Signup Request
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
        Submit::PARAM_FIRSTNAME, // First name
        Submit::PARAM_LASTNAME, // 'Last name',
        Submit::PARAM_EMAIL, // 'Email Address',
        Submit::PARAM_COMPANY, // 'Company',
        Submit::PARAM_PASSWORD, //  => 'Password',
        Submit::PARAM_STORE_NAME, //  => 'Store Name',
        Submit::PARAM_REGION, //  => 'Region',
        Submit::PARAM_URL, //  => 'URL',
        Submit::PARAM_PLATFORM, //  => 'Platform',
        Submit::PARAM_CURRENCY, //  => 'Currency',
        Submit::PARAM_TIMEZONE, //  => 'Timezone'
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
        $this->validateEmail($params);
        $this->validateUrl($params);
        $this->validateRegion($params);
        $this->validatePassword($params);

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
     * Validates Email Address
     *
     * @param mixed[] $params
     */
    private function validateEmail($params)
    {
        if (isset($params[Submit::PARAM_EMAIL]) && !filter_var($params[Submit::PARAM_EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid Email Address';
        }
    }

    /**
     * Validate URL is present and checks it's valid
     *
     * @param mixed[] $params
     */
    private function validateUrl($params)
    {
        if (isset($params[Submit::PARAM_URL]) && !$this->isValidUrl($params[Submit::PARAM_URL])) {
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
        if (isset($params[Submit::PARAM_REGION]) && !empty($params[Submit::PARAM_REGION])) {
            $regions = new Regions();
            if ($regions->isValidRegion($params[Submit::PARAM_REGION]) === false) {
                $this->errors[] = 'Invalid Region';
            }
        }
    }

    /**
     * Validates the password is strong enough
     *
     * @param mixed[] $params
     */
    private function validatePassword($params)
    {
        // check password is strong enough
        if (isset($params[Submit::PARAM_PASSWORD]) &&
            !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $params[Submit::PARAM_PASSWORD])
        ) {
            $this->errors[] = 'Password not strong enough, must contain 1 lowercase letter,'
                            . ' 1 uppercase letter, 1 number and be 8 characters or longer';
        }
    }
}
