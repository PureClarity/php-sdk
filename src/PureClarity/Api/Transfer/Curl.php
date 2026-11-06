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
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FAILONERROR => false,
        CURLOPT_POST => true
    ];

    /** @var string|null $fallbackCaBundle - CA bundle file to use when PHP has no CA store configured */
    private static $fallbackCaBundle;

    /**
     * Sets a CA bundle file to verify SSL certificates with when PHP has no CA store configured,
     * so requests still work on hosts that are missing one.
     *
     * @param string $path - path to a PEM CA bundle file
     */
    public static function setFallbackCaBundle($path)
    {
        self::$fallbackCaBundle = $path;
    }

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

        if (self::$fallbackCaBundle && is_file(self::$fallbackCaBundle) && !$this->hasCaStore()) {
            $request->setopt(CURLOPT_CAINFO, self::$fallbackCaBundle);
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
     * Checks whether PHP has a CA store for curl to verify SSL certificates with: either the
     * curl.cainfo setting, or OpenSSL's default certificate file or directory.
     * The checks are silenced because open_basedir can forbid them; that counts as no store.
     *
     * @return bool
     */
    private function hasCaStore()
    {
        $caInfo = ini_get('curl.cainfo');
        if ($caInfo && @is_file($caInfo)) {
            return true;
        }

        if (!function_exists('openssl_get_cert_locations')) {
            return false;
        }

        $locations = openssl_get_cert_locations();

        return @is_file($locations['default_cert_file']) || @is_dir($locations['default_cert_dir']);
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
