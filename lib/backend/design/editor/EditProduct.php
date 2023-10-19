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

namespace backend\design\editor;

use Yii;
use yii\base\Widget;
use common\models\Products;

class EditProduct extends Widget {

    public $manager;
    public $uprid;

    public function init() {
        parent::init();
    }
    
    public function run() {

        $insulator = new \backend\services\ProductInsulatorService($this->uprid, $this->manager);
        $insulator->edit = true;
        $productDetails = $insulator->getProductMainDetails();
        return $this->render('edit-product',[
            'manager' => $this->manager,
            'product' => $productDetails,
            'rates' => $this->manager->getOrderTaxRates(),
            'queryParams' => array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams()),
            'currentUrl' => Yii::$app->request->url
        ]);
    }

}
