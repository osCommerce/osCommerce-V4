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

namespace backend\models\EP\Provider\PaymentBots;

use Yii;
use backend\models\EP\Messages;
use backend\models\EP\Provider\DatasourceInterface;
use backend\models\EP\Exception;
use backend\models\EP\Tools;
use yii\db\Query;


class PaypalCollector implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $job_task;
    protected $config = [];
    protected $check_order_ids = [];
    protected $startJobServerGmtTime = '';
    protected $useModifyTimeCheck = true;
    protected $isErrorOccurredDuringCheck = false;
    private $transactionManager;
    private $orderManager;
    private $begining;
    
    public function __construct($config) {        
        $this->config = $config;        
    }

    public function allowRunInPopup() {
        return false;
    }

    public function getProgress() {
        if ($this->total_count > 0) {
            $percentDone = min(100, ($this->row_count / $this->total_count) * 100);
        } else {
            $percentDone = 100;
        }
        return number_format($percentDone, 1, '.', '');
    }

    public function prepareProcess(Messages $message) {

        $migrate = new \common\classes\Migration();
        if (!$migrate->isTableExists('paypal_cron')){
            $migrate->createTable('paypal_cron', [
                'id' => \yii\db\Schema::TYPE_PK,
                'platform_id' => $migrate->integer(),
                'payment_class' => $migrate->string(64),
                'start_date' => $migrate->dateTime(),
                'end_date' => $migrate->dateTime(),
            ], '');
        }
        
        $this->orderManager = new \common\services\OrderManager(Yii::$app->get('storage'));
        
        if (is_array($this->config['payments'])){
            foreach ($this->config['payments'] as $platform => $modules){
                if (is_array($modules)){
                    foreach($modules as $module){
                        $this->job_task = $this->getJob($module, $platform, $message);
                        if ($this->job_task) break 2;
                    }
                }
            }
        }
        
        if (!$this->job_task) {
            $message->info('No data');
            $message->progress(100);
            return false;
        }
        
        $this->total_count = 1;

        $this->afterProcessFilename = tempnam($this->config['workingDirectory'], 'after_process');
        $this->afterProcessFile = fopen($this->afterProcessFilename, 'w+');
    }
    
    protected function getInstalledModuels($platform){
        static $cache = [];
        if (!isset($cache[$platform])){
            $installed = (new Query())->select('configuration_value')->from("platforms_configuration")
                    ->where(['configuration_key' => 'MODULE_PAYMENT_INSTALLED', 'platform_id' => $platform])
                    ->one();
            $cache[$platform] = [];
            if ($installed){
                $modules = explode(";", $installed['configuration_value']);
                foreach($modules as $module){
                    $class = pathinfo($module, PATHINFO_FILENAME);
                    if (!in_array($class, $cache[$platform]) && class_exists("\common\modules\orderPayment\\{$class}")){
                        $cache[$platform][] = $class;
                    }
                }
            }
        }
        return $cache[$platform];
    }
    
    protected function getJob($class, $platform, $message){
        $job = false;
        $modules = $this->getInstalledModuels($platform);
        if (in_array($class, $modules)){
            
            $lastJob = (new Query)->select(['*'])->from('paypal_cron')
                        ->where(['payment_class' => $class, 'platform_id' => $platform])
                        ->orderBy('id desc')->one();            
            if (!$lastJob){
                $message->info("{$class} for " . \common\classes\platform::name($platform) . " started");
                $year = $this->config['payments'][$platform]['begining'];
                if (!$year) $year = date("Y", strtotime("-1 year"));
                $this->begining = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, $year));//configure it
                $start = new \DateTime($this->begining);
            } else {
                $start = new \DateTime($lastJob['end_date']);                    
            }
            
            $end = clone $start;
            $end = $end->add(new \DateInterval('P31D'));
            
            $now = new \DateTime();
            $diff = $now->diff($start);
            
            if (!$diff->y && !$diff->m && $diff->d <= 31){
                $end = $now->sub(new \DateInterval('P1D'));
            }
            
            $now = new \DateTime();
            $diff = $now->diff($end);
            if (!$diff->y && !$diff->m && !$diff->d) return false;//check if it is loaded till today
            
            $config = new \common\classes\platform_config($platform);
            $config->constant_up();
            
            $builder = new \common\classes\modules\ModuleBuilder($this->orderManager);            
            $payment = $builder(['class' => "\\common\\modules\\orderPayment\\{$class}"]);
            
            if (is_object($payment) && $payment instanceof \common\classes\modules\TransactionSearchInterface ){
        
                $job = [
                    'payment' => $payment,
                    'start_date' => $start->format(DATE_ATOM),
                    'end_date' => $end->format(DATE_ATOM),
                    'platform' => $platform,
                    'fields' => [
                        'start_date' => $this->config['payments'][$platform][$payment->code]['start_date'],
                        'end_date' => $this->config['payments'][$platform][$payment->code]['end_date'],
                    ]
                ];
            }
        }
        
        return $job;
    }

    public function processRow(Messages $message) {
        set_time_limit(0);

        if (!$this->job_task) return false;
        
        try {
            $this->processTask($this->job_task, $message);
        } catch (\Exception $ex) {
            throw new \Exception('Processing task error ' . $ex->getMessage() . " Trace:" . $ex->getTraceAsString());
        }
        
        return true;
    }

    public function postProcess(Messages $message) {
        return;
    }

    public function processTask(&$task, Messages $message, $useAfterProcess = false) {
        if ($task){
            
            $payment = $task['payment'];
            $params = [];
            if (is_array($task['fields'])){
                if (isset($task['fields']['start_date'])){
                    $params[$task['fields']['start_date']] = $task['start_date'];
                }
                if (isset($task['fields']['end_date'])){
                    $params[$task['fields']['end_date']] = $task['end_date'];
                }
            }
            $params['no_modify'] = true;
            try{
                $defPlId = \common\classes\platform::defaultId();
                $transactions = $payment->search($params);                
                if ($transactions){
                    foreach($transactions as $transaction){
                        $info = $transaction['transaction_info'];
                        $rows = \yii\helpers\ArrayHelper::index(\common\models\PaypalipnTxn::findAll(['txn_id' => $info['transaction_id']]), 'platform_id');
                        $row = null;
                        if (isset($rows[$task['platform']])){
                            $row = $rows[$task['platform']];
                        }
                        if (!$row && isset($rows[0])){
                            $row = $rows[0];
                            if ($row->item_number){
                                $row->is_assigned = 1;
                                $order = \common\models\Orders::find()->select(['platform_id'])->where(['orders_id' => (int)$row->item_number ])->one();
                            }
                            $row->platform_id = ($order ? $order->platform_id : $defPlId);
                        }
                        //'platform_id' => $task['platform']
                        if ($row){
                            if (!$row->payment_class) $row->payment_class = $payment->code;
                        } else {
                            $row = new \common\models\PaypalipnTxn;
                            $row->loadDefaultValues();
                            $row->setAttributes([
                                'txn_id' => $info['transaction_id'],
                                'receiver_email' => '',
                                'item_name' => (isset($info['transaction_subject']) ? $info['transaction_subject'] : ''),
                                'payment_status' => (isset($info['transaction_status'])? $payment->describeStatus($info['transaction_status']): ''),
                                'mc_gross' => (float)$info['transaction_amount']['value'],
                                'mc_fee' => abs((float)$info['fee_amount']['value']),
                                'mc_currency' => (string)$info['transaction_amount']['currency_code'],
                                'txn_type' => 'cron',
                                'payment_class' => $payment->code,
                                'is_assigned' => 0,
                                'platform_id' => $task['platform'],
                            ], false);
                            if (isset($info['transaction_initiation_date'])){
                                $row->setAttribute('payment_date', date("Y-m-d H:i:s", strtotime($info['transaction_initiation_date'])));
                            }
                            if (isset($info['sales_tax_amount'])){
                                $row->setAttribute('tax', $info['sales_tax_amount']['value']);
                            }
                        }
                        $payer_info = $transaction['payer_info'];
                        if ($payer_info){
                            $row->setAttribute('payer_email', strval($payer_info['email_address']));
                            $row->setAttribute('payer_id', strval($payer_info['account_id']));
                            $status = $payer_info['payer_status'] == 'Y'? 'verified': 'unverified';
                            $row->setAttribute('payer_status', $status);
                            if ($payer_info['payer_name']){
                                if (isset($payer_info['payer_name']['given_name'])){
                                    $row->setAttribute('first_name', strval($payer_info['payer_name']['given_name']));
                                    $row->setAttribute('last_name', strval($payer_info['payer_name']['surname']));
                                } else {
                                    $ex = explode(" ", $payer_info['payer_name']['alternate_full_name']);
                                    $fname = $ex[0];
                                    unset($ex[0]);
                                    $lname = implode(" ", $ex);
                                    $row->setAttribute('first_name', strval($fname));
                                    $row->setAttribute('last_name', strval($lname));
                                }
                            }
                            $status = $payer_info['address_status'] == 'Y'? 'confirmed': '';
                            $row->setAttribute('address_status', $status);
                        }
                        $shipping = $transaction['shipping_info'];
                        if ($shipping && $shipping['address']){
                            $country = \common\helpers\Country::get_country_info_by_iso($shipping['address']['country_code']);
                            $row->setAttribute('address_street', strval($shipping['address']['line1']));
                            $row->setAttribute('address_city', strval($shipping['address']['city']));
                            $row->setAttribute('address_state', strval($shipping['address']['line2']));
                            $row->setAttribute('address_zip', strval($shipping['address']['postal_code']));
                            $row->setAttribute('address_country', strval(($country ? $country['title'] :'')));
                        }
                        $row->save(false);
                    }
                }
            } catch (\Exception $ex) {}
            
            $params = [];
            $values = [
                'platform_id' => $task['platform'],
                'payment_class' => $task['payment']->code,
                'start_date' => date("Y-m-d H:i:s", strtotime($task['start_date'])),
                'end_date' => date("Y-m-d H:i:s", strtotime($task['end_date'])),
            ];
            $sql = (new \yii\db\QueryBuilder(\Yii::$app->db))->insert('paypal_cron', $values, $params);            
            Yii::$app->db->createCommand($sql, $params)->execute();
            $message->info("{$task['payment']->code} for " . \common\classes\platform::name($task['platform']) . " to " . date("d m Y", strtotime($task['end_date'])) . " completed");
        }
        $task = false;
        return false;
    }
    
}

