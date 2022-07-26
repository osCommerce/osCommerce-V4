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

namespace common\components\google\widgets;

use Yii;
use common\components\GoogleTools;
use frontend\design\Info;

class GoogleVerification {

    public static function verify() {

        $module = GoogleTools::instance()->getModulesProvider()->getActiveByCode('verification', \common\classes\platform::currentId());
        if ($module) {
            Yii::$app->controller->getView()->registerMetaTag([
                'name' => 'google-site-verification',
                'content' => $module->config[$module->code]['fields'][0]['value'],
                    ], 'google-site-verification');
        }
    }

}
