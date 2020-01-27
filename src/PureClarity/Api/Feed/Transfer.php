<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

/**
 * Class Transfer
 *
 * Handles sending the  start / append / close of feed data
 */
class Transfer
{
    /** @var string $feedType */
    private $feedType;

    /** @var string $accessKey */
    private $accessKey;

    /** @var string $secretKey */
    private $secretKey;

    /** @var string $region */
    private $region;

    /** @var string $feedId */
    private $feedId;

    /**
     * @param string $feedType - Feed Type, used in naming of the feed file
     * @param string $accessKey - Application Access Key
     * @param string $secretKey - Application Secret Key
     * @param integer $region - PureClarity Region ID
     * @param string $feedId - Optional Feed ID, used in naming of the feed file
     */
    public function __construct($feedType, $accessKey, $secretKey, $region, $feedId = '')
    {
        $this->feedType  = $feedType;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
        $this->feedId    = $feedId ?: uniqid();
    }

    /**
     * Returns the current Feed ID
     *
     * @return string
     */
    private function getFeedId()
    {
        return $this->feedId;
    }

    /**
     * Starts the feed by sending first bit of data to feed-create end point. For orders,
     * sends first row of CSV data, otherwise sends opening string of json.
     *
     * @param string $data
     * @return mixed[]
     * @throws Exception
     */
    public function create($data)
    {
        return $this->send('feed-create', $data);
    }

    /**
     * End the feed by sending any closing data to the feed-close end point. For order feeds,
     * no closing data is sent, the end point is simply called. For others, it's simply a closing
     * bracket.
     *
     * @param string $data - character to close feed with
     * @return mixed[]
     * @throws Exception
     */
    public function close($data)
    {
        return $this->send('feed-close', $data);
    }

    /**
     * @param string $data
     * @return mixed[]
     * @throws Exception
     */
    public function append($data)
    {
        return $this->send('feed-append', $data);
    }

    /**
     * Returns parameters ready for POSTing. A unique id is added to the feed type
     * so that each feed request is always treated uniquely on the server. For example,
     * you could have two people initialising feeds at the same time, which would otherwise
     * cause overlapping, corrupted data.
     *
     * @param string $data
     * @return mixed[]
     */
    private function buildRequest($data)
    {
        $parameters = array(
            'accessKey' => $this->accessKey,
            'secretKey' => $this->secretKey,
            'feedName' => $this->feedType . '-' . $this->getFeedId()
        );

        if (! empty($data)) {
            $parameters['payLoad'] = $data;
        }

        $parameters['php'] = phpversion();

        return $parameters;
    }

    /**
     * Sends the provided Data to the PureClarity SFTP endpoint
     *
     * @param string $endPoint
     * @param string $data
     *
     * @return mixed[]
     * @throws Exception
     */
    private function send($endPoint, $data)
    {
        $request = $this->buildRequest($data);
        $url = $this->getSftpEndpoint($this->region) . $endPoint;
        $request = http_build_query($request);

        $curl = new Curl();
        $curl->setDataType('application/x-www-form-urlencoded');
        $curl->post($url, $request);

        $status = $curl->getStatus();
        $error = $curl->getError();
        $body = $curl->getBody();

        if ($status !== 200) {
            throw new Exception(
                'Error: HTTP ' . $status . ' Response | ' .
                'Message: ' . $error . ' | ' .
                'Body: ' . $body . ' | '
            );
        }

        if (empty($error) === false) {
            throw new Exception($error);
        }

        return [
            'status' => $status,
            'body' => $body
        ];
    }

    /**
     * Gets the PureClarity SFTP endpoint for the region provided
     *
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getSftpEndpoint($region)
    {
        $endpoints = new Endpoints();
        return $endpoints->getSftpEndpoint($region);
    }
}
