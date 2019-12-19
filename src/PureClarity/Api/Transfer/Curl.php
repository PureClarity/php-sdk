<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Transfer;

/**
 * Class Curl
 *
 * Handles Curl Requests
 *
 * @package PureClarity\Api
 */
class Curl
{
    /** @var array  */
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
    private $status;

    /** @var string|null $body */
    private $body;

    /** @var string|null $error */
    private $error;

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @param string $url
     * @param string $payload
     * @param array $options
     */
    public function post($url, $payload, $options = [])
    {
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);

        foreach ($this->options as $optionKey => $optValue) {
            curl_setopt($request, $optionKey, $optValue);
        }

        foreach ($options as $optionKey => $optValue) {
            curl_setopt($request, $optionKey, $optValue);
        }

        curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
        curl_setopt(
            $request,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: ' . $this->dataType,
                'Content-Length: ' . strlen($payload)
            ]
        );

        if (!$this->body = curl_exec($request)) {
            $this->error = curl_error($request);
        }

        $info = curl_getinfo($request);
        $this->status = isset($info['http_code']) ? $info['http_code'] : null;

        curl_close($request);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}
