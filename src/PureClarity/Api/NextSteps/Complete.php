<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\NextSteps;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Complete
 *
 * Handles NextSteps completion API call
 */
class Complete
{
    /** @var Curl $curl */
    private $curl;

    /** @var string $accessKey */
    private $accessKey;

    /** @var string $nextStepId */
    private $nextStepId;

    /** @var string $region */
    private $region;

    /** @var string $endpoint */
    private $endpoint;

    /**
     * @param string $accessKey
     * @param string $nextStepId
     * @param integer $region
     */
    public function __construct($accessKey, $nextStepId, $region)
    {
        $this->accessKey  = $accessKey;
        $this->nextStepId = $nextStepId;
        $this->region     = $region;
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
     * Sends the Next Step being completed to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    private function send()
    {
        $url = $this->getNextStepsCompleteEndpoint($this->region);

        $curl = $this->getCurlHandler();
        $curl->post($url, json_encode([
            'appkey' => $this->accessKey,
            'id' => $this->nextStepId
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
     * Gets the PureClarity Endpoint for the Next Step Complete action
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getNextStepsCompleteEndpoint($region)
    {
        if ($this->endpoint === null) {
            $endpoints = new Endpoints();
            $this->endpoint = $endpoints->getNextStepsCompleteEndpoint($region);
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
