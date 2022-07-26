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

class ExtraCharge extends Widget {

    public $manager;
    public $product;

    public function init(){
        parent::init();
        if (!$this->product) throw new \Exception('Product is not defined');
    }

    public function run(){

        $predefined = null;
        if (isset($this->product['overwritten']) && !empty($this->product['overwritten'])){
            $predefined = $this->product['overwritten']['final_price_formula_data'][1]['vars'] ?? null;
        }

        if (!$predefined){
            $predefined = [
                'percent_action' => '-',
                'percent_value' => 0,
                'fixed_action' => '-',
                'fixed_value' => 0,
            ];
        } else {
            if (isset($predefined['init_value'])) {
                $predefined['init_value'] *= \common\helpers\Product::getVirtualItemQuantityValue($this->product['id']);
            }
            if (isset($predefined['fixed_value'])) {
                $predefined['fixed_value'] *= \common\helpers\Product::getVirtualItemQuantityValue($this->product['id']);
            }
        }

        return $this->render('extra-charge', [
            'product' => $this->product,
            'manager' => $this->manager,
            'predefined' => $predefined,
        ]);
    }

}
