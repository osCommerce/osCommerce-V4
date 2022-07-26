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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;

class Downloads extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        $dQuery = \common\models\OrdersProductsDownload::find()->where(['orders_id' => $this->order->order_id]);
        if ($dQuery->exists()){
            return $this->render('downloads', [
                'data' => $dQuery->all()
            ]);
        }
    }
}
