<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup;

use Exception;
use PureClarity\Api\Signup\Status\Validator;
use PureClarity\Api\Signup\Status\Requestor;
use PureClarity\Api\Signup\Status\Processor;

/**
 * Class Status
 *
 * Handles Signup Status requests - used to verify if a signup is complete.
 */
class Status
{
    /** @var string */
    const PARAM_ID = 'id';

    /** @var string */
    const PARAM_REGION = 'region';

    /**
     * Validates, Sends & Processes Signup Status requests
     *
     * @param mixed[] $params
     *
     * @return mixed[]
     */
    public function request($params)
    {
        $validator = new Validator();

        if ($validator->isValid($params)) {
            $result = $this->handleRequest($params);
        } else {
            $result = [
                'status' => 0,
                'errors' => $validator->getErrors(),
                'response' => [],
                'complete' => false,
            ];
        }

        return $result;
    }

    /**
     * Requests & Processes the Signup Status request
     * @param mixed[] $params
     * @return mixed[]
     */
    private function handleRequest($params)
    {
        $result = [
            'status' => 0,
            'errors' => [],
            'response' => [],
            'complete' => false,
        ];

        try {
            $requestor = new Requestor();
            $response = $requestor->send($params['id'], $params['region']);

            $processor = new Processor();
            $result = $processor->process($response);
        } catch (Exception $e) {
            $result['errors'][] = 'Exception: ' . $e->getMessage();
        }

        return $result;
    }
}
