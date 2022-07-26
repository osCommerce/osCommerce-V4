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

use common\components\GoogleTools;
use frontend\design\Info;
use common\classes\platform;

class GoogleWidget extends \yii\base\Widget {

    public function init() {
        parent::init();
    }

    public function run() {

        if (Info::isAdmin())
            return;
        $provider = GoogleTools::instance()->getModulesProvider();
        $to_work = [];
        $priority = [];
        foreach ($provider->getInstalledModules(platform::currentId()) as $module) {
            $_pages = $module->getAvailablePages();
            if (in_array('checkout', $_pages) && strtolower(str_replace("-", "", \Yii::$app->controller->id)) == 'checkout') {
                if (\Yii::$app->controller->action->id == 'success') {
                    $priority[$module->code] = $module->getPriority();
                    $to_work[$module->code] = $module;
                }
            } else if (in_array(strtolower(str_replace("-", "", \Yii::$app->controller->id)), $_pages) || in_array('all', $_pages)) {
                $priority[$module->code] = $module->getPriority();
                $to_work[$module->code] = $module;
            }
        }

        if (is_array($priority)) {
            asort($priority);
            foreach ($priority as $module => $value) {
                echo $to_work[$module]->renderWidget();
            }
        }
    }

}
