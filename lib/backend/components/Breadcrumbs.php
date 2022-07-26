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

namespace backend\components;

use yii\base\Widget;

class Breadcrumbs extends Widget {

    public $navigation = array();
		public $topButtons = array();
    
    public function run() {
        if (isset(\Yii::$app->controller->navigation)) {
            $this->navigation = \Yii::$app->controller->navigation;
        }
        if (isset(\Yii::$app->controller->topButtons)) {
						$this->topButtons = \Yii::$app->controller->topButtons;
        }
        return $this->render('Breadcrumbs', [
          'context' => $this,
        ]);
    }

}

