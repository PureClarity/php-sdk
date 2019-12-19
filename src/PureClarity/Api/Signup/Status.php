<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Status
 */
class Status
{
    /**
     * Required parameters for this request
     *
     * @var string[]
     */
    private $requiredParams = [
        'id' => 'Request ID',
        'region' => 'Region',
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
            'status' => '',
            'response' => '',
            'complete' => false,
        ];

        $errors = $this->validate($params);

        if (empty($errors)) {
            try {
                $response = $this->sendRequest($params['id'], $params['region']);

                $result['status'] = $response['status'];
                $result['response'] = $response['body'] ? (array)json_decode($response['body']) : '';

                if ($response['status'] === 400) {
                    $responseData = json_decode($response['body']);
                    $result['error'] = 'Signup status error: ' . implode('|', $responseData['errors']);
                } elseif ($response['status'] !== 200) {
                    $result['error'] = 'PureClarity server error occurred. If this persists, '
                                     . 'please contact PureClarity support. Error code ' . $response['status'];
                } else {
                    if ($result['response']['Complete'] === true) {
                        $result['response'] = [
                            'access_key' => $result['response']['AccessKey'],
                            'secret_key' => $result['response']['SecretKey'],
                            'region' => $params['region']
                        ];

                        $result['complete'] = true;
                    }
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
     * @param $region
     * @return array
     * @throws Exception
     */
    private function sendRequest($requestId, $region)
    {
        $request = $this->buildRequest($requestId);
        $url = $this->getSignupStatusApiEndpointUrl($region);

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
    private function getSignupStatusApiEndpointUrl($region)
    {
        return Endpoints::getSignupStatusEndpoint($region);
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

        if (Regions::isValidRegion($params['region']) === false) {
            $errors[] = 'Invalid Region';
        }

        return $errors;
    }

    /**
     * Builds the JSON for the request from the parameters provided
     *
     * @param string $requestId
     * @return string
     */
    private function buildRequest($requestId)
    {
        $requestData = [
            'Id' => $requestId
        ];

        return json_encode($requestData);
    }
}
