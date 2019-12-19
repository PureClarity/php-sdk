<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Resource;

/**
 * Class Regions
 */
class Regions
{
    /**
     * Default PureClarity regions
     *
     * @var array[]
     */
    private static $regions = [
        1 => [
            'label' => 'Europe',
            'name' => 'eu-west-1',
            'endpoints' => [
                'api' => 'https://api-eu-w-1.pureclarity.net',
                'sftp' => 'https://sftp-eu-w-1.pureclarity.net',
            ]
        ],
        4 => [
            'label' => 'USA',
            'name' => 'us-east-1',
            'endpoints' => [
                'api' => 'https://api-us-e-1.pureclarity.net',
                'sftp' => 'https://sftp-us-e-1.pureclarity.net',
            ]
        ],
    ];

    /**
     * Gets array of valid regions for use in a dropdown
     *
     * @return array[]
     */
    public static function getRegionLabels()
    {
        $regions = [];
        foreach (self::$regions as $value => $info) {
            $regions[$value] = [
                'value' => $value,
                'label' => $info['label']
            ];
        }

        return $regions;
    }

    /**
     * Gets array of valid regions for use in a dropdown
     *
     * @param string $region
     * @return array|false|mixed|string|null
     */
    public static function getRegionName($region)
    {
        $localRegion = getenv('PURECLARITY_REGION');

        if ($localRegion) {
            $regionName = $localRegion;
        } else {
            $regionName = isset(self::$regions[$region]) ? self::$regions[$region]['name'] : null;
        }

        return $regionName;
    }

    /**
     * Gets array of valid regions for use in a dropdown
     *
     * @param string $region
     * @return array|false|mixed|string|null
     */
    public static function getRegion($region)
    {
        return isset(self::$regions[$region]) ? self::$regions[$region] : null;
    }

    /**
     * Gets array of valid regions for use in a dropdown
     *
     * @return mixed|null
     */
    public static function isValidRegion($region)
    {
        return isset(self::$regions[$region]);
    }
}
