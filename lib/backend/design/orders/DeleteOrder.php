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

class DeleteOrder extends Widget {
    
    public $order;    
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        if (\common\helpers\Order::is_stock_updated(intval($this->order->order_id))) {
            $restock_disabled = '';
            $restock_selected = ' checked ';
        } else {
            $restock_disabled = ' disabled="disabled" readonly="readonly" ';
            $restock_selected = '';
        }
        return $this->render('delete-order', [
            'order' => $this->order,
            'restock_selected' => $restock_selected,
            'restock_disabled' => $restock_disabled,
        ]);
    }
}
