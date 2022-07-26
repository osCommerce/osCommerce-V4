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

class GiveAway extends Widget {
    
    public $manager;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        
        $cart = $this->manager->getCart();
        $currencies = Yii::$container->get('currencies');
        $currency_value = $currencies->currencies[$cart->currency]['value'];
        
        $products = \common\helpers\Gifts::getGiveAways();
        
        return $this->render('give-away',[
            'products' => $products,
            'queryParams' => array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams())
        ]);
    }
    
}
