<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup;

use PureClarity\Api\Signup\Submit\Validator;
use PureClarity\Api\Signup\Submit\Requestor;
use PureClarity\Api\Signup\Submit\Processor;
use Exception;

/**
 * Class Submit
 *
 * Handles Signup requests - request to create a PureClarity acccount
 */
class Submit
{
    /** @var string */
    const PARAM_FIRSTNAME = 'firstname';

    /** @var string */
    const PARAM_LASTNAME = 'lastname';

    /** @var string */
    const PARAM_EMAIL = 'email';

    /** @var string */
    const PARAM_COMPANY = 'company';

    /** @var string */
    const PARAM_PASSWORD = 'password';

    /** @var string */
    const PARAM_STORE_NAME = 'store_name';

    /** @var string */
    const PARAM_REGION = 'region';

    /** @var string */
    const PARAM_URL = 'url';

    /** @var string */
    const PARAM_PLATFORM = 'platform';

    /** @var string */
    const PARAM_CURRENCY = 'currency';

    /** @var string */
    const PARAM_TIMEZONE = 'timezone';

    /** @var string */
    const PARAM_PHONE = 'phone';

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
     * Validates & Sends the signup request to PureClarity
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
     * Sends the Signup request & Processes the response
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
