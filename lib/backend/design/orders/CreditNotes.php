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

class CreditNotes extends Widget {
    
    public $orders_id;
    public $manager;
    public $data;    
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        if ($this->orders_id){
            $splitter = $this->manager->getOrderSplitter();
            $rma = $splitter->getInstancesFromSplinters($this->orders_id, $splitter::STATUS_RETURNING);
            if ($rma){
                $rma = array_pop($rma);
                //if ($this->data['isAjax']){
                    $rmaTotal = $splitter->getDue($rma->totals);
                    $tManager = $this->manager->getTransactionManager();

                    $preffered = [];
                    $pTransaction = null;
                    foreach($tManager->getTransactions(true) as $transaction){
                        $allowedAmount = $transaction->transaction_amount;
                        if ($transaction->transactionChildren){
                            foreach($transaction->transactionChildren as $child){
                                $allowedAmount -= $child->transaction_amount;
                            }
                        }
                        $transaction->transaction_amount = $allowedAmount;
                        
                        
                        $transaction->currency_id = 0;
                        $curr = \common\models\Currencies::find()->select(['currencies_id'])->where(['code' => $transaction->transaction_currency])->one();
                        if (is_object($curr)) {
                            $transaction->currency_id = $curr->currencies_id;
                        }

                        if (abs($allowedAmount) == abs($rmaTotal)){
                            $pTransaction = $transaction;
                        } else {
                            array_push($preffered, $transaction);
                        }
                    }
                    
                    \yii\helpers\ArrayHelper::multisort($preffered, 'transaction_amount');
                    if (!is_null($pTransaction)){
                        array_unshift($preffered, $pTransaction);
                    }
                    
                //}
                
                  
                /*echo "<pre>";
                print_r($this->manager->get('currency'));
                echo "</pre>";
                die();*/
                $url = Yii::$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $this->orders_id]);
                return $this->render('credit-notes', [
                    'rma' => $rma,
                    'preffered' => $preffered,
                    'manager' => $this->manager,
                    'url' => $url
                ]);
            }
        }
    }
}
