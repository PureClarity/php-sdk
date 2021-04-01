<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Info;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Dashboard
 *
 * Handles Dashboard info API call
 */
class Dashboard
{
    /** @var Curl $curl */
    private $curl;

    /** @var string $accessKey */
    private $accessKey;

    /** @var string $secretKey */
    private $secretKey;

    /** @var string $region */
    private $region;

    /** @var string $endpoint */
    private $endpoint;

    /**
     * @param string $accessKey
     * @param string $secretKey
     * @param integer $region
     */
    public function __construct($accessKey, $secretKey, $region)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
    }

    /**
     * Triggers the sending of the Delta data to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    public function request()
    {
        return $this->send();
    }

    /**
     * Sends the provided delta string to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    private function send()
    {
        $url = $this->getDashboardEndpoint($this->region);

        $curl = $this->getCurlHandler();
        $curl->post($url, json_encode([
        	'AccessKey' => $this->accessKey,
        	'SecretKey' => $this->secretKey
        ]));

        $status = $curl->getStatus();
        $error = $curl->getError();
        $body = $curl->getBody();

        if ($status < 200 || $status > 299) {
            throw new Exception(
                'Error: HTTP ' . $status . ' Response | ' .
                'Error Message: ' . $error . ' | ' .
                'Body: ' . $body
            );
        }

        if ($error) {
            throw new Exception(
                'Error: ' . $error
            );
        }

        return [
            'status' => $status,
            'body' => $body
        ];
    }

    /**
     * Gets the PureClarity Endpoint for deltas
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getDashboardEndpoint($region)
    {
        if ($this->endpoint === null) {
            $endpoints = new Endpoints();
            $this->endpoint = $endpoints->getDashboardEndpoint($region);
        }

        return $this->endpoint;
    }

    /**
     * Gets the PureClarity Curl Handler
     *
     * @return Curl
     */
    private function getCurlHandler()
    {
        if ($this->curl === null) {
            $this->curl = new Curl();
            $this->curl->setDataType('application/json');
        }

        return $this->curl;
    }
}
