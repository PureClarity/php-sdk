<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Account\Validate;

use PureClarity\Api\Account\Validate;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Transfer\Curl;
use Exception;

/**
 * Class Requestor
 *
 * Handles Sending Validate Account requests to PureClarity
 */
class Requestor
{
    /** @var string */
    const PARAM_ACCESS_KEY = 'AccessKey';

    /** @var string */
    const PARAM_SECRET_KEY = 'SecretKey';

    /**
     * Sends the Validate Account request to PureClarity
     *
     * @param mixed[] $params - Submission Request parameters
     * @return mixed[]
     * @throws Exception
     */
    public function send($params)
    {
        $request = $this->buildRequest($params);
        $url = $this->getEndpoint($params[Validate::PARAM_REGION]);

        $curl = new Curl();

        $curl->post($url, $request);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody(),
            'error' => $curl->getError()
        ];
    }

    /**
     * Gets the PureClarity Endpoint for Validating Accounts
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getEndpoint($region)
    {
        $endpoints = new Endpoints();
        return $endpoints->getValidateAccountEndpoint($region);
    }

    /**
     * Builds the JSON for the request from the parameters provided
     *
     * @param mixed[] $params
     * @return string
     */
    private function buildRequest($params)
    {
        $regions = new Regions();

        $requestData = [
            self::PARAM_ACCESS_KEY => $params[Validate::PARAM_ACCESS_KEY],
            self::PARAM_SECRET_KEY => $params[Validate::PARAM_SECRET_KEY]
        ];

        return json_encode($requestData);
    }
}
