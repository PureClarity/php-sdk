<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Resource;

use Exception;

/**
 * Class Endpoints
 */
class Endpoints
{
    /**
     * Default PureClarity ClientScript URL
     *
     * @var string
     */
    private static $scriptUrl = '//pcs.pureclarity.net';

    /**
     * Gets the PureClarity delta endpoint for the given store
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public static function getDeltaEndpoint($region)
    {
        return self::getApiUrl($region) . '/api/productdelta';
    }

    /**
     * Gets the PureClarity signup request endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public static function getSignupRequestEndpoint($region)
    {
        return self::getApiUrl($region) . '/api/plugin/signuprequest';
    }

    /**
     * Gets the PureClarity signup tracking endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public static function getSignupStatusEndpoint($region)
    {
        return self::getApiUrl($region) . '/api/plugin/signupstatus';
    }

    /**
     * Gets the PureClarity clientscript URL
     *
     * @param string $accessKey
     *
     * @return string
     */
    public static function getClientScriptUrl($accessKey)
    {
        $pureclarityScriptUrl = getenv('PURECLARITY_SCRIPT_URL');
        if ($pureclarityScriptUrl !== null && $pureclarityScriptUrl !== '') {
            $pureclarityScriptUrl .= $accessKey . '/dev.js';
            return $pureclarityScriptUrl;
        } else {
            $pureclarityScriptUrl = self::$scriptUrl . '/' . $accessKey . '/cs.js';
        }

        return $pureclarityScriptUrl;
    }

    /**
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public static function getSftpEndpoint($region)
    {
        $regionData = Regions::getRegion($region);
        if (!$regionData) {
            throw new Exception('Invalid Region supplied');
        }

        $url = getenv('PURECLARITY_FEED_HOST');
        if (empty($url)) {
            $url = $regionData['endpoints']['sftp'];
        }

        $port = getenv('PURECLARITY_FEED_PORT');
        if (!empty($port)) {
            $url = $url . ':' . $port;
        }

        return $url . '/';
    }

    /**
     * @param $region
     * @return array|false|string
     * @throws Exception
     */
    private static function getApiUrl($region)
    {
        $regionData = Regions::getRegion($region);
        if (!$regionData) {
            throw new Exception('Invalid Region supplied');
        }

        $host = getenv('PURECLARITY_HOST');
        if ($host != null && $host != '') {
            $parsed = parse_url($host);
            if (empty($parsed['scheme'])) {
                $host = 'http://' . $host;
            }
        } else {
            $host = $regionData['endpoints']['api'];
        }

        return $host;
    }
}
