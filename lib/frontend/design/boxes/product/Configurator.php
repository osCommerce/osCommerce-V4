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

namespace frontend\design\boxes\product;

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Configurator extends Widget {

    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        $params = Yii::$app->request->get();

        if ($params['products_id'] && Yii::$app->controller instanceof \frontend\controllers\CatalogController ) {
          $action = Yii::$app->controller->createAction('product-configurator');
          return $action->runWithParams($params);
          //return Yii::$app->runAction('catalog/product-configurator', $params);
        } else {
          return '';
        }
    }

}
