<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\AddStore;

use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Resource\Regions;
use PureClarity\Api\Signup\AddStore;
use PureClarity\Api\Transfer\Curl;
use Exception;
use PureClarity\Api\Signup\Submit;

/**
 * Class Requestor
 *
 * Handles Sending Add Store requests to PureClarity
 */
class Requestor
{
    /** @var string */
    const PARAM_ID = 'Id';

    /** @var string */
    const PARAM_ACCESSKEY = 'AccessKey';

    /** @var string */
    const PARAM_SECRETKEY = 'SecretKey';

    /** @var string */
    const PARAM_REGION = 'Region';

    /** @var string */
    const PARAM_STORE_NAME = 'StoreName';

    /** @var string */
    const PARAM_URL = 'Url';

    /** @var string */
    const PARAM_PLATFORM = 'Platform';

    /** @var string */
    const PARAM_CURRENCY = 'Currency';

    /** @var string */
    const PARAM_TIMEZONE = 'TimeZone';

    /**
     * Sends the Add Store request to PureClarity
     *
     * @param string $requestId - The Request ID for this request
     * @param mixed[] $params - Add Store Request parameters
     * @return mixed[]
     * @throws Exception
     */
    public function send($requestId, $params)
    {
        $request = $this->buildRequest($requestId, $params);
        $url = $this->getEndpointUrl($params[Submit::PARAM_REGION]);

        $curl = new Curl();

        $curl->post($url, $request);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody(),
            'error' => $curl->getError()
        ];
    }

    /**
     * Gets the Add Store API endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    private function getEndpointUrl($region)
    {
        $endpoints = new Endpoints();
        return $endpoints->getAddStoreEndpoint($region);
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
            self::PARAM_ACCESSKEY => $params[AddStore::PARAM_ACCESS_KEY],
            self::PARAM_SECRETKEY => $params[AddStore::PARAM_SECRET_KEY],
            self::PARAM_PLATFORM => $params[AddStore::PARAM_PLATFORM],
            self::PARAM_REGION => $regions->getRegionName($params[AddStore::PARAM_REGION]),
            self::PARAM_CURRENCY => $params[AddStore::PARAM_CURRENCY],
            self::PARAM_TIMEZONE => $params[AddStore::PARAM_TIMEZONE],
            self::PARAM_URL => $params[AddStore::PARAM_URL],
            self::PARAM_STORE_NAME => $params[AddStore::PARAM_STORE_NAME]
        ];

        return json_encode($requestData);
    }
}
