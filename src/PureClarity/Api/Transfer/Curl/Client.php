<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Transfer\Curl;

/**
 * Class Client
 *
 * Just a wrapper around curl_* functions
 *
 * @codeCoverageIgnore
 */
class Client
{
    /** @var  */
    private $request;

    /**
     * Initializes the CURL request using curl_init
     */
    public function init()
    {
        $this->request = curl_init();
    }

    /**
     * Sets options on the CURL request using curl_setopt
     *
     * @param integer $optionKey - CURL_ option constant
     * @param mixed $optValue - value to set on the CURL request
     */
    public function setopt($optionKey, $optValue)
    {
        curl_setopt($this->request, $optionKey, $optValue);
    }

    /**
     * Executes the CURL request using curl_exec and returns the response
     *
     * @return bool|string
     */
    public function exec()
    {
        return curl_exec($this->request);
    }

    /**
     * Gets any errors  string for the curl request using curl_error
     * @return string
     */
    public function error()
    {
        return curl_error($this->request);
    }

    /**
     * Gets the info about the CURL request using curl_info
     *
     * @return mixed
     */
    public function getinfo()
    {
        return curl_getinfo($this->request);
    }

    /**
     *  Closes the CURL request using curl_close
     */
    public function close()
    {
        curl_close($this->request);
    }
}
