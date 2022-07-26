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

class OrderStatuses extends Widget {
    
    public $manager;
    public $admin;
        
    public function init(){
        parent::init();
    }
    
    public function run(){
        $cart = $this->manager->getCart();
        if ($cart->order_id){
            return $this->render('order-statuses',[
                'url' => Yii::$app->urlManager->createAbsoluteUrl(array_merge(['editor/checkout', 'action'=> 'show_statuses'], Yii::$app->request->getQueryParams())),
            ]);
        }
    }
}
