<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\models\repositories;


use common\extensions\PlatformSoapServer\models\PlatformsApi;

class PlatformsApiRepository
{

    /**
     * @param $apiKey
     * @return bool|PlatformsApi
     */
    public static function findPlatformApiByKey($apiKey)
    {
        $apiKey = trim($apiKey);
        if ( !empty($apiKey) ) {
            return PlatformsApi::findOne(['api_key' => $apiKey]);
        }
        return false;
    }

    public static function findPlatformByApiKey($apiKey)
    {
        if ($platformApi = static::findPlatformApiByKey($apiKey)){
            return $platformApi->platform;
        }
        return false;
    }

}