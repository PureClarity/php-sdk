<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup;

use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Transfer\Curl;
use Exception;

/**
 * Class Submit
 */
class Submit
{
    /**
     * Required parameters for this request
     *
     * @var string[]
     */
    private $requiredParams = [
        'firstname' => 'First name',
        'lastname' => 'Last name',
        'email' => 'Email Address',
        'company' => 'Company',
        'password' => 'Password',
        'store_name' => 'Store Name',
        'region' => 'Region',
        'url' => 'URL',
        'platform' => 'Platform',
        'currency' => 'Currency',
        'timezone' => 'Timezone'
    ];

    /**
     * Sends the signup request to PureClarity
     *
     * @param mixed[] $params
     *
     * @return mixed[]
     */
    public function request($params)
    {
        $result = [
            'error' => '',
            'request_id' => '',
            'status' => '',
            'response' => '',
            'success' => false,
        ];

        $errors = $this->validate($params);

        if (empty($errors)) {
            try {
                $result['request_id'] = uniqid('', true);
                $response = $this->sendRequest($result['request_id'], $params);

                $result['status'] = $response['status'];
                $result['response'] = $response['body'];

                if ($response['status'] === 400) {
                    $responseData = json_decode($response['body']);
                    $result['error'] = 'Signup error: ' . implode('|', $responseData['errors']);
                } elseif ($response['status'] !== 200) {
                    $result['error'] = 'PureClarity server error occurred. If this persists, '
                                     . 'please contact PureClarity support. Error code ' . $response['status'];
                } else {
                    $result['success'] = true;
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'timed') !== false) {
                    $result['error'] = 'Connection to PureClarity server timed out, please try again';
                } else {
                    $result['error'] = 'A general error occurred: ' . $e->getMessage();
                }
            }
        } else {
            $result['error'] = implode(',', $errors);
        }

        return $result;
    }

    /**
     * @param $requestId
     * @param $params
     * @return array
     * @throws Exception
     */
    private function sendRequest($requestId, $params)
    {
        $request = $this->buildRequest($requestId, $params);
        $url = $this->getSignupApiEndpointUrl($params['region']);

        $curl = new Curl();

        $curl->post($url, $request);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody()
        ];
    }

    /**
     * @param $region
     * @return string
     * @throws Exception
     */
    private function getSignupApiEndpointUrl($region)
    {
        return Endpoints::getSignupRequestEndpoint($region);
    }

    /**
     * Validates that all the necessary params are present and well formatted
     *
     * @param mixed[] $params
     * @return string[]
     */
    private function validate($params)
    {
        $errors = [];
        // Check all required params are present
        foreach ($this->requiredParams as $key => $label) {
            if (!isset($params[$key]) || empty($params[$key])) {
                $errors[] = 'Missing ' . $label;
            }
        }

        if (isset($params['email']) && !filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid Email Address';
        }

        if (isset($params['url']) && !$this->isValidUrl($params['url'], ['http', 'https'])) {
            $errors[] = 'Invalid URL';
        }

        if (Regions::isValidRegion($params['region']) === false) {
            $errors[] = 'Invalid Region';
        }

        // check password is strong enough
        if (isset($params['password']) &&
            !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/', $params['password'])
        ) {
            $errors[] = 'Password not strong enough, must contain 1 lowercase letter,'
                      . ' 1 uppercase letter, 1 number and be 8 characters or longer';
        }

        return $errors;
    }

    /**
     * Builds the JSON for the request from the parameters provided
     *
     * @param string $requestId
     * @param mixed[] $params
     * @return string
     */
    private function buildRequest($requestId, $params)
    {
        $requestData = [
            'Id' => $requestId,
            'Platform' => $params['platform'],
            'Email' => $params['email'],
            'FirstName' => $params['firstname'],
            'LastName' => $params['lastname'],
            'Company' => $params['company'],
            'Region' => Regions::getRegionName($params['region']),
            'Currency' => $params['currency'],
            'TimeZone' => $params['timezone'],
            'Url' => $params['url'],
            'Password' => $params['password'],
            'StoreName' => $params['store_name']
        ];

        return json_encode($requestData);
    }

    /**
     * Validate URL and check that it has allowed scheme
     *
     * @param string $value
     * @param array $allowedSchemes
     * @return bool
     */
    public function isValidUrl($value, array $allowedSchemes = [])
    {
        $isValid = true;

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        if ($isValid && !empty($allowedSchemes)) {
            $url = parse_url($value);
            if (empty($url['scheme']) || !in_array($url['scheme'], $allowedSchemes)) {
                $isValid = false;
            }
        }
        return $isValid;
    }
}
