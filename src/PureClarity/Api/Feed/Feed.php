<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Feed;

use Exception;

/**
 * Class Feed
 *
 * Feed Base class - handles building & sending of a Feed
 */
abstract class Feed
{
    /** @var string */
    const FEED_TYPE_BRAND = 'brand';

    /** @var string */
    const FEED_TYPE_CATEGORY = 'category';

    /** @var string */
    const FEED_TYPE_PRODUCT = 'product';

    /** @var string */
    const FEED_TYPE_ORDER = 'orders';

    /** @var string */
    const FEED_TYPE_USER = 'user';

    /** @var int $dataIndex */
    protected $dataIndex = 0;

    /** @var string $feedType */
    protected $feedType;

    /** @var string $feedStart */
    protected $feedStart = '';

    /** @var string $feedEnd */
    protected $feedEnd = '';

    /** @var string[] $requiredFields - Fields that must be present in the data (regardless of content) */
    protected $requiredFields = [];

    /** @var string[] $nonEmptyFields - Fields that must contain data */
    protected $nonEmptyFields = [];

    /** @var string $pageData - Feed Data */
    protected $pageData = '';

    /** @var integer $pageSize - Feed Handler class */
    protected $pageSize = 50;
    
    /** @var Transfer $transfer - Feed Sending Handler class */
    private $transfer;

    /**
     * Feed constructor.
     *
     * @param string $accessKey
     * @param string $secretKey
     * @param string $region
     */
    public function __construct($accessKey, $secretKey, $region)
    {
        $this->transfer = new Transfer($this->feedType, $accessKey, $secretKey, $region);
    }

    /**
     * Set the page size to a non-default value
     *
     * @param integer $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int)$pageSize;
    }

    /**
     * Calls the create method on the feed transfer object to initialize the feed process
     *
     * @throws Exception
     */
    public function start()
    {
        $this->transfer->create($this->feedStart);
    }

    /**
     * Appends the provided data to the feed.
     *
     * If we hit the page limit, then calls the append method on the feed transfer object to send the page of data
     *
     * @param mixed[] $data
     * @throws Exception
     */
    public function append($data)
    {
        $errors = $this->validate($data);
        if (empty($errors) === false) {
            throw new Exception(implode('|', $errors));
        }

        $this->dataIndex++;
        $this->pageData .= $this->processData($data);

        if (($this->dataIndex % $this->pageSize) === 0) {
            $this->transfer->append($this->pageData);
            $this->pageData = '';
        }
    }

    /**
     * Formats the data to add to the feed
     *
     * @param mixed[] $data
     * @return false|string
     */
    protected function processData($data)
    {
        $data['_index'] = $this->dataIndex;

        if ($this->dataIndex >= 2) {
            $this->pageData .= ',';
        }

        return json_encode($data);
    }

    /**
     * Validates the provided feed data
     * @param mixed[] $data
     * @return array
     */
    protected function validate($data)
    {
        $errors = [];
        foreach ($this->requiredFields as $field) {
            if (!isset($data[$field])) {
                $errors[] = 'Missing ' . $field;
            }
        }
        foreach ($this->nonEmptyFields as $field) {
            if (isset($data[$field]) && empty($data[$field])) {
                $errors[] = 'Missing data for ' . $field;
            }
        }

        return $errors;
    }

    /**
     * Calls the close method on the feed transfer object to end the feed process
     *
     * Sends any unset page data if necessary
     *
     * @throws Exception
     */
    public function end()
    {
        if ($this->pageData !== '') {
            $this->transfer->append($this->pageData);
            $this->pageData = '';
        }

        $this->transfer->close($this->feedEnd);
    }
}
