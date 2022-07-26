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

class InvoiceComments extends Widget {
    
    public $manager;
    public $order;
            
    public function init(){
        parent::init();
    }
    
    public function run(){
        
        $comment = \common\models\OrdersComments::find()->where(['orders_id' => $this->order->order_id, 'for_invoice' => 1])->one();
        
        return $this->render('invoice-comments', [
            'order' => $this->order,
            'manager' => $this->manager,
            'comment' => $comment->comments ?? '',
            ]);
    }
}
