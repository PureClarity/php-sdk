<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\AddStore;

/**
 * Class Processor
 */
class Processor
{
    /**
     * Processes a response from the PureClarity Add Store API Endpoint
     *
     * Expected format of $response:
     *
     *  'status' - HTTP status code of the response
     *  'body' - body of the response
     *  'error' - any curl errors
     *
     * @param mixed[] $response
     * @return mixed[]
     */
    public function process($response)
    {
        $result = [
            'status' => 0,
            'errors' => [],
            'response' => [],
            'success' => false,
        ];

        if ($this->isResponseFormatValid($response) === false) {
            $result['errors'][] = 'Invalid Response';
        } else {
            $result['status'] = $response['status'];
            $result['response'] = $this->processBody($response);
            $result['errors'] = $this->processErrors($result['response'], $response);

            if ($response['status'] >= 200 && $response['status'] <= 299) {
                $result['success'] = true;
            }
        }

        return $result;
    }

    /**
     * Checks to see if the provided response is valid
     *
     * @param mixed[] $response
     * @return bool
     */
    private function isResponseFormatValid($response)
    {
        return array_keys($response) === ['status', 'body', 'error'];
    }

    /**
     * Processes the response body into an array (will be a json string or empty)
     * @param mixed[] $response
     * @return mixed[]
     */
    private function processBody($response)
    {
        return $response['body'] ? (array)json_decode($response['body']) : [];
    }

    /**
     * Determine what errors have occurred in the provided response
     *
     * @param mixed[] $processedBody
     * @param mixed[] $response
     * @return mixed[]
     */
    private function processErrors($processedBody, $response)
    {
        $errors = isset($processedBody['errors']) ? $processedBody['errors'] : [];
    
        if ($response['error']) {
            $errors[] = $response['error'];
        }

        return $errors;
    }
}
