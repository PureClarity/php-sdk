<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Delta;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

abstract class Base
{
    /** @var string $accessKey */
    private $accessKey;

    /** @var string $secretKey */
    private $secretKey;

    /** @var string $region */
    private $region;

    /** @var mixed[] $data */
    private $data = [];

    /** @var mixed[] $delete */
    private $delete = [];

    /** @var string $endpoint */
    private $endpoint;

    /** @var string $dataKey */
    protected $dataKey;

    /** @var string $deleteKey */
    protected $deleteKey;

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
     * Adds a Delete Delta row
     *
     * @param string $delete
     */
    public function addDelete($delete)
    {
        $this->delete[] = $delete;
    }

    /**
     * Adds a normal Delta Row
     *
     * @param mixed[] $data
     */
    public function addData($data)
    {
        $this->data[] = $data;
    }

    /**
     * Builds a Base Request array
     *
     * @return array
     */
    private function buildRequest()
    {
        return [
            'AppKey'         => $this->accessKey,
            'Secret'         => $this->secretKey,
            $this->dataKey   => [],
            $this->deleteKey => [],
            'Format'         => 'magentoplugin1.0.0'
        ];
    }

    /**
     * Triggers the sending of the Delta data to PureClarity
     *
     * @return mixed[]
     * @throws Exception
     */
    public function send()
    {
        $responses = [];
        if (count($this->delete) > 0) {
            $responses[] = $this->sendDeletes();
        }

        if (count($this->data) > 0) {
            $dataResponses = $this->sendData();
            $responses = array_merge($responses, $dataResponses);
        }

        return $responses;
    }

    /**
     * Handles sending just delete Deltas
     *
     * @return mixed[]
     * @throws Exception
     */
    private function sendDeletes()
    {
        $request = $this->buildRequest();
        $request[$this->deleteKey] = $this->delete;
        $body = json_encode($request);
        return $this->sendDelta($body);
    }

    /**
     * Handles sending data Deltas, grouped into chunks of 10 so as not to hit the data limit
     *
     * @return mixed[]
     * @throws Exception
     */
    private function sendData()
    {
        $responses = [];
        $requestBase = $this->buildRequest();
        $chunks = array_chunk($this->data, 10);
        foreach ($chunks as $data) {
            $request = $requestBase;
            $request[$this->dataKey] = $data;
            $body = json_encode($request);
            $responses[] = $this->sendDelta($body);
        }

        return $responses;
    }

    /**
     * Sends the provided delta string to PureClarity
     *
     * @param string $body
     * @return mixed[]
     * @throws Exception
     */
    private function sendDelta($body)
    {
        $url = $this->getDeltaEndpoint($this->region);

        $curl = new Curl();
        $curl->setDataType('application/json');
        $curl->post($url, $body);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody(),
            'error' => $curl->getError()
        ];
    }

    /**
     * Gets the PureClarity Endpoint for deltas
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getDeltaEndpoint($region)
    {
        if ($this->endpoint === null) {
            $endpoints = new Endpoints();
            $this->endpoint = $endpoints->getDeltaEndpoint($region);
        }

        return $this->endpoint;
    }
}
