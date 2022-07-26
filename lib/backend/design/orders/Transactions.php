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
/**
 * @prop
 */
class Transactions extends Widget {
    
    public $manager;
    public $orders_id;
    public $data;//[type - view, parent - transaction_id]
            
    public function init(){
        parent::init();
        if (!$this->data){
            $this->data['type'] = 'full';
        }
    }
    
    public function run(){
        $currencies = Yii::$container->get('currencies');
        if ($this->manager->isInstance() ) {
          $order = $this->manager->getOrderInstance();
        }
        $url = Yii::$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $this->orders_id, 'platform_id' => ($order->info['platform_id']??0)]);
        if ($this->data['type'] == 'full'){
            $transactions = \common\models\OrdersTransactions::find()
                    ->where(['orders_id' => $this->orders_id])->orderBy('date_created asc')->all();
            
            return $this->render('transactions', [
                'transactions' => $transactions,
                'manager' => $this->manager,
                'currencies' => $currencies,
                'url' => $url,
                'cnurl' => $url = Yii::$app->urlManager->createUrl(['orders/credit-notes']),
                'orders_id' => $this->orders_id,
                'orderprocessUrl'=> Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $this->orders_id]),
            ]);
        } else if ($this->data['type'] == 'children'){
            $this->data = array_pop($this->data);
            $transaction = \common\models\OrdersTransactions::find()->alias('ot')
                    ->where(['orders_id' => $this->orders_id, 'ot.orders_transactions_id' => $this->data['parent']])
                    ->joinWith('transactionChildren')->orderBy('date_created desc')->one();
            
            return json_encode([
                'children' => $this->render('transactions-chldren', [
                    'transaction' => $transaction,
                    'manager' => $this->manager,
                    'currencies' => $currencies,
                    'url' => $url,
                    ]),
                'actions' => $this->render('transactions-actions', ['transaction' => $transaction, 'url' => $url, 'data'=> $this->data ]),
            ]);
        }
        
    }
}
