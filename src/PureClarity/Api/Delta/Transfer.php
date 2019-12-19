<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Delta;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

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

    protected $problemFeeds = array();

    public function __construct($feedType, $accessKey, $secretKey, $region)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
        $this->setFeedBase();
    }

    /**
     * @return array
     */
    public function setDeltaBase()
    {
        $this->delta = [
            'AppKey'            => $this->accessKey,
            'Secret'            => $this->secretKey,
            'Format'            => 'magentoplugin1.0.0'
        ];
    }

    /**
     * Starts the feed by sending first bit of data to feed-create end point. For orders,
     * sends first row of CSV data, otherwise sends opening string of json.
     * @param $data
     * @throws Exception
     */
    public function create($data)
    {
        $this->send('feed-create', $data);
    }

    /**
     * End the feed by sending any closing data to the feed-close end point. For order feeds,
     * no closing data is sent, the end point is simply called. For others, it's simply a closing
     * bracket.
     * @param $data string character to close feed with
     * @throws Exception
     */
    public function close($data)
    {
        $this->send('feed-close', $data);
    }

    /**
     * @param $data
     * @throws Exception
     */
    public function append($data)
    {
        $this->send('feed-append', $data);
    }

    /**
     * Returns parameters ready for POSTing. A unique id is added to the feed type
     * so that each feed request is always treated uniquely on the server. For example,
     * you could have two people initialising feeds at the same time, which would otherwise
     * cause overlapping, corrupted data.
     * @param $data string
     * @return array
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
     * @param string $data
     * @return array
     * @throws Exception
     */
    private function send($data)
    {
        $request = $this->buildRequest($data);
        $url = $this->getDeltaEndpoint($this->region);
        $request = http_build_query($request);

        echo "<pre>";
        var_dump($url, $data);
        echo "</pre>";

        $curl = new Curl();
        $curl->setDataType('application/x-www-form-urlencoded');
        $curl->post($url, $request);

        $error = $curl->getError();

        if (empty($error) === false) {
            throw new Exception($curl->getError());
        }

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody()
        ];
    }

    /**
     * @param string $region
     * @return string
     * @throws Exception
     */
    private function getDeltaEndpoint($region)
    {
        return Endpoints::getDeltaEndpoint($region);
    }
}
