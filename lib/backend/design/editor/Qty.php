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

class Qty extends Widget {

    public $manager;
    public $product;
    public $isPack = false;
    public $max;
    public $min = "data-min='1'";
    public $step = "data-step='1'";

    public function init(){
        parent::init();
    }

    public function run(){

        $this->max = $this->product['stock_info']['max_qty'] + $this->product['reserved_qty'];

        if (!isset($this->product['stock_limits'])) {
            $this->product['stock_limits'] = \common\helpers\Product::get_product_order_quantity($this->product['id']);
        }

        if (\common\helpers\Acl::checkExtensionAllowed('MinimumOrderQty', 'allowed')){
            $this->min = \common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($this->product['stock_limits']);
        }

        if ($oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')) {
            $this->step = $oqs::setLimit($this->product['stock_limits']);
        }
        if ($this->isPack){
            $insulator = new \backend\services\ProductInsulatorService( $this->product['id'], $this->manager);
            $_product = $insulator->getProduct();
            if ($_product){
                $this->product['data'] = $_product->getAttributes();
                $m = [
                    $this->max,
                    floor($this->product['data']['pack_unit'] ? $this->max/$this->product['data']['pack_unit']  :0 ),
                    floor($this->product['data']['packaging'] ? $this->max/$this->product['data']['packaging']  :0 ),
                ];
                $this->max = $m;
            }
            $this->min = "data-min='0'";//thais is becuase very cool extension!!!
        }

        return $this->render('qty', [
            'product' => $this->product,
            'isPack' => $this->isPack,
            'max' => $this->max,
            'min' => $this->min,
            'step' => $this->step,
        ]);
    }

}

