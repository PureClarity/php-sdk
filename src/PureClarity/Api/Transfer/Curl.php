<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Transfer;

use PureClarity\Api\Transfer\Curl\Client;

/**
 * Class Curl
 *
 * Handles Curl Requests
 *
 * @package PureClarity\Api
 */
class Curl
{
    /** @var mixed[] - Default CURL options, can be overridden per request */
    private $options = [
        CURLOPT_CONNECTTIMEOUT_MS => 5000,
        CURLOPT_TIMEOUT_MS => 5000,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FAILONERROR => false,
        CURLOPT_POST => true
    ];

    /** @var string $dataType */
    private $dataType = 'application/json';

    /** @var string|null $status */
    private $status = '';

    /** @var string|null $body */
    private $body = '';

    /** @var string|null $error */
    private $error = '';

    /**
     * Returns current data type
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * POSTs the CURL request
     *
     * @param string $url - URL to post to
     * @param string $payload - Data to send
     * @param array $options - options to set (can be used to override defaults)
     */
    public function post($url, $payload, $options = [])
    {
        $request = new Client();
        $request->init();

        $request->setopt(CURLOPT_URL, $url);

        foreach ($this->options as $optionKey => $optValue) {
            $request->setopt($optionKey, $optValue);
        }

        foreach ($options as $optionKey => $optValue) {
            $request->setopt($optionKey, $optValue);
        }

        $request->setopt(CURLOPT_POSTFIELDS, $payload);
        $request->setopt(
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: ' . $this->dataType,
                'Content-Length: ' . strlen($payload)
            ]
        );

        if (!$this->body = $request->exec()) {
            $this->error = $request->error();
        }

        $info = $request->getinfo();
        $this->status = isset($info['http_code']) ? $info['http_code'] : null;

        $request->close();
    }

    /**
     * Gets the http response status of the last CURL request
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the response body of the last CURL request
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Gets the error string for the last CURL request
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}
