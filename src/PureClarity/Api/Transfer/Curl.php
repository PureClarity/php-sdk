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

    /** @var int[] - curl errors for a failed certificate check: no trusted CA for the certificate, or no readable CA file */
    const CERT_VERIFY_ERRORS = [60, 77];

    /** @var string|null $fallbackCaBundle - CA bundle file to retry with when certificate verification fails */
    private static $fallbackCaBundle;

    /**
     * Sets a CA bundle file to retry a request with when certificate verification fails, such as on
     * hosts where PHP has no CA store configured. Without one, Composer's bundle is used when available.
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
        $errno = $this->send($url, $payload, $options);

        // PHP's CA store couldn't verify the certificate, or PHP has none: retry once with the fallback.
        // The request body was never sent, because the connection failed during the TLS handshake.
        if (in_array($errno, self::CERT_VERIFY_ERRORS, true)) {
            $fallbackOptions = $this->getFallbackCaOptions();
            if ($fallbackOptions) {
                $this->send($url, $payload, $fallbackOptions + $options);
            }
        }
    }

    /**
     * Sends the request and records its response
     *
     * @param string $url - URL to post to
     * @param string $payload - Data to send
     * @param array $options - options to set (can be used to override defaults)
     * @return int - the curl error number, 0 if the request succeeded
     */
    private function send($url, $payload, $options)
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

        $errno = 0;
        $this->error = '';
        if (!$this->body = $request->exec()) {
            $this->error = $request->error();
            $errno = $request->errno();
        }

        $info = $request->getinfo();
        $this->status = isset($info['http_code']) ? $info['http_code'] : null;

        $request->close();

        return $errno;
    }

    /**
     * Gets the curl options to retry a request with after certificate verification failed
     *
     * @return mixed[] - empty when there is nothing to retry with
     */
    private function getFallbackCaOptions()
    {
        $options = [];

        $caBundle = self::$fallbackCaBundle;
        if (!$caBundle && class_exists('Composer\CaBundle\CaBundle')) {
            // Composer installs, such as Magento: use the Mozilla CA bundle that ships with composer/ca-bundle
            $caBundle = \Composer\CaBundle\CaBundle::getBundledCaBundlePath();
        }

        // Silenced because open_basedir can forbid the check
        if ($caBundle && @is_file($caBundle)) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        // Windows keeps trusted CAs in its own certificate store, which PHP's curl doesn't read by default.
        // Trust it as well, so CAs installed there, such as a company's TLS proxy CA, work (PHP 8.2+, libcurl 7.71+).
        if (DIRECTORY_SEPARATOR === '\\' && defined('CURLSSLOPT_NATIVE_CA')) {
            $options[CURLOPT_SSL_OPTIONS] = CURLSSLOPT_NATIVE_CA;
        }

        return $options;
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
