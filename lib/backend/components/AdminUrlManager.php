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

namespace app\components;

use yii\web\UrlManager;
use Yii;

class AdminUrlManager extends UrlManager
{

    public function createAbsoluteUrl($params, $scheme = null, $front = false) {
        if ($front && !empty($params['platform_id']) /*&& PLATFORM_ID != $params['platform_id']*/) {
            // save current params
            $HostInfo = $this->getHostInfo();
            $BaseUrl = $this->getBaseUrl();

            $pc = new \common\classes\platform_config($params['platform_id']);
            $parsed = parse_url($pc->getCatalogBaseUrl(true, false));

            $this->setHostInfo($parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:''));
            $this->setBaseUrl(rtrim($parsed['path']));

    // restore params
            $ret = parent::createAbsoluteUrl($params, $scheme);
            $this->setHostInfo($HostInfo);
            $this->setBaseUrl($BaseUrl);
        } else {
            $ret = parent::createAbsoluteUrl($params, $scheme);
        }
        return $ret;
    }

}
