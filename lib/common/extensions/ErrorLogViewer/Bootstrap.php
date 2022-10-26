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

namespace common\extensions\ErrorLogViewer;

use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface {

    /**
     * @param Application $app
     */
    public function bootstrap($app) {
        if (!ErrorLogViewer::enabled()) return;
        \Yii::setAlias('@ext-error-log-viewer', dirname(__FILE__));
        if ($app instanceof \yii\web\Application) {
            if ($app->id == 'app-backend') {
                $app->controllerMap = array_merge($app->controllerMap, [
                    'error-log-viewer' => ['class' => __NAMESPACE__ . '\backend\controllers\ErrorLogViewerController'],
                ]);
            }
        }
    }

}