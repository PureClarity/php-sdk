<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Account;

use Exception;
use PureClarity\Api\Account\Validate\Requestor;
use PureClarity\Api\Account\Validate\Validator;
use PureClarity\Api\Account\Validate\Processor;

/**
 * Class Validate
 *
 * Handles Validate Account info API call
 */
class Validate
{
    /** @var string */
    const PARAM_ACCESS_KEY = 'access_key';

    /** @var string */
    const PARAM_SECRET_KEY = 'secret_key';

    /** @var integer */
    const PARAM_REGION = 'region';

    /**
     * Validates & Sends the Validate Account request to PureClarity
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
                'response' => [],
                'errors' => $validator->getErrors()
            ];
        }

        return $result;
    }

    /**
     * Sends the Validate Account request & Processes the response
     * @param mixed[] $params
     * @return array|mixed[]
     */
    private function handleRequest($params)
    {
        $result = [
            'status' => 0,
            'response' => [],
            'errors' => []
        ];

        try {
            $requestor = new Requestor();
            $response = $requestor->send($params);

            $processor = new Processor();
            $result = $processor->process($response);
        } catch (Exception $e) {
            $result['errors'][] = 'Exception: ' . $e->getMessage();
        }

        return $result;
    }
}
