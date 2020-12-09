<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup;

use PureClarity\Api\Signup\AddStore\Validator;
use PureClarity\Api\Signup\AddStore\Requestor;
use PureClarity\Api\Signup\AddStore\Processor;
use Exception;

/**
 * Class AddStore
 *
 * Handles Add Store to account requests - request to create a new store on an existing PureClarity account
 */
class AddStore
{
    /** @var string */
    const PARAM_ACCESS_KEY = 'access_key';

    /** @var string */
    const PARAM_SECRET_KEY = 'secret_key';

    /** @var integer */
    const PARAM_REGION = 'region';

    /** @var string */
    const PARAM_STORE_NAME = 'store_name';

    /** @var string */
    const PARAM_URL = 'url';

    /** @var string */
    const PARAM_PLATFORM = 'platform';

    /** @var string */
    const PARAM_CURRENCY = 'currency';

    /** @var string */
    const PARAM_TIMEZONE = 'timezone';

    /** @var string */
    private $requestId;

    /**
     * Sets the Request ID for this request
     * @param string $value
     */
    public function setRequestId($value)
    {
        $this->requestId = $value;
    }

    /**
     * Gets the Request ID, generates one if not set
     *
     * @return string
     */
    public function getRequestId()
    {
        if ($this->requestId === null) {
            $this->requestId = uniqid('', true);
        }
        return $this->requestId;
    }

    /**
     * Validates & Sends the Add Store request to PureClarity
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
                'errors' => $validator->getErrors(),
                'success' => false,
                'request_id' => ''
            ];
        }

        return $result;
    }

    /**
     * Sends the Add Store request & Processes the response
     * @param mixed[] $params
     * @return array|mixed[]
     */
    private function handleRequest($params)
    {
        $result = [
            'status' => 0,
            'response' => [],
            'errors' => [],
            'success' => false,
            'request_id' => ''
        ];

        try {
            $requestor = new Requestor();
            $response = $requestor->send($this->getRequestId(), $params);

            $processor = new Processor();
            $result = $processor->process($response);
            $result['request_id'] = $this->getRequestId();
        } catch (Exception $e) {
            $result['errors'][] = 'Exception: ' . $e->getMessage();
        }

        return $result;
    }
}
