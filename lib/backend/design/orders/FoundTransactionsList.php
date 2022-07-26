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

class FoundTransactionsList extends Widget {
    
    public $manager;
    public $transactions;
    public $payment;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
                
        return $this->render('found-transactions-list',[
            'transactions' => $this->transactions,
            'payment' => $this->payment,
            'url' => Yii::$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $this->manager->getOrderInstance()->order_id])
        ]);
    }
}
