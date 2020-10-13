<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feedback;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Submit
 *
 * Handles plugin Feedback API call
 */
class Submit
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

    /** @var string $feedback */
    private $feedback;

    /** @var string $platform */
    private $platform;

    /**
     * @param string $accessKey
     * @param string $secretKey
     * @param integer $region
     * @param string $feedback
     * @param string $platform
     */
    public function __construct($accessKey, $secretKey, $region, $feedback, $platform)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
        $this->feedback  = $feedback;
        $this->platform  = $platform;
    }

    /**
     * Triggers the sending of the Feedback data to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    public function request()
    {
        return $this->send();
    }

    /**
     * Sends the provided feedback to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    private function send()
    {
        $url = $this->getFeedbackEndpoint($this->region);

        $curl = $this->getCurlHandler();
        $curl->post($url, json_encode([
            'AccessKey' => $this->accessKey,
            'SecretKey' => $this->secretKey,
            'Feedback' => $this->feedback,
            'Platform' => $this->platform
        ]));

        $status = $curl->getStatus();
        $error = $curl->getError();
        $body = $curl->getBody();

        if ($status !== 200) {
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
     * Gets the PureClarity Endpoint for Feedback
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getFeedbackEndpoint($region)
    {
        if ($this->endpoint === null) {
            $endpoints = new Endpoints();
            $this->endpoint = $endpoints->getFeedbackEndpoint($region);
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
