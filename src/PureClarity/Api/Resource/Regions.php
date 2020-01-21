<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Resource;

/**
 * Class Regions
 *
 * Handles Data related to PureClarity Regions
 */
class Regions
{
    /**
     * Default PureClarity Regions
     *
     * @var array[]
     */
    private $regions = [
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
        99 => [
            'label' => 'UK',
            'name' => 'uk',
            'endpoints' => [
                'api' => 'https://api-eu-w-1.pureclarity.net',
                'sftp' => 'https://sftp-eu-w-1.pureclarity.net',
            ]
        ],
    ];

    /**
     * Gets array of valid regions for use in a dropdown
     *
     * @return array[]
     */
    public function getRegionLabels()
    {
        $regions = [];
        foreach ($this->regions as $value => $info) {
            $regions[$value] = [
                'value' => $value,
                'label' => $info['label']
            ];
        }

        return $regions;
    }

    /**
     * Gets the name for the provided region
     *
     * @param integer $region
     * @return string
     */
    public function getRegionName($region)
    {
        $localRegion = getenv('PURECLARITY_REGION');

        if ($localRegion) {
            $regionName = $localRegion;
        } else {
            $regionName = isset($this->regions[$region]) ? $this->regions[$region]['name'] : null;
        }

        return $regionName;
    }

    /**
     * Gets array of info related to the provided region
     *
     * @param integer $region
     * @return array|false|mixed|string|null
     */
    public function getRegion($region)
    {
        return isset($this->regions[$region]) ? $this->regions[$region] : null;
    }

    /**
     * Simple check to see if a region ID exists
     *
     * @param integer $region
     *
     * @return mixed|null
     */
    public function isValidRegion($region)
    {
        return isset($this->regions[$region]);
    }
}
