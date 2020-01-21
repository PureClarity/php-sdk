<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\Submit;

use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Transfer\Curl;
use Exception;
use PureClarity\Api\Signup\Submit;

/**
 * Class Requestor
 *
 * Handles Sending Signup requests to PureClarity
 */
class Requestor
{
    /** @var string */
    const PARAM_ID = 'Id';

    /** @var string */
    const PARAM_FIRSTNAME = 'FirstName';

    /** @var string */
    const PARAM_LASTNAME = 'LastName';

    /** @var string */
    const PARAM_EMAIL = 'Email';

    /** @var string */
    const PARAM_COMPANY = 'Company';

    /** @var string */
    const PARAM_PASSWORD = 'Password';

    /** @var string */
    const PARAM_STORE_NAME = 'StoreName';

    /** @var string */
    const PARAM_REGION = 'Region';

    /** @var string */
    const PARAM_URL = 'Url';

    /** @var string */
    const PARAM_PLATFORM = 'Platform';

    /** @var string */
    const PARAM_CURRENCY = 'Currency';

    /** @var string */
    const PARAM_TIMEZONE = 'TimeZone';

    /**
     * Sends the Signup request to PureClarity
     *
     * @param string $requestId - The Request ID that was sent to PureClarity as part of the Signup data
     * @param mixed[] $params - Submission Request parameters
     * @return mixed[]
     * @throws Exception
     */
    public function send($requestId, $params)
    {
        $request = $this->buildRequest($requestId, $params);
        $url = $this->getSignupApiEndpointUrl($params[Submit::PARAM_REGION]);

        $curl = new Curl();

        $curl->post($url, $request);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody(),
            'error' => $curl->getError()
        ];
    }

    /**
     * Gets the Signup API endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    private function getSignupApiEndpointUrl($region)
    {
        $endpoints = new Endpoints();
        return $endpoints->getSignupRequestEndpoint($region);
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
        $regions = new Regions();

        $requestData = [
            self::PARAM_ID => $requestId,
            self::PARAM_PLATFORM => $params[Submit::PARAM_PLATFORM],
            self::PARAM_EMAIL => $params[Submit::PARAM_EMAIL],
            self::PARAM_FIRSTNAME => $params[Submit::PARAM_FIRSTNAME],
            self::PARAM_LASTNAME => $params[Submit::PARAM_LASTNAME],
            self::PARAM_COMPANY => $params[Submit::PARAM_COMPANY],
            self::PARAM_REGION => $regions->getRegionName($params[Submit::PARAM_REGION]),
            self::PARAM_CURRENCY => $params[Submit::PARAM_CURRENCY],
            self::PARAM_TIMEZONE => $params[Submit::PARAM_TIMEZONE],
            self::PARAM_URL => $params[Submit::PARAM_URL],
            self::PARAM_PASSWORD => $params[Submit::PARAM_PASSWORD],
            self::PARAM_STORE_NAME => $params[Submit::PARAM_STORE_NAME]
        ];

        return json_encode($requestData);
    }
}
