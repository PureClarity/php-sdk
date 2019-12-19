<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Delta;

use Exception;
use PureClarity\Api\Resource\Endpoints;
use PureClarity\Api\Transfer\Curl;

class Base
{
    /** @var string $accessKey */
    private $accessKey;

    /** @var string $secretKey */
    private $secretKey;

    /** @var string $region */
    private $region;

    /** @var string $dataKey */
    protected $dataKey;

    /** @var string $deleteKey */
    protected $deleteKey;

    /** @var mixed[] $data */
    private $data;

    /** @var mixed[] $delete */
    private $delete;

    public function __construct($accessKey, $secretKey, $region)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
    }

    /**
     * @param string $delete
     */
    public function addDelete($delete)
    {
        $this->delete[] = $delete;
    }

    /**
     * @param $data
     */
    public function addData($data)
    {
        $this->data[] = $data;
    }

    /**
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
     * @throws Exception
     */
    public function send()
    {
        $this->sendDeletes();
        $this->sendData();
    }

    /**
     * @throws Exception
     */
    private function sendDeletes()
    {
        $request = $this->buildRequest();
        $request[$this->deleteKey] = $this->delete;
        $body = json_encode($request);
        $this->sendDelta($body);
    }

    /**
     * @throws Exception
     */
    private function sendData()
    {
        $requestBase = $this->buildRequest();

        $chunks = array_chunk($this->data, 10);
        foreach ($chunks as $data) {
            $request = $requestBase;
            $request[$this->dataKey][] = $data;
            $body = json_encode($request);
            $this->sendDelta($body);
        }
    }

    /**
     * @param string $body
     * @return array
     * @throws Exception
     */
    private function sendDelta($body)
    {
        $url = $this->getDeltaEndpoint($this->region);
        $curl = new Curl();
        $curl->setDataType('application/json');
        $curl->post($url, $body);

        var_dump($url, $body);

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
