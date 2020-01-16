<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Signup\Status;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Status
 *
 * Handles Sending Signup Status requests to PureClarity
 */
class Requestor
{

    /** @var string */
    const PARAM_ID = 'Id';

    /**
     * Sends the Signup Status request to PureClarity
     *
     * @param string $requestId - The Request ID that was sent to PureClarity as part of the Signup data
     * @param integer $region - PureClarity Region ID
     * @return mixed[]
     * @throws Exception
     */
    public function send($requestId, $region)
    {
        $request = $this->buildRequest($requestId);
        $url = $this->getSignupStatusApiEndpointUrl($region);

        $curl = new Curl();

        $curl->post($url, $request);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody(),
            'error' => $curl->getError()
        ];
    }

    /**
     * Gets the Signup Status API endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    private function getSignupStatusApiEndpointUrl($region)
    {
        $endpoints = new Endpoints();
        return $endpoints->getSignupStatusEndpoint($region);
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
            self::PARAM_ID => $requestId
        ];

        return json_encode($requestData);
    }
}
